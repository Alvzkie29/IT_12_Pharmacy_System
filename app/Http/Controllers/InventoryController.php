<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Show inventory page with products and stock info
     */
    public function index(Request $request)
{
    $search = $request->input('search');

    $stocks = Stock::with('product')
        ->where('type', 'IN')          
        ->where('availability', true) 
        ->when($search, function ($query, $search) {
            return $query->where('batchNo', 'like', "%{$search}%")
                         ->orWhere('quantity', 'like', "%{$search}%")
                         ->orWhereHas('product', function ($q) use ($search) {
                             $q->where('productName', 'like', "%{$search}%")
                               ->orWhere('genericName', 'like', "%{$search}%")
                               ->orWhere('productWeight', 'like', "%{$search}%")
                               ->orWhere('dosageForm', 'like', "%{$search}%");
                         });
        })
        ->orderBy('expiryDate', 'asc')
        ->paginate(10)
        ->appends(['search' => $search]);

    $products = Product::all();
    $suppliers = Supplier::all();

    return view('inventory.index', compact('stocks', 'products', 'suppliers', 'search'));
}


    /**
     * Stock In - Add new stock entry
     */
    public function stockIn(Request $request)
    {
        $request->validate([
            'productID'  => 'required|exists:products,productID',
            'price'      => 'required|numeric|min:0',
            'quantity'   => 'required|integer|min:1',
            'batchNo'    => 'nullable|string',
            'expiryDate' => 'nullable|date',
        ]);

        Stock::create([
            'productID'    => $request->productID,
            'employeeID'   => Auth::user()->employeeID,
            'type'         => 'IN',
            'price'        => $request->price,
            'quantity'     => $request->quantity,
            'availability' => true,
            'batchNo'      => $request->batchNo,
            'expiryDate'   => $request->expiryDate,
            'movementDate' => now(),
        ]);

        return redirect()->route('inventory.index')
                         ->with('success', 'Stock added successfully!');
    }


public function stockOut(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
        'reason'   => 'required|string',
    ]);

    $stock = Stock::findOrFail($id);

    if ($request->quantity > $stock->quantity) {
        return back()->with('error', 'Not enough stock available.');
    }

    // 1. Reduce the available stock on the IN row
    $stock->quantity -= $request->quantity;
    $stock->availability = $stock->quantity > 0;
    $stock->save();

    // 2. Insert a separate OUT row (only for history)
    Stock::create([
        'productID'    => $stock->productID,
        'employeeID'   => Auth::user()->employeeID, 
        'type'         => 'OUT',
        'reason'       => $request->reason,
        'price'        => $stock->price,
        'quantity'     => $request->quantity,
        'availability' => false, // ✅ OUT rows never count as active stock
        'batchNo'      => $stock->batchNo,
        'expiryDate'   => $stock->expiryDate,
        'movementDate' => now(),
    ]);

    return redirect()->route('reports.index')->with('success', 'Stock out recorded successfully.');
}


}
