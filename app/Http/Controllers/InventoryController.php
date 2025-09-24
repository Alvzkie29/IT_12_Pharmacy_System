<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $stocks = Stock::with('product')
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
            ->orderBy('movementDate', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        $products = Product::all();
        $suppliers = Supplier::all();

        return view('inventory.index', compact('stocks', 'products', 'suppliers', 'search'));
    }

    public function stockIn(Request $request)
    {
        $request->validate([
            'productID' => 'required|exists:products,productID',
            'price'     => 'required|numeric|min:0',
            'quantity'  => 'required|integer|min:1',
            'batchNo'   => 'nullable|string|max:255',
            'expiryDate' => 'nullable|date',
        ]);

        Stock::create([
            'productID'    => $request->productID,
            'employeeID'   => Auth::user()->employeeID ?? null, // ensure the logged in user has employeeID
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
 * Stock Out - Remove or reduce stock
 */
    public function stockOut(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason'   => 'required|string|in:sale,expired,pullout',
        ]);

        $stock = Stock::findOrFail($id);

        // Check if requested quantity is valid
        if ($request->quantity > $stock->quantity) {
            return redirect()->route('inventory.index')
                            ->with('error', 'Quantity exceeds available stock!');
        }

        // Reduce the stock quantity
        $stock->quantity -= $request->quantity;

        // If fully out, mark availability as false and type as OUT
        if ($stock->quantity <= 0) {
            $stock->quantity = 0;
            $stock->availability = false;
            $stock->type = 'OUT';
        }

        $stock->movementDate = now();
        $stock->save();

        // Optionally, you can log the reason in a separate table if needed
        // StockOutLog::create([
        //     'stockID' => $stock->stockID,
        //     'quantity' => $request->quantity,
        //     'reason' => $request->reason,
        //     'employeeID' => Auth::user()->employeeID,
        //     'created_at' => now(),
        // ]);

        return redirect()->route('inventory.index')
                        ->with('success', 'Stock updated successfully!');
    }
}
