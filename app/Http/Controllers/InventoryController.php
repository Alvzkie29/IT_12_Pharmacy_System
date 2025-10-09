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
            // Exclude near-expiry (<=6 months) from main list; they are shown in Near Expiry page
            ->whereDate('expiryDate', '>', now()->addMonths(6))
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

        // Only get products with active suppliers
        $products = Product::whereHas('supplier', function($query) {
            $query->where('is_active', true);
        })->get();
        $suppliers = Supplier::all();

        return view('inventory.index', compact('stocks', 'products', 'suppliers', 'search'));
    }

    /**
     * Near Expiry listing
     */
    public function nearExpiry(Request $request)
    {
        $months = (int) ($request->input('months', 6));
        $thresholdDate = now()->addMonths($months)->startOfDay();

        $stocks = Stock::with('product')
            ->where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->whereDate('expiryDate', '<=', $thresholdDate)
            ->orderBy('expiryDate', 'asc')
            ->get()
            ->filter(function ($stock) {
                return $stock->available_quantity > 0;
            });

        return view('inventory.near_expiry', compact('stocks', 'months'));
    }

    /**
     * Helper: fetch last known purchase/selling price for a product from recent stock-ins
     */
    public function lastPrice(Request $request)
    {
        $productID = $request->input('productID');
        if (!$productID) {
            return response()->json(['success' => false, 'message' => 'Missing productID'], 422);
        }

        $last = Stock::where('productID', $productID)
            ->where('type', 'IN')
            ->orderByDesc('created_at')
            ->first(['purchase_price', 'selling_price']);

        if (!$last) {
            return response()->json(['success' => true, 'purchase_price' => null, 'selling_price' => null]);
        }

        return response()->json([
            'success' => true,
            'purchase_price' => (float) $last->purchase_price,
            'selling_price' => (float) $last->selling_price,
        ]);
    }

    /**
     * Stock In - Add new stock entry
     */
    public function stockIn(Request $request)
    {
        $request->validate([
            'productID'          => 'required|exists:products,productID',
            'selling_price'      => 'required|numeric|min:0',
            'package_total_cost' => 'nullable|numeric|min:0',
            'quantity'           => 'required|integer|min:1',
            'batchNo'            => 'nullable|string|max:50',
            'expiryDate'         => 'nullable|date|after:today',
        ]);
        
        // Check if the product's supplier is active
        $product = Product::with('supplier')->findOrFail($request->productID);
        if (!$product->supplier || !$product->supplier->is_active) {
            return back()->with('error', 'Cannot add stock for products with inactive suppliers.');
        }

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
            
            // Compute intended purchase price per piece from package total / quantity
            $computedPurchasePrice = null;
            if ($request->filled('package_total_cost') && (int)$request->quantity > 0) {
                $computedPurchasePrice = round(((float)$request->package_total_cost) / (int)$request->quantity, 2);
            }
            // Check purchase price against computed
            if (!is_null($computedPurchasePrice) && (float)$existingStock->purchase_price != (float)$computedPurchasePrice) {
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
            // Compute purchase price per piece (from package_total_cost / quantity) if provided
            $purchasePerPiece = 0;
            if ($request->filled('package_total_cost') && (int)$request->quantity > 0) {
                $purchasePerPiece = round(((float)$request->package_total_cost) / (int)$request->quantity, 2);
            }

            Stock::create([
                'productID'          => $request->productID,
                'employeeID'         => Auth::user()->employeeID,
                'type'               => 'IN',
                'purchase_price'     => $purchasePerPiece,
                'selling_price'      => $request->selling_price,
                'package_total_cost' => $request->package_total_cost,
                'quantity'           => $request->quantity,
                'availability'       => true,
                'batchNo'            => $request->batchNo,
                'expiryDate'         => $request->expiryDate,
                'movementDate'       => now(),
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
