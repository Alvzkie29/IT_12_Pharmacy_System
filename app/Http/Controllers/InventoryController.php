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

        // Check if there's an existing stock with the same product and batch number
        $existingStock = Stock::where('productID', $request->productID)
            ->where('batchNo', $request->batchNo)
            ->where('type', 'IN')
            ->where('availability', true)
            ->first();

        // Check if the request is AJAX
        $isAjax = $request->ajax();
        
        if ($existingStock && $request->batchNo) {
            // Check if all the important fields match
            $mismatches = [];
            $mismatchDetails = [];
            
            // Check expiry date
            $existingExpiryDate = $existingStock->expiryDate;
            if (is_object($existingExpiryDate) && method_exists($existingExpiryDate, 'format')) {
                $existingExpiryDate = $existingExpiryDate->format('Y-m-d');
            }
            $newExpiryDate = $request->expiryDate;
            if ($existingExpiryDate != $newExpiryDate) {
                $mismatches[] = "expiry date (should be: {$existingExpiryDate})";
                $mismatchDetails['expiryDate'] = $existingExpiryDate;
            }
            
            // Check purchase price
            if ((float)$existingStock->purchase_price != (float)$request->purchase_price) {
                $mismatches[] = "purchase price (should be: ₱" . number_format($existingStock->purchase_price, 2) . ")";
                $mismatchDetails['purchase_price'] = $existingStock->purchase_price;
            }
            
            // Check selling price
            if ((float)$existingStock->selling_price != (float)$request->selling_price) {
                $mismatches[] = "selling price (should be: ₱" . number_format($existingStock->selling_price, 2) . ")";
                $mismatchDetails['selling_price'] = $existingStock->selling_price;
            }
            
            // If there are mismatches, show error with suggestions
            if (!empty($mismatches)) {
                $mismatchText = implode(', ', $mismatches);
                $productName = Product::find($request->productID)->productName;
                $errorMessage = "Product '{$productName}' with batch number '{$request->batchNo}' already exists with different {$mismatchText}. Please use the suggested values to match the existing entry.";
                
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'mismatches' => $mismatchDetails,
                        'productName' => $productName,
                        'batchNo' => $request->batchNo
                    ]);
                }
                
                return redirect()
                    ->route('inventory.index')
                    ->with('error', $errorMessage);
            }
            
            // All fields match, update the existing stock quantity
            $existingStock->quantity += $request->quantity;
            $existingStock->save();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock quantity updated successfully!'
                ]);
            }
            
            return redirect()
                ->route('inventory.index')
                ->with('success', 'Stock quantity updated successfully!');
        } else {
            // Create a new stock entry
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

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock added successfully!'
                ]);
            }
            
            return redirect()
                ->route('inventory.index')
                ->with('success', 'Stock added successfully!');
        }
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

        // Check if the available quantity is now zero and delete if necessary
        if ($stock->available_quantity - $request->quantity <= 0) {
            // Delete the stock record to avoid unique constraint violation
            $stock->delete();
        }

        return redirect()->route('reports.index')->with('success', 'Stock out recorded successfully.');
    }
}
