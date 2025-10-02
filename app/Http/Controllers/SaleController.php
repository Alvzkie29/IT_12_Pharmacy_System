<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{

    protected function cartHasPrescription(array $cart): bool
    {
        if (empty($cart)) return false;

        foreach ($cart as $item) {
            if (!isset($item['stockID'])) continue;

            $stock = Stock::with('product')->find($item['stockID']);
            if (!$stock || !$stock->product) continue;

            if ($stock->product->category === 'Prescription') {
                return true;
            }
        }

        return false;
    }
    /**
     * Show POS with cart
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $stocks = Stock::with('product')
            ->when($search, function ($query, $search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('productName', 'like', "%{$search}%")
                    ->orWhere('genericName', 'like', "%{$search}%");
                });
            })
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $cart = session()->get('cart', []);
        $hasPrescription = $this->cartHasPrescription($cart);

        return view('sales.index', compact('stocks', 'search', 'hasPrescription'));
    }

    /**
     * Step 1: Handle cart actions and go to confirmation
     */
    public function store(Request $request)
{
    // Get current cart from session
    $cart = session()->get('cart', []);

    // Handle Add button
    if ($request->has('add_item')) {
        $stockID = $request->input('add_item');
        $stock = Stock::find($stockID);

        if ($stock) {
            if (isset($cart[$stockID])) {
                // Increase but don't exceed available stock
                $cart[$stockID]['quantity'] = min(
                    $cart[$stockID]['quantity'] + 1,
                    $stock->quantity
                );
            } else {
                $cart[$stockID] = [
                    'stockID'  => $stockID,
                    'quantity' => 1,
                ];
            }
        }
    }

    // Handle update (inc/dec buttons)
    if ($request->has('update_item')) {
        [$action, $stockID] = explode('-', $request->update_item);
        $stock = Stock::find($stockID);

        if ($stock && isset($cart[$stockID])) {
            if ($action === 'inc') {
                $cart[$stockID]['quantity'] = min(
                    $cart[$stockID]['quantity'] + 1,
                    $stock->quantity
                );
            }
            if ($action === 'dec') {
                $cart[$stockID]['quantity'] = max(
                    $cart[$stockID]['quantity'] - 1,
                    1
                );
            }
        }
    }

    // ✅ Handle manual quantity input
    if ($request->has('items')) {
        foreach ($request->items as $stockID => $data) {
            $stock = Stock::find($stockID);
            if ($stock && isset($cart[$stockID])) {
                $qty = (int) $data['quantity'];
                // Clamp between 1 and available stock
                $qty = max(1, min($qty, $stock->quantity));
                $cart[$stockID]['quantity'] = $qty;
            }
        }
    }

    // Handle remove button
    if ($request->has('remove_item')) {
        $removeID = $request->remove_item;
        unset($cart[$removeID]);
    }

    // Save back to session
    session()->put('cart', $cart);

    // Calculate subtotal
    $subtotal = 0;
    foreach ($cart as $item) {
        $stock = Stock::find($item['stockID']);
        if ($stock) {
            $subtotal += $stock->selling_price * $item['quantity'];
        }
    }

    // Check if cart has prescription items
    $hasPrescription = $this->cartHasPrescription($cart);

 
    $grandTotal = $subtotal;

    $cash = (float) $request->input('cash', 0);

    // Return POS view with updated cart
    $stocks = Stock::with('product')
        ->where('availability', true)
        ->whereDate('expiryDate', '>', now())
        ->where('quantity', '>', 0)
        ->get();

    return view('sales.index', [
        'stocks'     => $stocks,
        'items'      => $cart,
        'subtotal'   => $subtotal,
        'grandTotal' => $grandTotal,
        'cash'       => $cash,
        'hasPrescription'=> $hasPrescription,
    ]);
}

public function updateCart(Request $request)
{
    $cart = session()->get('cart', []);
    $stockID = $request->stockID;    
    $qty = max(1, (int) $request->quantity);

    if (isset($cart[$stockID])) {
        $cart[$stockID]['quantity'] = $qty;
        session()->put('cart', $cart);
    }

    // Get stock from DB
    $stock = Stock::find($stockID);
    $itemSubtotal = $stock ? $stock->selling_price * $qty : 0;

    // Recalculate total
    $total = 0;
    foreach ($cart as $c) {
        $s = Stock::find($c['stockID']); // Always use stockID
        if ($s) {
            $total += $s->selling_price * $c['quantity'];
        }
    }

    return response()->json([
        'success' => true,
        'itemSubtotal' => number_format($itemSubtotal, 2),
        'total' => number_format($total, 2)
    ]);
}
/**
 * Finalize sale after confirmation
 */
public function confirm(Request $request)
{
    $cart = session()->get('cart', []);

    if (empty($cart)) {
        return back()->with('error', 'Cart is empty.');
    }

    // Cash and discount flag (accept multiple possible field names for safety)
    $cash = (float) $request->input('cash', 0);
    $isDiscounted = (int) (
        $request->input('isDiscounted') ??
        $request->input('discountApplied') ??
        $request->input('discounted') ??
        0
    );

    $subtotal = 0;
    $validCart = [];

    foreach ($cart as $item) {
        $stock = Stock::with('product')->find($item['stockID']);

        // Skip expired/unavailable
        if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->quantity <= 0) {
            continue;
        }

        $lineTotal = $stock->selling_price * $item['quantity'];
        $subtotal += $lineTotal;

        // Enrich the cart item so blade can use price/name/quantity
        $validCart[$stock->stockID] = [
            'stockID'  => $stock->stockID,
            'name'     => $stock->product->productName,
            'quantity' => $item['quantity'],
            'price'    => $stock->selling_price,
        ];
    }

    if (empty($validCart)) {
        return back()->with('error', 'No valid items in cart.');
    }

    // Apply discount if checked (20% off)
    $grandTotal = $isDiscounted ? round($subtotal * 0.80, 2) : round($subtotal, 2);

    if ($cash < $grandTotal) {
        return back()->with('error', 'Insufficient cash received.');
    }

    $change = round($cash - $grandTotal, 2);

    // Update session with only valid items (including price)
    session()->put('cart', $validCart);

    return view('sales.confirm', [
        'items'       => $validCart,
        'stocks'      => Stock::with('product')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->where('quantity', '>', 0)
            ->get(),
        'subtotal'    => $subtotal,
        'grandTotal'  => $grandTotal,
        'cash'        => $cash,
        'change'      => $change,
        'isDiscounted'=> $isDiscounted,
    ]);
}



public function finalize(Request $request)
{
    $cart = session()->get('cart', []);
    if (empty($cart)) {
        return redirect()->route('sales.index')->with('error', 'Cart is empty.');
    }

    $cash = (float) $request->input('cash', 0);
    $isDiscounted = (int) (
        $request->input('isDiscounted') ??
        $request->input('discountApplied') ??
        $request->input('discounted') ??
        0
    );

    $subtotal = 0;
    $validCart = [];

    foreach ($cart as $item) {
        $stock = Stock::with('product')->find($item['stockID']);

        // Skip expired/unavailable
        if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->quantity <= 0) {
            continue;
        }

        $lineTotal = $stock->selling_price * $item['quantity'];
        $subtotal += $lineTotal;

        // Enrich item
        $validCart[$stock->stockID] = [
            'stockID'  => $stock->stockID,
            'name'     => $stock->product->productName,
            'quantity' => $item['quantity'],
            'price'    => $stock->selling_price,
        ];
    }

    if (empty($validCart)) {
        return redirect()->route('sales.index')->with('error', 'No valid items in cart.');
    }

    $grandTotal = $isDiscounted ? round($subtotal * 0.80, 2) : round($subtotal, 2);

    if ($cash < $grandTotal) {
        return back()->with('error', 'Insufficient cash received.');
    }

    DB::beginTransaction();
    try {
        $sale = Sale::create([
            'employeeID'  => Auth::user()->employeeID,
            'totalAmount' => $grandTotal,
            'saleDate'    => now(),
        ]);

        foreach ($validCart as $item) {
            $stock = Stock::with('product')->find($item['stockID']);
            $quantity = $item['quantity'];

            if ($quantity > $stock->quantity) {
                throw new \Exception("Not enough stock for {$stock->product->productName}");
            }

            Transaction::create([
                'saleID'  => $sale->saleID,
                'stockID' => $stock->stockID,
                'quantity'=> $quantity,
            ]);

            $stock->quantity -= $quantity;
            if ($stock->quantity <= 0) $stock->availability = false;
            $stock->save();
        }

        DB::commit();
        session()->forget('cart');

        return redirect()->route('sales.index')
            ->with('success', 'Sale recorded successfully! Change: ₱' . number_format($cash - $grandTotal, 2));
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Sale failed: ' . $e->getMessage());
    }
}


}
