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
    $lowStockThreshold = 30;

    $stocksQuery = Stock::with(['product', 'supplier'])
        ->where('type', 'IN')
        ->where('availability', true)
        ->where(function($query) use ($lowStockThreshold) {
            // Include expired products (regardless of stock level)
            $query->whereDate('expiryDate', '<=', now()->startOfDay())
                  // OR include safe products (beyond 6 months) that are not low stock
                  ->orWhere(function($q) use ($lowStockThreshold) {
                      $q->whereDate('expiryDate', '>', now()->addMonths(6))
                        ->where('quantity', '>', $lowStockThreshold);
                  });
        })
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

    // Get all products and active suppliers
    $products = Product::all();
    $suppliers = Supplier::where('is_active', true)->get();

    return view('inventory.index', compact('stocks', 'products', 'suppliers', 'search'));
}

    /**
     * Near Expiry listing
     */
    public function nearExpiry(Request $request)
{
    $months = 6; // Fixed threshold
    $lowStockThreshold = 30; // Fixed threshold
    $thresholdDate = now()->addMonths($months)->startOfDay();

    // Get near-expiry stocks ONLY (regardless of stock quantity)
    $nearExpiryStocks = Stock::with('product')
        ->where('type', 'IN')
        ->where('availability', true)
        ->whereDate('expiryDate', '>', now()->startOfDay()) // Exclude today's date
        ->whereDate('expiryDate', '<=', $thresholdDate) // Expiring within 6 months
        ->orderBy('expiryDate', 'asc')
        ->get()
        ->filter(function ($stock) {
            return $stock->available_quantity > 0;
        });

    // Get low stock items ONLY (excluding expired products)
    $lowStocks = Stock::with('product')
        ->where('type', 'IN')
        ->where('availability', true)
        ->where('quantity', '<=', $lowStockThreshold) // Low stock only (<= 10)
        ->whereDate('expiryDate', '>', now()->startOfDay()) // Exclude expired products (today or earlier)
        ->orderBy('quantity', 'asc')
        ->get()
        ->filter(function ($stock) {
            return $stock->available_quantity > 0;
        });

    return view('inventory.near_expiry', compact('nearExpiryStocks', 'lowStocks', 'months', 'lowStockThreshold'));
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
            'supplierID'         => 'required|exists:suppliers,supplierID',
            'productID'          => 'required|exists:products,productID',
            'selling_price'      => 'required|numeric|min:0',
            'package_total_cost' => 'nullable|numeric|min:0',
            'quantity'           => 'required|integer|min:1',
            'batchNo'            => 'nullable|string|max:50',
            'expiryDate'         => 'nullable|date|after:today',
        ]);
        
        // Check if the supplier is active
        $supplier = Supplier::findOrFail($request->supplierID);
        if (!$supplier->is_active) {
            return back()->with('error', 'Cannot add stock for inactive suppliers.');
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
                'supplierID'         => $request->supplierID,
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
 * Now supports bulk pull-out of all near-expiry items
 */
public function stockOut(Request $request, $id = null)
{
    // Check if this is a bulk pull-out of all near-expiry items
    if ($request->has('pull_all_near_expiry') && $request->pull_all_near_expiry == '1') {
        return $this->pullOutAllNearExpiry($request);
    }

    $request->validate([
        'quantity' => 'required|integer|min:1',
        'reason'   => 'required|string',
    ]);

    $stock = Stock::findOrFail($id);

    if ($request->quantity > $stock->available_quantity) {
        return back()->with('error', 'Not enough stock available.');
    }

    // Map reason to consistent format
    $mappedReason = $request->reason;
    if (false) { // Removed pulled_out_expired option
        $mappedReason = 'pulled_out_near_expiry';
    }

    // Insert a separate OUT row (only for history)
    Stock::create([
        'supplierID'     => $stock->supplierID,
        'productID'      => $stock->productID,
        'employeeID'     => Auth::user()->employeeID, 
        'type'           => 'OUT',
        'reason'         => $mappedReason, // Use mapped reason
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

/**
 * Helper method to pull out all near-expiry items
 */
/**
 * Helper method to pull out all near-expiry items
 */
private function pullOutAllNearExpiry(Request $request)
{
    $months = (int) ($request->input('months', 6));
    $thresholdDate = now()->addMonths($months)->startOfDay();

    // Get all near-expiry stocks
    $nearExpiryStocks = Stock::with('product')
        ->where('type', 'IN')
        ->where('availability', true)
        ->whereDate('expiryDate', '>', now()->startOfDay()) // Exclude today's date
        ->whereDate('expiryDate', '<=', $thresholdDate)
        ->get()
        ->filter(function ($stock) {
            return $stock->available_quantity > 0;
        });

    if ($nearExpiryStocks->isEmpty()) {
        return redirect()
            ->route('inventory.near-expiry')
            ->with('error', 'No near-expiry items found to pull out.');
    }

    $pulledCount = 0;

    foreach ($nearExpiryStocks as $stock) {
        // Create OUT record for each near-expiry stock with correct reason
        Stock::create([
            'supplierID'     => $stock->supplierID,
            'productID'      => $stock->productID,
            'employeeID'     => Auth::user()->employeeID,
            'type'           => 'OUT',
            'reason'         => 'pulled_out_near_expiry', // Use specific reason
            'purchase_price' => $stock->purchase_price,
            'selling_price'  => $stock->selling_price,
            'quantity'       => $stock->available_quantity,
            'availability'   => false,
            'batchNo'        => $stock->batchNo,
            'expiryDate'     => $stock->expiryDate,
            'movementDate'   => now(),
        ]);

        // Delete the original stock record since we've pulled out all available quantity
        $stock->delete();

        $pulledCount++;
    }

    return redirect()
        ->route('inventory.nearExpiry')
        ->with('success', "Successfully pulled out {$pulledCount} near-expiry items.");
}

// Method for manual expiry status update removed as it's now handled automatically in AppServiceProvider

/**
 * Restock low stock items
 */
public function restock(Request $request, $id)
{
    $request->validate([
        'additional_quantity' => 'required|integer|min:1',
        'purchase_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'batchNo' => 'nullable|string|max:50',
        'expiryDate' => 'required|date|after:today',
    ]);

    $originalStock = Stock::findOrFail($id);
    $lowStockThreshold = 30; // Same threshold
    
    // Check if there's an existing stock with the same product and batch number
    $existingStock = Stock::where('productID', $originalStock->productID)
        ->where('batchNo', $request->batchNo)
        ->where('type', 'IN')
        ->where('availability', true)
        ->first();

    if ($existingStock && $request->batchNo) {
        // Check if prices match
        if ((float)$existingStock->purchase_price != (float)$request->purchase_price || 
            (float)$existingStock->selling_price != (float)$request->selling_price) {
            return back()->with('error', 'Prices do not match existing batch. Please use the same prices or create a new batch.');
        }

        // Update existing stock quantity
        $existingStock->quantity += $request->additional_quantity;
        $existingStock->save();

        $message = 'Stock restocked successfully!';
        
        // Check if item should move back to main inventory
        if ($existingStock->available_quantity > $lowStockThreshold) {
            $message .= ' Item has been moved back to main inventory.';
        }

        return redirect()
            ->route('inventory.nearExpiry')
            ->with('success', $message);
    } else {
        // Create new stock entry
        Stock::create([
            'supplierID'     => $originalStock->supplierID,
            'productID'      => $originalStock->productID,
            'employeeID'     => Auth::user()->employeeID,
            'type'           => 'IN',
            'purchase_price' => $request->purchase_price,
            'selling_price'  => $request->selling_price,
            'quantity'       => $request->additional_quantity,
            'availability'   => true,
            'batchNo'        => $request->batchNo,
            'expiryDate'     => $request->expiryDate,
            'movementDate'   => now(),
        ]);

        $message = 'New stock added successfully!';
        
        // If the new stock quantity is above threshold, it will automatically appear in main inventory
        if ($request->additional_quantity > $lowStockThreshold) {
            $message .= ' Item will appear in main inventory.';
        } else {
            $message .= ' Item remains in low stock.';
        }

        return redirect()
            ->route('inventory.nearExpiry')
            ->with('success', $message);
    }
}
}
