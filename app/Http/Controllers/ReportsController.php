<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Latest stock movements (for dashboard widget)
        $stocks = Stock::with('product')->latest()->take(3)->get();

        // Expired, damaged, pulled-out (still from stocks table)
        $expiredDamagedItems = Stock::with('product')
            ->whereIn('type', ['expired', 'damaged', 'pulled_out'])
            ->latest()
            ->take(10)
            ->get();

        // All stock movements (for modal, paginated)
        $allStockMovements = Stock::with('product')->latest()->paginate(10);

        return view('reports.index', compact('stocks', 'expiredDamagedItems', 'allStockMovements'));
    }
}
