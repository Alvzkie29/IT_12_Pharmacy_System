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
                    $q->where('productName', 'like', "%$search%");
                });
            })
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

        if (isset($cart[$stockID])) {
            $cart[$stockID]['quantity'] += 1;
        } else {
            $cart[$stockID] = [
                'stockID' => $stockID,
                'quantity' => 1,
            ];
        }
    }

    // Handle update/decrease buttons
    if ($request->has('update_item')) {
        [$action, $stockID] = explode('-', $request->update_item);

        if (isset($cart[$stockID])) {
            if ($action === 'inc') $cart[$stockID]['quantity'] += 1;
            if ($action === 'dec' && $cart[$stockID]['quantity'] > 1) $cart[$stockID]['quantity'] -= 1;
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
        if ($stock) $subtotal += $stock->selling_price * $item['quantity'];
    }

    $cash = (float) $request->input('cash', 0);

    // Return POS view with updated cart
    $stocks = Stock::with('product')->get();
    return view('sales.index', [
        'stocks' => $stocks,
        'items' => $cart,
        'subtotal' => $subtotal,
        'cash' => $cash,
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

    // Calculate subtotal
    $subtotal = 0;
    foreach ($cart as $item) {
        $stock = Stock::find($item['stockID']);
        if ($stock) {
            $subtotal += $stock->selling_price * $item['quantity'];
        }
    }

    if ($cash < $subtotal) {
        return back()->with('error', 'Insufficient cash received.');
    }

    $change = $cash - $subtotal;

    // Pass cart, subtotal, cash, and change to confirm view
    return view('sales.confirm', [
        'items' => $cart,
        'stocks' => Stock::with('product')->get(),
        'subtotal' => $subtotal,
        'cash' => $cash,
        'change' => $change
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
    foreach ($cart as $item) {
        $stock = Stock::find($item['stockID']);
        if ($stock) {
            $subtotal += $stock->selling_price * $item['quantity'];
        }
    }

    if ($cash < $subtotal) {
        return back()->with('error', 'Insufficient cash received.');
    }

    DB::beginTransaction();
    try {
        // Create sale
        $sale = Sale::create([
            'employeeID' => Auth::user()->employeeID,
            'totalAmount' => $subtotal,
            'saleDate' => now(),
        ]);

        foreach ($cart as $item) {
            $stock = Stock::find($item['stockID']);
            $quantity = $item['quantity'];

            if ($quantity > $stock->quantity) {
                throw new \Exception("Not enough stock for {$stock->product->productName}");
            }

            Transaction::create([
                'saleID' => $sale->saleID,
                'stockID' => $stock->stockID,
                'quantity' => $quantity,
            ]);

            $stock->quantity -= $quantity;
            if ($stock->quantity <= 0) $stock->availability = false;
            $stock->save();
        }

        DB::commit();
        session()->forget('cart'); // clear cart

        return redirect()->route('sales.index')
            ->with('success', 'Sale recorded successfully! Change: ₱' . number_format($cash - $subtotal, 2));
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Sale failed: ' . $e->getMessage());
    }
}

}
