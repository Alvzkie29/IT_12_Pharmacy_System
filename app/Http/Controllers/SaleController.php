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
    // Existing cart items
    $items = collect($request->items ?? []);

    // Handle 'Add' button
    if ($request->has('add_item')) {
        $stockID = $request->input('add_item');
        
        // Check if item already exists in cart
        $existingKey = $items->search(fn($item) => $item['stockID'] == $stockID);
        if ($existingKey !== false) {
            // Increase quantity if already in cart
            $items[$existingKey]['quantity'] += 1;
        } else {
            // Add new item with quantity 1
            $items->push([
                'stockID' => $stockID,
                'quantity' => 1,
            ]);
        }
    }

    // Now check if cart has items
    if ($items->isEmpty()) {
        return back()->with('error', 'Please select at least one product.')->withInput();
    }

    $cash = (float) $request->input('cash', 0);
    $subtotal = 0;

    // Calculate subtotal
    foreach ($items as $item) {
        $stock = Stock::with('product')->find($item['stockID']);
        if ($stock) {
            $subtotal += $stock->selling_price * ($item['quantity'] ?? 1);
        }
    }

    if ($cash < $subtotal && $cash > 0) {
        return back()->with('error', 'Insufficient cash received.')->withInput();
    }

    // Return to the same POS view with updated items
    return view('sales.index', [
        'stocks' => Stock::with('product')->get(),
        'items' => $items,
        'cash' => $cash,
    ]);
}

/**
 * Finalize sale after confirmation
 */
public function confirm(Request $request)
{
    $items = collect($request->items ?? []);
    $cash = (float) $request->input('cash', 0);

    DB::beginTransaction();

    try {
        $sale = Sale::create([
            'employeeID'  => Auth::user()->employeeID,
            'totalAmount' => 0,
            'saleDate'    => now(),
        ]);

        $totalAmount = 0;

        foreach ($items as $item) {
            $stock = Stock::with('product')->findOrFail($item['stockID']);
            $quantity = (int) $item['quantity'];

            if ($quantity > $stock->quantity) {
                throw new \Exception("Not enough stock for {$stock->product->productName}");
            }

            $lineTotal = $stock->selling_price * $quantity;
            $totalAmount += $lineTotal;

            Transaction::create([
                'saleID'   => $sale->saleID,
                'stockID'  => $stock->stockID,
                'quantity' => $quantity,
            ]);

            $stock->quantity -= $quantity;
            if ($stock->quantity <= 0) {
                $stock->availability = false;
            }
            $stock->save();
        }

        $sale->update(['totalAmount' => $totalAmount]);

        DB::commit();

        return redirect()->route('sales.index')
            ->with('success', "Sale recorded successfully! Change: ₱" . number_format($cash - $totalAmount, 2));
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Sale failed: ' . $e->getMessage())->withInput();
    }
}

}
