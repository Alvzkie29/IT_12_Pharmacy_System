<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryController extends Controller
{
    /**
     * Show inventory page with products and stock info
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $stocksQuery = Stock::with('product')
            ->where('type', 'IN')
            ->where('availability', true)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('batchNo', 'like', "%{$search}%")
                      ->orWhere('quantity', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('productName', 'like', "%{$search}%")
                      ->orWhere('genericName', 'like', "%{$search}%")
                      ->orWhere('productWeight', 'like', "%{$search}%")
                      ->orWhere('dosageForm', 'like', "%{$search}%");
                });
            })
            ->orderBy('expiryDate', 'asc');

        // Fetch, then filter out batches with no available quantity, then paginate manually
        $stocksCollection = $stocksQuery->get()->filter(function ($stock) {
            return $stock->available_quantity > 0;
        })->values();

        $perPage = 10;
        $currentPage = (int) (request()->get('page', 1));
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $stocksCollection->slice($offset, $perPage)->values();

        $stocks = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $stocksCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => ['search' => $search],
            ]
        );

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
            'productID'      => 'required|exists:products,productID',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0|gte:purchase_price', // selling must be >= purchase
            'quantity'       => 'required|integer|min:1',
            'batchNo'        => 'nullable|string|max:50',
            'expiryDate'     => 'nullable|date|after:today',
        ]);

        Stock::create([
            'productID'      => $request->productID,
            'employeeID'     => Auth::user()->employeeID,
            'type'           => 'IN',
            'purchase_price' => $request->purchase_price,
            'selling_price'  => $request->selling_price,
            'quantity'       => $request->quantity,
            'availability'   => true,
            'batchNo'        => $request->batchNo,
            'expiryDate'     => $request->expiryDate,
            'movementDate'   => now(),
        ]);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock added successfully!');
    }
    /**
     * Stock Out - Reduce quantity and log movement
     */
    public function stockOut(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason'   => 'required|string',
        ]);

        $stock = Stock::findOrFail($id);

        if ($request->quantity > $stock->available_quantity) {
            return back()->with('error', 'Not enough stock available.');
        }

        // DO NOT modify the original stock-in record - it should remain as historical data
        // The available quantity is calculated dynamically

        // Insert a separate OUT row (only for history)
        Stock::create([
            'productID'      => $stock->productID,
            'employeeID'     => Auth::user()->employeeID, 
            'type'           => 'OUT',
            'reason'         => $request->reason,
            'purchase_price' => $stock->purchase_price,
            'selling_price'  => $stock->selling_price,
            'quantity'       => $request->quantity,
            'availability'   => false, 
            'batchNo'        => $stock->batchNo,
            'expiryDate'     => $stock->expiryDate,
            'movementDate'   => now(),
        ]);

        return redirect()->route('reports.index')->with('success', 'Stock out recorded successfully.');
    }
}
