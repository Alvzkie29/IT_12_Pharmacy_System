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
    /**
     * Show POS with cart
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // Products (stock list)
        $stocks = Stock::with('product')
            ->when($search, function ($query, $search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('productName', 'like', "%{$search}%")
                    ->orWhere('genericName', 'like', "%{$search}%");
                });
            })
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()) // not expired
            ->where('quantity', '>', 0)            // has stock
            ->orderBy('created_at', 'desc')
            ->get();

        return view('sales.index', compact('stocks', 'search'));
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

    $cash = (float) $request->input('cash', 0);

    $subtotal = 0;
    $validCart = [];

    foreach ($cart as $item) {
        $stock = Stock::with('product')->find($item['stockID']);

        // ✅ Skip expired or unavailable
        if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->quantity <= 0) {
            continue;
        }

        $subtotal += $stock->selling_price * $item['quantity'];
        $validCart[$stock->stockID] = $item;
    }

    if (empty($validCart)) {
        return back()->with('error', 'No valid items in cart.');
    }

    // ✅ Tax + Total
    $grandTotal = $subtotal;

    if ($cash < $grandTotal) {
        return back()->with('error', 'Insufficient cash received.');
    }

    $change = $cash - $grandTotal;

    // Update session with only valid items
    session()->put('cart', $validCart);

    return view('sales.confirm', [
        'items'      => $validCart,
        'stocks'     => Stock::with('product')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->where('quantity', '>', 0)
            ->get(),
        'subtotal'   => $subtotal,
        'grandTotal' => $grandTotal,
        'cash'       => $cash,
        'change'     => $change
    ]);
}

public function finalize(Request $request)
{
    $cart = session()->get('cart', []);
    if (empty($cart)) {
        return redirect()->route('sales.index')->with('error', 'Cart is empty.');
    }

    $cash = (float) $request->input('cash', 0);

    $subtotal = 0;
    $validCart = [];

    foreach ($cart as $item) {
        $stock = Stock::with('product')->find($item['stockID']);

        // ✅ Skip expired/unavailable before finalizing
        if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->quantity <= 0) {
            continue;
        }

        $subtotal += $stock->selling_price * $item['quantity'];
        $validCart[$stock->stockID] = $item;
    }

    if (empty($validCart)) {
        return redirect()->route('sales.index')->with('error', 'No valid items in cart.');
    }

    
    $grandTotal = $subtotal;

    if ($cash < $grandTotal) {
        return back()->with('error', 'Insufficient cash received.');
    }

    DB::beginTransaction();
    try {
        // Create sale with TOTAL (including tax)
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
        session()->forget('cart'); // clear cart

        return redirect()->route('sales.index')
            ->with('success', 'Sale recorded successfully! Change: ₱' . number_format($cash - $grandTotal, 2));
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Sale failed: ' . $e->getMessage());
    }
}


}
