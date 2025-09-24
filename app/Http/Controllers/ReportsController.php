<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

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
            ->latest('movementDate')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('reports.index', compact('reports',  'search'));
    }
}
