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
            ->when($search, function ($query, $search) {
                return $query->where('batchNo', 'like', "%{$search}%")
                            ->orWhere('quantity', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($q) use ($search) {
                                $q->where('productName', 'like', "%{$search}%");
                            });
            })
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
            'productID' => 'required|exists:products,productID',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
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

        return redirect()->route('inventory.index')->with('success', 'Stock added successfully!');
    }

    /**
     * Stock Out - Remove expired or damaged stock
     */
    public function stockOut(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);

        $stock->update([
            'type'         => 'OUT',
            'availability' => false,
            'movementDate' => now(),
        ]);

        return redirect()->route('inventory.index')->with('success', 'Stock marked as OUT!');
    }
}
