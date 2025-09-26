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
     * List sales and/or stock movements
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Sales with employees
        $sales = Sale::with('employee')
            ->when($search, function ($query, $search) {
                return $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('firstName', 'like', "%$search%")
                      ->orWhere('lastName', 'like', "%$search%");
                });
            })
            ->orderBy('saleDate', 'desc')
            ->paginate(10, ['*'], 'sales_page');

        // Stock movements with products
        $stocks = Stock::with('product')
            ->when($search, function ($query, $search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('productName', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'stocks_page');

        return view('sales.index', compact('sales', 'stocks', 'search'));
    }

    /**
     * Store new sale
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'   => 'required|array|min:1',
        ]);

        // Filter out empty rows (no product or no quantity)
        $items = collect($request->items)
            ->filter(fn($item) => !empty($item['stockID']) && !empty($item['quantity']));

        if ($items->isEmpty()) {
            return back()->with('error', 'Please select at least one product.')->withInput();
        }

        DB::beginTransaction();

        try {
            // 1. Create the sale record
            $sale = Sale::create([
                'employeeID'  => Auth::user()->employeeID,
                'totalAmount' => 0, // will update later
                'saleDate'    => now(),
            ]);

            $totalAmount = 0;

            // 2. Loop through sale items
            foreach ($items as $item) {
                $stock = Stock::with('product')->findOrFail($item['stockID']);

                // Check if enough stock exists
                if ($item['quantity'] > $stock->quantity) {
                    throw new \Exception("Not enough stock for {$stock->product->productName}");
                }

                $lineTotal = $stock->selling_price * $item['quantity'];
                $totalAmount += $lineTotal;

                // Create transaction record
                Transaction::create([
                    'saleID'    => $sale->saleID,
                    'stockID'   => $stock->stockID,
                    'quantity'  => $item['quantity'],
                ]);

                // Deduct stock
                $stock->quantity -= $item['quantity'];
                if ($stock->quantity <= 0) {
                    $stock->availability = false;
                }
                $stock->save();
            }

            // 3. Update total amount in sale
            $sale->update(['totalAmount' => $totalAmount]);

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sale failed: ' . $e->getMessage())->withInput();
        }
    }
}
