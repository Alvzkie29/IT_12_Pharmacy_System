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
        $date = $request->input('date', now()->toDateString());

        // 🔹 Stock Reports
        $reports = Stock::with('product')
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
            })
            ->whereDate('created_at', $date)
            ->latest('movementDate')
            ->paginate(10)
            ->appends(['search' => $search, 'date' => $date]);

        $validReports = $reports->filter(fn($r) =>
            !str_starts_with(strtolower($r->reason), 'pulled_out') && strtolower($r->reason) !== 'expired'
        );
        $expiredReports = $reports->filter(fn($r) => strtolower($r->reason) === 'expired');
        $pulledOutReports = $reports->filter(fn($r) => str_starts_with(strtolower($r->reason), 'pulled_out'));

        $totalStockIn = $validReports->sum('quantity');
        $totalPulledOut = $pulledOutReports->sum('quantity');
        $totalExpired = $expiredReports->sum('quantity');

        // 🔹 Sales Reports (profit from stocks)
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

        return view('reports.index', compact(
            'reports',
            'search',
            'date',
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
