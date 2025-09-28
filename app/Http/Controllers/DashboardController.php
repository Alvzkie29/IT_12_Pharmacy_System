<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index(Request $request)
{
    
    // get selected period
    $period = $request->input('period', 'today');
    $date = $request->input('date', now()->toDateString());
    // query based on period
    $salesQuery = Sale::query();
    if ($period === 'today') {
        $salesQuery->whereDate('saleDate', today());
    } elseif ($period === 'monthly') {
        $salesQuery->whereMonth('saleDate', now()->month)
                   ->whereYear('saleDate', now()->year);
    } elseif ($period === 'yearly') {
        $salesQuery->whereYear('saleDate', now()->year);
    }

    // sum of totalAmount for chosen period
    $salesTotal = $salesQuery->sum('totalAmount');

    // Stocks
    $stockIn = Stock::where('type', 'IN')->count();
    $pulledOut = Stock::where('reason', 'pullout')->count();
    $expiredItems = Stock::where('reason', 'expired')->count();
    $damagedItems = Stock::where('reason', 'damaged')->count();

    // Totals
    $totalProducts   = Product::count();
    $totalStocks     = Stock::count();
    $lowStockCount   = Stock::where('quantity', '<', 10)->count(); // adjust column name
    $latestStocks    = Stock::with('product')
    ->orderBy('created_at','desc', $date)
    ->latest('movementDate')->take(5)->get();

    // Combined expired + damaged total
    $totalExpiredDamaged = Stock::whereIn('reason', ['expired', 'damaged'])->count();


    // Top Products (via transactions)
    $topProductsData = \App\Models\Transaction::select('stockID', DB::raw('SUM(quantity) as total'))
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

    // Inventory categories
    $inventoryData = Product::selectRaw('category, COUNT(*) as total')
        ->groupBy('category')
        ->pluck('total', 'category');

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

    // Recent stock movements
    $recentActivities = Stock::latest('movementDate')->take(3)->get();

    return view('dashboard.index', compact(
        'salesTotal','period',
        'stockIn','pulledOut','expiredItems','damagedItems',
        'topProductsData','inventoryData',
        'recentActivities','totalProducts','totalStocks','lowStockCount',
        'latestStocks','salesSummary', 'date', 'totalExpiredDamaged'
    ));
}


}