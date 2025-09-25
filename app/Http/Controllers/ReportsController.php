<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $date = $request->input('date', now()->toDateString());

        // All stock movements (with search + pagination)
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

        // Separate by reason
        $validReports = $reports->filter(fn($r) => 
            !str_starts_with(strtolower($r->reason), 'pulled_out') && strtolower($r->reason) !== 'expired'
        );
        $expiredReports = $reports->filter(fn($r) => strtolower($r->reason) === 'expired');
        $pulledOutReports = $reports->filter(fn($r) => str_starts_with(strtolower($r->reason), 'pulled_out'));

        // Totals
        $totalStockIn = $validReports->sum('quantity');
        $totalPulledOut = $pulledOutReports->sum('quantity');
        $totalExpired = $expiredReports->sum('quantity');

        return view('reports.index', compact(
            'reports',
            'search',
            'date',
            'validReports',
            'expiredReports',
            'pulledOutReports',
            'totalStockIn',
            'totalPulledOut',
            'totalExpired'
        ));
    }

    public function print($date)
    {
        $reports = Stock::with('product')
            ->whereDate('created_at', $date)
            ->get();

        // Separate by reason
        $validReports = $reports->filter(fn($r) => 
            !str_starts_with(strtolower($r->reason), 'pulled_out') && strtolower($r->reason) !== 'expired'
        );
        $expiredReports = $reports->filter(fn($r) => strtolower($r->reason) === 'expired');
        $pulledOutReports = $reports->filter(fn($r) => str_starts_with(strtolower($r->reason), 'pulled_out'));

        // Totals
        $totalQuantity = $reports->sum('quantity');
        $validTotalValue = $validReports->sum(fn($r) => $r->quantity * $r->product->price);
        $expiredTotal = $expiredReports->sum('quantity');
        $pulledOutTotal = $pulledOutReports->sum('quantity');

        return view('reports.print', compact(
            'validReports',
            'expiredReports',
            'pulledOutReports',
            'totalQuantity',
            'validTotalValue',
            'expiredTotal',
            'pulledOutTotal',
            'date'
        ));
    }
}
