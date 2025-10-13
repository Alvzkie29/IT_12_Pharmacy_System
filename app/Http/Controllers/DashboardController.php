<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get selected period
        $period = $request->input('period', 'today');
        $date = $request->input('date', now()->toDateString());

        // Today's specific metrics
        $todaySales = Sale::whereDate('saleDate', today())->sum('totalAmount');
        $todayTransactions = Sale::whereDate('saleDate', today())->count();
        
        // Query based on period for general sales
        $salesQuery = Sale::query();
        if ($period === 'today') {
            $salesQuery->whereDate('saleDate', today());
        } elseif ($period === 'monthly') {
            $salesQuery->whereMonth('saleDate', now()->month)
                       ->whereYear('saleDate', now()->year);
        } elseif ($period === 'yearly') {
            $salesQuery->whereYear('saleDate', now()->year);
        }

        $salesTotal = $salesQuery->sum('totalAmount');

        // VAT handling
        $vatAmount = 0;
        $netSales = $salesTotal;
        if ($period === 'monthly') {
            $vatAmount = $salesTotal * 0.01;
            $netSales  = $salesTotal - $vatAmount;
        }

        // Stock metrics
        $stockIn = Stock::where('type', 'IN')->count();
        $pulledOut = Stock::where('reason', 'pullout')->count();
        $expiredItems = Stock::where('reason', 'expired')->count();
        $damagedItems = Stock::where('reason', 'damaged')->count();

        // Product and inventory metrics
        $totalProducts = Product::count();
        $totalStocks = Stock::count();
        $lowStockThreshold = 30;
        $lowStockCount = Stock::where('type', 'IN')
        ->where('availability', true)
        ->whereDate('expiryDate', '>', now()) // Only non-expired
        ->where('quantity', '<=', $lowStockThreshold)
        ->count();
        
        // Near expiry count (within 6 months)
        $nearExpiryCount = Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->whereDate('expiryDate', '<=', now()->addMonths(6))
            ->count();

        // Latest stocks for activity feed
        $latestStocks = Stock::with('product')
            ->orderBy('created_at', 'desc')
            ->latest('movementDate')
            ->take(5)
            ->get();

        // Critical alerts
        $criticalAlerts = $this->getCriticalAlerts($lowStockCount, $nearExpiryCount);
        $criticalAlertsCount = $criticalAlerts->count();

        // Today's activities for the activity feed
        $todayActivities = $this->getTodayActivities();

        // Top products
        $topProductsData = Transaction::select('stockID', DB::raw('SUM(quantity) as total'))
            ->groupBy('stockID')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                $product = $transaction->stock->product ?? null;
                return [
                    'name' => $product?->productName ?? 'Unknown',
                    'total' => $transaction->total,
                ];
            });

        // Sales summary (last 7 days)
        $salesSummary = DB::table('sales')
            ->join('transactions', 'transactions.saleID', '=', 'sales.saleID')
            ->select(
                DB::raw('DATE(sales.saleDate) as saleDate'),
                DB::raw('COUNT(DISTINCT sales.saleID) as transactions_count'),
                DB::raw('SUM(transactions.quantity) as items_sold'),
                DB::raw('SUM(sales.totalAmount) as total_sales')
            )
            ->groupBy(DB::raw('DATE(sales.saleDate)'))
            ->orderByDesc(DB::raw('DATE(sales.saleDate)'))
            ->take(7)
            ->get();

        // Quarterly sales with VAT (1% deduction per quarter)
        $quarterlySales = DB::table('sales')
            ->select(
                DB::raw('QUARTER(saleDate) as quarter'),
                DB::raw('YEAR(saleDate) as year'),
                DB::raw('SUM(totalAmount) as total_sales'),
                DB::raw('SUM(totalAmount) - (SUM(totalAmount) * 0.01) as net_sales')
            )
            ->groupBy(DB::raw('YEAR(saleDate), QUARTER(saleDate)'))
            ->orderByDesc(DB::raw('YEAR(saleDate), QUARTER(saleDate)'))
            ->get();

        $totalExpiredDamaged = Stock::whereIn('reason', ['expired', 'damaged'])->count();

        return view('dashboard.index', compact(
            'todaySales', 'todayTransactions', 'salesTotal', 'period',
            'vatAmount', 'netSales',
            'stockIn', 'pulledOut', 'expiredItems', 'damagedItems',
            'topProductsData',
            'totalProducts', 'totalStocks', 'lowStockCount', 'nearExpiryCount',
            'latestStocks', 'salesSummary', 'quarterlySales', // Changed from monthlySales to quarterlySales
            'date', 'totalExpiredDamaged',
            'criticalAlerts', 'criticalAlertsCount', 'todayActivities'
        ));
    }

    private function getCriticalAlerts($lowStockCount, $nearExpiryCount)
    {
        $alerts = collect();

        // Low stock alert
        if ($lowStockCount > 0) {
            $alerts->push([
                'title' => 'Low Stock Items',
                'description' => $lowStockCount . ' product(s) below minimum stock level',
                'type' => 'warning',
                'priority' => 'high',
                'date' => now()->format('M j, g:i A'),
                'link' => route('inventory.nearExpiry')
            ]);
        }

        // Near expiry alert
        if ($nearExpiryCount > 0) {
            $alerts->push([
                'title' => 'Near Expiry Items',
                'description' => $nearExpiryCount . ' product(s) expiring within 6 months',
                'type' => 'warning',
                'priority' => 'medium',
                'date' => now()->format('M j, g:i A'),
                'link' => route('inventory.nearExpiry')
            ]);
        }

        // No critical alerts message
        if ($alerts->isEmpty()) {
            $alerts->push([
                'title' => 'All Systems Normal',
                'description' => 'No critical issues detected',
                'type' => 'info',
                'priority' => 'low',
                'date' => now()->format('M j, g:i A'),
                'link' => null
            ]);
        }

        return $alerts;
    }

    private function getTodayActivities()
    {
        $activities = collect();

        // Get today's sales
        $todaySales = Sale::with(['transactions.stock.product'])
            ->whereDate('saleDate', today())
            ->orderBy('saleDate', 'desc')
            ->take(5)
            ->get();

        foreach ($todaySales as $sale) {
            foreach ($sale->transactions as $transaction) {
                $activities->push([
                    'time' => $sale->saleDate->format('h:i A'),
                    'type' => 'Sale',
                    'type_color' => 'success',
                    'product_name' => $transaction->stock->product->productName ?? 'Unknown',
                    'amount' => '₱' . number_format($transaction->quantity * $transaction->stock->selling_price, 2),
                    'amount_class' => 'metric-positive',
                    'status' => 'Completed',
                    'status_color' => 'success'
                ]);
            }
        }

        // Get today's stock movements
        $todayStocks = Stock::with('product')
            ->whereDate('movementDate', today())
            ->orderBy('movementDate', 'desc')
            ->take(5)
            ->get();

        foreach ($todayStocks as $stock) {
            $type = $stock->type === 'IN' ? 'Stock In' : 'Stock Out';
            $typeColor = $stock->type === 'IN' ? 'primary' : 'warning';
            
            $activities->push([
                'time' => $stock->movementDate->format('h:i A'),
                'type' => $type,
                'type_color' => $typeColor,
                'product_name' => $stock->product->productName ?? 'Unknown',
                'amount' => $stock->quantity . ' units',
                'amount_class' => 'metric-neutral',
                'status' => ucfirst($stock->reason ?? 'Processed'),
                'status_color' => 'info'
            ]);
        }

        // Sort by time and take latest 5
        return $activities->sortByDesc('time')->take(5);
    }
}