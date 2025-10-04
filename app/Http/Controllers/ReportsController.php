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

    // 🔹 Get all reports first (without pagination for filtering)
    $allReports = $reportsQuery->latest('movementDate')->get();

    // 🔹 Filter categories and paginate each separately
    $validReports = $allReports->filter(fn($r) =>
        $r->type === 'IN' && 
        !str_starts_with(strtolower($r->reason), 'pulled_out') && 
        strtolower($r->reason) !== 'expired'
    );
    $expiredReports = $allReports->filter(fn($r) => 
        $r->type === 'OUT' && strtolower($r->reason) === 'expired'
    );
    $pulledOutReports = $allReports->filter(fn($r) => 
        $r->type === 'OUT' && str_starts_with(strtolower($r->reason), 'pulled_out')
    );

    // 🔹 Paginate each stock table separately (10 items per page)
    $validReportsPaginated = $this->paginateCollection($validReports, 10, 'valid_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);
    $expiredReportsPaginated = $this->paginateCollection($expiredReports, 10, 'expired_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);
    $pulledOutReportsPaginated = $this->paginateCollection($pulledOutReports, 10, 'pulled_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);

    // 🔹 Now paginate the main reports for display
    $reports = $reportsQuery->latest('movementDate')
        ->paginate(10)
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);

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

    // 🔹 Map sales data with discount information
    $salesData = $sales->flatMap->transactions->map(function ($transaction) {
        $stock = $transaction->stock;
        $sale = $transaction->sale;
        $sellingPrice = $stock->selling_price ?? 0;
        $purchasePrice = $stock->purchase_price ?? 0;
        $lineTotal = $transaction->quantity * $sellingPrice;
        
        // If this sale had a discount, calculate the discounted amount for this line item
        $discountedTotal = $lineTotal;
        $itemDiscount = 0;
        if ($sale->isDiscounted && $sale->subtotal > 0) {
            // Calculate this item's share of the discount (proportional)
            $discountRatio = $sale->discountAmount / $sale->subtotal;
            $itemDiscount = $lineTotal * $discountRatio;
            $discountedTotal = $lineTotal - $itemDiscount;
        }
        
        // Calculate profit (reduced by discount if applicable)
        $originalProfit = ($sellingPrice - $purchasePrice) * $transaction->quantity;
        $profit = $originalProfit - $itemDiscount;

        return [
            'productName'     => $stock->product->productName ?? 'N/A',
            'batchNo'         => $stock->batchNo ?? 'N/A',
            'quantity'        => $transaction->quantity,
            'purchasePrice'   => $purchasePrice,
            'sellingPrice'    => $sellingPrice,
            'total'           => $lineTotal,
            'discountedTotal' => $discountedTotal,
            'profit'          => $profit,
            'saleDate'        => $transaction->sale->saleDate,
            'isDiscounted'    => $sale->isDiscounted,
            'discountAmount'  => $sale->discountAmount,
        ];
    });

    $totalSales         = $salesData->sum('total');
    $totalDiscountedSales = $salesData->sum('discountedTotal');
    $totalProfit        = $salesData->sum('profit');
    $totalDiscounts     = $salesData->where('isDiscounted', true)->sum('discountAmount');

    return view('reports.index', compact(
        'reports',
        'search',
        'date',
        'period',
        'validReports',
        'expiredReports',
        'pulledOutReports',
        'validReportsPaginated',
        'expiredReportsPaginated',
        'pulledOutReportsPaginated',
        'totalStockIn',
        'totalPulledOut',
        'totalExpired',
        'salesData',
        'totalSales',
        'totalDiscountedSales',
        'totalProfit',
        'totalDiscounts'
    ));
}


    public function print($date)
    {
        // Stock reports
        $reports = Stock::with('product')
            ->whereDate('created_at', $date)
            ->get();

        $validReports = $reports->filter(fn($r) =>
            $r->type === 'IN' && 
            !str_starts_with(strtolower($r->reason), 'pulled_out') && 
            strtolower($r->reason) !== 'expired'
        );
        $expiredReports = $reports->filter(fn($r) => 
            $r->type === 'OUT' && strtolower($r->reason) === 'expired'
        );
        $pulledOutReports = $reports->filter(fn($r) => 
            $r->type === 'OUT' && str_starts_with(strtolower($r->reason), 'pulled_out')
        );

        // Sales reports
        $sales = Sale::with(['transactions.stock.product'])
            ->whereDate('saleDate', $date)
            ->get();

        $salesData = $sales->flatMap->transactions->map(function ($transaction) {
            $stock = $transaction->stock;
            $sale = $transaction->sale;
            $sellingPrice = $stock->selling_price ?? 0;
            $purchasePrice = $stock->purchase_price ?? 0;
            $lineTotal = $transaction->quantity * $sellingPrice;
            
            // If this sale had a discount, calculate the discounted amount for this line item
            $discountedTotal = $lineTotal;
            $itemDiscount = 0;
            if ($sale->isDiscounted && $sale->subtotal > 0) {
                // Calculate this item's share of the discount (proportional)
                $discountRatio = $sale->discountAmount / $sale->subtotal;
                $itemDiscount = $lineTotal * $discountRatio;
                $discountedTotal = $lineTotal - $itemDiscount;
            }
            
            // Calculate profit (reduced by discount if applicable)
            $originalProfit = ($sellingPrice - $purchasePrice) * $transaction->quantity;
            $profit = $originalProfit - $itemDiscount;

            return [
                'productName'     => $stock->product->productName ?? 'N/A',
                'batchNo'         => $stock->batchNo ?? 'N/A',
                'quantity'        => $transaction->quantity,
                'purchasePrice'   => $purchasePrice,
                'sellingPrice'    => $sellingPrice,
                'total'           => $lineTotal,
                'discountedTotal' => $discountedTotal,
                'profit'          => $profit,
                'saleDate'        => $transaction->sale->saleDate,
                'isDiscounted'    => $sale->isDiscounted,
                'discountAmount'  => $sale->discountAmount,
            ];
        });

        $totalSales = $salesData->sum('total');
        $totalDiscountedSales = $salesData->sum('discountedTotal');
        $totalProfit = $salesData->sum('profit');
        $totalDiscounts = $salesData->where('isDiscounted', true)->sum('discountAmount');

        return view('reports.print', compact(
            'validReports',
            'expiredReports',
            'pulledOutReports',
            'salesData',
            'totalSales',
            'totalDiscountedSales',
            'totalProfit',
            'totalDiscounts',
            'date'
        ));
    }

    /**
     * Paginate a collection manually
     */
    private function paginateCollection($items, $perPage, $pageName = 'page')
    {
        $currentPage = request()->get($pageName, 1);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $items->slice($offset, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }
}
