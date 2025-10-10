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
    $from   = $request->input('from_date');
    $to     = $request->input('to_date');

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
    } elseif ($period === 'custom_range' && $from && $to) {
        $reportsQuery->whereBetween('created_at', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay()
        ]);
    } else {
        $reportsQuery->whereDate('created_at', $date);
    }

    // 🔹 Get all reports first (without pagination for filtering)
    $allReports = $reportsQuery->latest('movementDate')->get();

    // 🔹 Filter categories and paginate each separately
    $validReports = $allReports->filter(fn($r) =>
        $r->type === 'IN' && 
        !str_starts_with(strtolower($r->reason ?? ''), 'pulled_out') && 
        strtolower($r->reason ?? '') !== 'expired' &&
        strtolower($r->reason ?? '') !== 'near_expiry'
    );
    $expiredReports = $allReports->filter(fn($r) => 
        ($r->type === 'OUT' && strtolower($r->reason ?? '') === 'expired') ||
        ($r->type === 'IN' && strtolower($r->reason ?? '') === 'expired')
    );
    $nearExpiryReports = $allReports->filter(fn($r) => 
        $r->type === 'IN' && strtolower($r->reason ?? '') === 'near_expiry'
    );
    $pulledOutReports = $allReports->filter(fn($r) => 
        $r->type === 'OUT' && str_starts_with(strtolower($r->reason ?? ''), 'pulled_out')
    );

    // 🔹 Paginate each stock table separately (10 items per page)
    $validReportsPaginated = $this->paginateCollection($validReports, 10, 'valid_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);
    $expiredReportsPaginated = $this->paginateCollection($expiredReports, 10, 'expired_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);
    $nearExpiryReportsPaginated = $this->paginateCollection($nearExpiryReports, 10, 'near_expiry_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);
    $pulledOutReportsPaginated = $this->paginateCollection($pulledOutReports, 10, 'pulled_page')
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);

    // 🔹 Now paginate the main reports for display
    $reports = $reportsQuery->latest('movementDate')
        ->paginate(10)
        ->appends(['search' => $search, 'date' => $date, 'period' => $period]);

    $totalStockIn   = (int) $validReports->sum('quantity');
    $totalPulledOut = (int) $pulledOutReports->sum('quantity');
    $totalExpired   = (int) $expiredReports->sum('quantity');

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
    } elseif ($period === 'custom_range' && $from && $to) {
        $salesQuery->whereBetween('saleDate', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay()
        ]);
    } else {
        $salesQuery->whereDate('saleDate', $date);
    }

    $sales = $salesQuery->orderByDesc('saleDate')->get();

    // 🔹 Map sales data with discount information
    $salesData = $sales->flatMap->transactions->map(function ($transaction) {
    $stock = $transaction->stock;
    $sale = $transaction->sale;
    $sellingPrice = (float) ($stock->selling_price ?? 0);
    $purchasePrice = (float) ($stock->purchase_price ?? 0);
    $quantity = (int) ($transaction->quantity ?? 0);
    $lineTotal = $quantity * $sellingPrice;
    
    // If this sale had a discount, calculate the discounted amount for this line item
    $discountedTotal = $lineTotal;
    $itemDiscount = 0;
    if ($sale->isDiscounted && $sale->subtotal > 0) {
        // Calculate this item's share of the discount (proportional)
        $discountRatio = (float) $sale->discountAmount / (float) $sale->subtotal;
        $itemDiscount = $lineTotal * $discountRatio;
        $discountedTotal = $lineTotal - $itemDiscount;
    }
    
    // Calculate profit (reduced by discount if applicable)
    $originalProfit = ($sellingPrice - $purchasePrice) * $quantity;
    $profit = $originalProfit - $itemDiscount;

    return [
        'productName'     => $stock->product->productName ?? 'N/A',
        'genericName'     => $stock->product->genericName ?? 'N/A', // ADD THIS LINE
        'batchNo'         => $stock->batchNo ?? 'N/A',
        'quantity'        => $quantity,
        'purchasePrice'   => $purchasePrice,
        'sellingPrice'    => $sellingPrice,
        'total'           => $lineTotal,
        'discountedTotal' => $discountedTotal,
        'itemDiscount'    => $lineTotal - $discountedTotal,
        'profit'          => $profit,
        'saleDate'        => $transaction->sale->saleDate,
        'isDiscounted'    => $sale->isDiscounted,
        'discountAmount'  => (float) $sale->discountAmount,
    ];
});

    $totalSales           = (float) $salesData->sum('total');
    $totalDiscountedSales = (float) $salesData->sum('discountedTotal');
    $totalProfit          = (float) $salesData->sum('profit');
    // Sum actual per-line discounts to avoid multiplying sale-level discount by number of lines
    $totalDiscounts       = (float) $salesData->sum('itemDiscount');

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
        'nearExpiryReportsPaginated',
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


    public function print(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $period = $request->input('period', 'specific_date');
        $from   = $request->input('from_date');
        $to     = $request->input('to_date');
        
        // Stock reports query
        $reportsQuery = Stock::with('product');
        
        // Sales reports query
        $salesQuery = Sale::with(['transactions.stock.product']);
        
        // Apply period filter to both queries
        if ($period === 'today') {
            $reportsQuery->whereDate('created_at', today());
            $salesQuery->whereDate('saleDate', today());
            $reportTitle = 'Daily Report - Today';
        } elseif ($period === 'monthly') {
            $reportsQuery->whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year);
            $salesQuery->whereMonth('saleDate', now()->month)
                       ->whereYear('saleDate', now()->year);
            $reportTitle = 'Monthly Report - ' . now()->format('F Y');
        } elseif ($period === 'yearly') {
            $reportsQuery->whereYear('created_at', now()->year);
            $salesQuery->whereYear('saleDate', now()->year);
            $reportTitle = 'Yearly Report - ' . now()->year;
        }elseif ($period === 'custom_range' && $from && $to) {
        $reportsQuery->whereBetween('created_at', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay()
        ]);
        }
         else {
            $reportsQuery->whereDate('created_at', $date);
            $salesQuery->whereDate('saleDate', $date);
            $reportTitle = 'Daily Report - ' . $date;
        }
        
        // Get valid reports directly from database
        $validReports = Stock::with('product')
            ->where('type', 'IN')
            ->where(function($query) {
                $query->whereNull('reason')
                      ->orWhere(function($q) {
                          $q->where('reason', 'not like', 'pulled_out%')
                            ->where('reason', '!=', 'expired')
                            ->where('reason', '!=', 'near_expiry');
                      });
            })
            ->when($period === 'today', fn($q) => $q->whereDate('created_at', today()))
            ->when($period === 'monthly', fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->when($period === 'yearly', fn($q) => $q->whereYear('created_at', now()->year))
            ->when($period === 'custom_range', fn($q) => $q->whereDate('created_at', $date))
            ->get();

    $expiredReports = Stock::with('product')
        ->where('reason', 'expired')
        ->when($period === 'today', fn($q) => $q->whereDate('created_at', today()))
        ->when($period === 'monthly', fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
        ->when($period === 'yearly', fn($q) => $q->whereYear('created_at', now()->year))
        ->when($period === 'custom_range', fn($q) => $q->whereDate('created_at', $date))
        ->where('type', 'IN')
        ->where('quantity', '>', 0)
        ->get();
    
    $nearExpiryReports = Stock::with('product')
        ->where('type', 'IN')
        ->where('reason', 'near_expiry')
        ->when($period === 'today', fn($q) => $q->whereDate('created_at', today()))
        ->when($period === 'monthly', fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
        ->when($period === 'yearly', fn($q) => $q->whereYear('created_at', now()->year))
        ->when($period === 'custom_range', fn($q) => $q->whereDate('created_at', $date))
        ->get();

    $pulledOutReports = Stock::with('product')
        ->where('type', 'OUT')
        ->where(function($query) {
            $query->where('reason', 'like', 'pulled_out%')
                  ->orWhere('reason', 'pulled_out_low_stock');
        })
        ->when($period === 'today', fn($q) => $q->whereDate('created_at', today()))
        ->when($period === 'monthly', fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
        ->when($period === 'yearly', fn($q) => $q->whereYear('created_at', now()->year))
        ->when($period === 'custom_range', fn($q) => $q->whereDate('created_at', $date))
        ->get();

        // Get the sales
        $sales = $salesQuery->get();

        $salesData = $sales->flatMap->transactions->map(function ($transaction) {
    $stock = $transaction->stock;
    $sale = $transaction->sale;
    $sellingPrice = (float) ($stock->selling_price ?? 0);
    $purchasePrice = (float) ($stock->purchase_price ?? 0);
    $quantity = (int) ($transaction->quantity ?? 0);
    $lineTotal = $quantity * $sellingPrice;
    
    // If this sale had a discount, calculate the discounted amount for this line item
    $discountedTotal = $lineTotal;
    $itemDiscount = 0;
    if ($sale->isDiscounted && $sale->subtotal > 0) {
        // Calculate this item's share of the discount (proportional)
        $discountRatio = (float) $sale->discountAmount / (float) $sale->subtotal;
        $itemDiscount = $lineTotal * $discountRatio;
        $discountedTotal = $lineTotal - $itemDiscount;
    }
    
    // Calculate profit (reduced by discount if applicable)
    $originalProfit = ($sellingPrice - $purchasePrice) * $quantity;
    $profit = $originalProfit - $itemDiscount;

    return [
        'productName'     => $stock->product->productName ?? 'N/A',
        'genericName'     => $stock->product->genericName ?? 'N/A', // ADD THIS LINE
        'batchNo'         => $stock->batchNo ?? 'N/A',
        'quantity'        => $quantity,
        'purchasePrice'   => $purchasePrice,
        'sellingPrice'    => $sellingPrice,
        'total'           => $lineTotal,
        'discountedTotal' => $discountedTotal,
        'itemDiscount'    => $lineTotal - $discountedTotal,
        'profit'          => $profit,
        'saleDate'        => $transaction->sale->saleDate,
        'isDiscounted'    => $sale->isDiscounted,
        'discountAmount'  => (float) $sale->discountAmount,
    ];
});

        $totalSales = $salesData->sum('total');
        $totalDiscountedSales = $salesData->sum('discountedTotal');
        $totalProfit = $salesData->sum('profit');
        $totalDiscounts = $salesData->sum('itemDiscount');

        return view('reports.print', compact(
            'validReports',
            'expiredReports',
            'nearExpiryReports',
            'pulledOutReports',
            'salesData',
            'totalSales',
            'totalDiscountedSales',
            'totalProfit',
            'totalDiscounts',
            'date',
            'period',
            'reportTitle'
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
