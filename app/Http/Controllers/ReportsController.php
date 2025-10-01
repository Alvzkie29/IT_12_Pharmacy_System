<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');
    $period = $request->input('period', 'today'); // same as dashboard
    $date   = $request->input('date', now()->toDateString());

    // 🔹 Stocks Query
    $reportsQuery = Stock::with('product')
        ->when($search, function ($query, $search) {
            return $query->where('batchNo', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('productName', 'like', "%{$search}%")
                      ->orWhere('genericName', 'like', "%{$search}%")
                      ->orWhere('productWeight', 'like', "%{$search}%")
                      ->orWhere('dosageForm', 'like', "%{$search}%");
                });
        });

    // 🔹 Apply period filter to Stocks (based on created_at)
    if ($period === 'today') {
        $reportsQuery->whereDate('created_at', today());
    } elseif ($period === 'monthly') {
        $reportsQuery->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    } elseif ($period === 'yearly') {
        $reportsQuery->whereYear('created_at', now()->year);
    } else {
        $reportsQuery->whereDate('created_at', $date);
    }

    $reports = $reportsQuery->latest('movementDate')
        ->paginate(10)
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);

    // 🔹 Filter categories
    $validReports = $reports->filter(fn($r) =>
        !str_starts_with(strtolower($r->reason), 'pulled_out') && strtolower($r->reason) !== 'expired'
    );
    $expiredReports = $reports->filter(fn($r) => strtolower($r->reason) === 'expired');
    $pulledOutReports = $reports->filter(fn($r) => str_starts_with(strtolower($r->reason), 'pulled_out'));

    $totalStockIn   = $validReports->sum('quantity');
    $totalPulledOut = $pulledOutReports->sum('quantity');
    $totalExpired   = $expiredReports->sum('quantity');

    // 🔹 Sales Query
    $salesQuery = Sale::with(['transactions.stock.product'])
        ->when($search, function ($query, $search) {
            $query->whereHas('transactions.stock.product', function ($q) use ($search) {
                $q->where('productName', 'like', "%{$search}%")
                  ->orWhere('genericName', 'like', "%{$search}%")
                  ->orWhere('productWeight', 'like', "%{$search}%")
                  ->orWhere('dosageForm', 'like', "%{$search}%");
            })
            ->orWhereHas('transactions.stock', function ($q) use ($search) {
                $q->where('batchNo', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        });

    // 🔹 Apply period filter to Sales (based on saleDate)
    if ($period === 'today') {
        $salesQuery->whereDate('saleDate', today());
    } elseif ($period === 'monthly') {
        $salesQuery->whereMonth('saleDate', now()->month)
                   ->whereYear('saleDate', now()->year);
    } elseif ($period === 'yearly') {
        $salesQuery->whereYear('saleDate', now()->year);
    } else {
        $salesQuery->whereDate('saleDate', $date);
    }

    $sales = $salesQuery->get();

    // 🔹 Map sales data
    $salesData = $sales->flatMap->transactions->map(function ($transaction) {
        $stock = $transaction->stock;
        $sellingPrice = $stock->selling_price ?? 0;
        $purchasePrice = $stock->purchase_price ?? 0;
        $profit = ($sellingPrice - $purchasePrice) * $transaction->quantity;

        return [
            'productName'   => $stock->product->productName ?? 'N/A',
            'batchNo'       => $stock->batchNo ?? 'N/A',
            'quantity'      => $transaction->quantity,
            'purchasePrice' => $purchasePrice,
            'sellingPrice'  => $sellingPrice,
            'total'         => $transaction->quantity * $sellingPrice,
            'profit'        => $profit,
            'saleDate'      => $transaction->sale->saleDate,
        ];
    });

    $totalSales  = $salesData->sum('total');
    $totalProfit = $salesData->sum('profit');

    return view('reports.index', compact(
        'reports',
        'search',
        'date',
        'period',
        'validReports',
        'expiredReports',
        'pulledOutReports',
        'totalStockIn',
        'totalPulledOut',
        'totalExpired',
        'salesData',
        'totalSales',
        'totalProfit'
    ));
}


    public function print($date)
    {
        // Stock reports
        $reports = Stock::with('product')
            ->whereDate('created_at', $date)
            ->get();

        $validReports = $reports->filter(fn($r) =>
            !str_starts_with(strtolower($r->reason), 'pulled_out') && strtolower($r->reason) !== 'expired'
        );
        $expiredReports = $reports->filter(fn($r) => strtolower($r->reason) === 'expired');
        $pulledOutReports = $reports->filter(fn($r) => str_starts_with(strtolower($r->reason), 'pulled_out'));

        // Sales reports
        $sales = Sale::with(['transactions.stock.product'])
            ->whereDate('saleDate', $date)
            ->get();

        $salesData = $sales->flatMap->transactions->map(function ($transaction) {
            $stock = $transaction->stock;
            $sellingPrice = $stock->selling_price ?? 0;
            $purchasePrice = $stock->purchase_price ?? 0;
            $profit = ($sellingPrice - $purchasePrice) * $transaction->quantity;

            return [
                'productName'   => $stock->product->productName ?? 'N/A',
                'batchNo'       => $stock->batchNo ?? 'N/A',
                'quantity'      => $transaction->quantity,
                'purchasePrice' => $purchasePrice,
                'sellingPrice'  => $sellingPrice,
                'total'         => $transaction->quantity * $sellingPrice,
                'profit'        => $profit,
                'saleDate'      => $transaction->sale->saleDate,
            ];
        });

        $totalSales = $salesData->sum('total');
        $totalProfit = $salesData->sum('profit');

        return view('reports.print', compact(
            'validReports',
            'expiredReports',
            'pulledOutReports',
            'salesData',
            'totalSales',
            'totalProfit',
            'date'
        ));
    }
}
