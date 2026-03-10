<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    // Get all parameters from the request
    $search = $request->input('search');
    $date = $request->input('date', now()->toDateString());
    $period = $request->input('period', 'today');
    $from = $request->input('from_date');
    $to = $request->input('to_date');
    
    // Determine report title based on period
    if ($period === 'today') {
        $reportTitle = 'Daily Report - ' . \Carbon\Carbon::parse($date)->format('F d, Y');
    } elseif ($period === 'monthly') {
        $reportTitle = 'Monthly Report - ' . \Carbon\Carbon::parse($date)->format('F Y');
    } elseif ($period === 'yearly') {
        $reportTitle = 'Yearly Report - ' . \Carbon\Carbon::parse($date)->format('Y');
    } elseif ($period === 'custom_range' && $from && $to) {
        $reportTitle = 'Custom Report - ' . $from . ' to ' . $to;
    } else {
        $reportTitle = 'Daily Report - ' . \Carbon\Carbon::parse($date)->format('F d, Y');
    }
    
    // 🔹 Stocks Query
    $baseStockQuery = Stock::with('product')
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

    // Apply period filter
    if ($period === 'today') {
        $baseStockQuery->whereDate('created_at', \Carbon\Carbon::parse($date));
    } elseif ($period === 'monthly') {
        $baseStockQuery->whereMonth('created_at', \Carbon\Carbon::parse($date)->month)
                     ->whereYear('created_at', \Carbon\Carbon::parse($date)->year);
    } elseif ($period === 'yearly') {
        $baseStockQuery->whereYear('created_at', \Carbon\Carbon::parse($date)->year);
    } elseif ($period === 'custom_range' && $from && $to) {
        $baseStockQuery->whereBetween('created_at', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay()
        ]);
    } else {
        $baseStockQuery->whereDate('created_at', \Carbon\Carbon::parse($date));
    }

    // Get all reports
    $allReports = (clone $baseStockQuery)->latest('movementDate')->get();

    // Filter reports by category
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

    // Sales Query
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

    // Apply period filter to Sales
    if ($period === 'today') {
        $salesQuery->whereDate('saleDate', \Carbon\Carbon::parse($date));
    } elseif ($period === 'monthly') {
        $salesQuery->whereMonth('saleDate', \Carbon\Carbon::parse($date)->month)
                   ->whereYear('saleDate', \Carbon\Carbon::parse($date)->year);
    } elseif ($period === 'yearly') {
        $salesQuery->whereYear('saleDate', \Carbon\Carbon::parse($date)->year);
    } elseif ($period === 'custom_range' && $from && $to) {
        $salesQuery->whereBetween('saleDate', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay()
        ]);
    } else {
        $salesQuery->whereDate('saleDate', \Carbon\Carbon::parse($date));
    }

    $sales = $salesQuery->orderByDesc('saleDate')->get();

    // Map sales data
    $salesData = $sales->flatMap->transactions->map(function ($transaction) {
        $stock = $transaction->stock;
        $sale = $transaction->sale;
        $sellingPrice = (float) ($stock->selling_price ?? 0);
        $purchasePrice = (float) ($stock->purchase_price ?? 0);
        $quantity = (int) ($transaction->quantity ?? 0);
        $lineTotal = $quantity * $sellingPrice;
        
        $discountedTotal = $lineTotal;
        $itemDiscount = 0;
        if ($sale->isDiscounted && $sale->subtotal > 0) {
            $discountRatio = (float) $sale->discountAmount / (float) $sale->subtotal;
            $itemDiscount = $lineTotal * $discountRatio;
            $discountedTotal = $lineTotal - $itemDiscount;
        }
        
        $originalProfit = ($sellingPrice - $purchasePrice) * $quantity;
        $profit = $originalProfit - $itemDiscount;

        return [
            'productName'     => $stock->product->productName ?? 'N/A',
            'genericName'     => $stock->product->genericName ?? 'N/A',
            'batchNo'         => $stock->batchNo ?? 'N/A',
            'quantity'        => $quantity,
            'purchasePrice'   => $purchasePrice,
            'sellingPrice'    => $sellingPrice,
            'total'           => $lineTotal,
            'discountedTotal' => $discountedTotal,
            'itemDiscount'    => $lineTotal - $discountedTotal,
            'profit'          => $profit,
            'saleDate'        => $sale->saleDate,
            'isDiscounted'    => $sale->isDiscounted,
            'discountAmount'  => (float) $sale->discountAmount,
            'cashReceived'    => (float) ($sale->cashReceived ?? 0),
            'changeGiven'     => (float) ($sale->changeGiven ?? 0),
        ];
    });

    // Calculate totals
    $totalStockIn   = (int) $validReports->sum('quantity');
    $totalPulledOut = (int) $pulledOutReports->sum('quantity');
    $totalExpired   = (int) $expiredReports->sum('quantity');
    $totalSales           = (float) $salesData->sum('total');
    $totalDiscountedSales = (float) $salesData->sum('discountedTotal');
    $totalProfit          = (float) $salesData->sum('profit');
    $totalDiscounts       = (float) $salesData->sum('itemDiscount');
    $totalCashReceived    = (float) $salesData->sum('cashReceived');
    $totalChangeGiven     = (float) $salesData->sum('changeGiven');

    // Return view for printing
    return view('reports.print', compact(
        'validReports',
        'expiredReports',
        'nearExpiryReports',
        'pulledOutReports',
        'salesData',
        'totalStockIn',
        'totalPulledOut',
        'totalExpired',
        'totalSales',
        'totalDiscountedSales',
        'totalProfit',
        'totalDiscounts',
        'totalCashReceived',
        'totalChangeGiven',
        'date',
        'period',
        'reportTitle',
        'search'
    ));
}

public function saveToS3(Request $request)
{
    try {
        // Get all parameters from the request
        $search = $request->input('search');
        $date = $request->input('date', now()->toDateString());
        $period = $request->input('period', 'today');
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        
        // Determine report title
        if ($period === 'today') {
            $reportTitle = 'Daily Report - ' . \Carbon\Carbon::parse($date)->format('F d, Y');
        } elseif ($period === 'monthly') {
            $reportTitle = 'Monthly Report - ' . \Carbon\Carbon::parse($date)->format('F Y');
        } elseif ($period === 'yearly') {
            $reportTitle = 'Yearly Report - ' . \Carbon\Carbon::parse($date)->format('Y');
        } elseif ($period === 'custom_range' && $from && $to) {
            $reportTitle = 'Custom Report - ' . $from . ' to ' . $to;
        } else {
            $reportTitle = 'Daily Report - ' . \Carbon\Carbon::parse($date)->format('F d, Y');
        }
        
        // Reuse the same logic to generate data (you might want to refactor this into a private method)
        // For now, I'll copy the essential parts
        
        // 🔹 Stocks Query
        $baseStockQuery = Stock::with('product')
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

        // Apply period filter
        if ($period === 'today') {
            $baseStockQuery->whereDate('created_at', \Carbon\Carbon::parse($date));
        } elseif ($period === 'monthly') {
            $baseStockQuery->whereMonth('created_at', \Carbon\Carbon::parse($date)->month)
                         ->whereYear('created_at', \Carbon\Carbon::parse($date)->year);
        } elseif ($period === 'yearly') {
            $baseStockQuery->whereYear('created_at', \Carbon\Carbon::parse($date)->year);
        } elseif ($period === 'custom_range' && $from && $to) {
            $baseStockQuery->whereBetween('created_at', [
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay()
            ]);
        } else {
            $baseStockQuery->whereDate('created_at', \Carbon\Carbon::parse($date));
        }

        // Get all reports
        $allReports = (clone $baseStockQuery)->latest('movementDate')->get();

        // Filter reports
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

        // Sales Query
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

        // Apply period filter to Sales
        if ($period === 'today') {
            $salesQuery->whereDate('saleDate', \Carbon\Carbon::parse($date));
        } elseif ($period === 'monthly') {
            $salesQuery->whereMonth('saleDate', \Carbon\Carbon::parse($date)->month)
                       ->whereYear('saleDate', \Carbon\Carbon::parse($date)->year);
        } elseif ($period === 'yearly') {
            $salesQuery->whereYear('saleDate', \Carbon\Carbon::parse($date)->year);
        } elseif ($period === 'custom_range' && $from && $to) {
            $salesQuery->whereBetween('saleDate', [
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay()
            ]);
        } else {
            $salesQuery->whereDate('saleDate', \Carbon\Carbon::parse($date));
        }

        $sales = $salesQuery->orderByDesc('saleDate')->get();

        // Map sales data
        $salesData = $sales->flatMap->transactions->map(function ($transaction) {
            $stock = $transaction->stock;
            $sale = $transaction->sale;
            $sellingPrice = (float) ($stock->selling_price ?? 0);
            $purchasePrice = (float) ($stock->purchase_price ?? 0);
            $quantity = (int) ($transaction->quantity ?? 0);
            $lineTotal = $quantity * $sellingPrice;
            
            $discountedTotal = $lineTotal;
            $itemDiscount = 0;
            if ($sale->isDiscounted && $sale->subtotal > 0) {
                $discountRatio = (float) $sale->discountAmount / (float) $sale->subtotal;
                $itemDiscount = $lineTotal * $discountRatio;
                $discountedTotal = $lineTotal - $itemDiscount;
            }
            
            $originalProfit = ($sellingPrice - $purchasePrice) * $quantity;
            $profit = $originalProfit - $itemDiscount;

            return [
                'productName'     => $stock->product->productName ?? 'N/A',
                'genericName'     => $stock->product->genericName ?? 'N/A',
                'batchNo'         => $stock->batchNo ?? 'N/A',
                'quantity'        => $quantity,
                'purchasePrice'   => $purchasePrice,
                'sellingPrice'    => $sellingPrice,
                'total'           => $lineTotal,
                'discountedTotal' => $discountedTotal,
                'itemDiscount'    => $lineTotal - $discountedTotal,
                'profit'          => $profit,
                'saleDate'        => $sale->saleDate,
                'isDiscounted'    => $sale->isDiscounted,
                'discountAmount'  => (float) $sale->discountAmount,
                'cashReceived'    => (float) ($sale->cashReceived ?? 0),
                'changeGiven'     => (float) ($sale->changeGiven ?? 0),
            ];
        });

        // Calculate totals
        $totalStockIn   = (int) $validReports->sum('quantity');
        $totalPulledOut = (int) $pulledOutReports->sum('quantity');
        $totalExpired   = (int) $expiredReports->sum('quantity');
        $totalSales           = (float) $salesData->sum('total');
        $totalDiscountedSales = (float) $salesData->sum('discountedTotal');
        $totalProfit          = (float) $salesData->sum('profit');
        $totalDiscounts       = (float) $salesData->sum('itemDiscount');
        $totalCashReceived    = (float) $salesData->sum('cashReceived');
        $totalChangeGiven     = (float) $salesData->sum('changeGiven');

        // Render the report HTML
        $reportHTML = view('reports.print', compact(
            'validReports',
            'expiredReports',
            'nearExpiryReports',
            'pulledOutReports',
            'salesData',
            'totalStockIn',
            'totalPulledOut',
            'totalExpired',
            'totalSales',
            'totalDiscountedSales',
            'totalProfit',
            'totalDiscounts',
            'totalCashReceived',
            'totalChangeGiven',
            'date',
            'period',
            'reportTitle',
            'search'
        ))->render();

        // Save to S3
        $filename = 'report_' . now()->format('Ymd_His') . '.html';
        $s3Path = 'reports/' . $filename;
        
        $uploaded = Storage::disk('s3')->put($s3Path, $reportHTML);
        
        if ($uploaded) {
            $s3Url = Storage::disk('s3')->url($s3Path);
            
            return response()->json([
                'success' => true,
                'message' => 'Report saved successfully to S3',
                'filename' => $filename,
                'path' => $s3Path,
                'url' => $s3Url
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save report to S3'
            ], 500);
        }
        
    } catch (\Exception $e) {
        \Log::error("S3 save failed: " . $e->getMessage());
        \Log::error("Stack trace: " . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


    //   Paginate a collection manually
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
