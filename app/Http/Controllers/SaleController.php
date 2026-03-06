<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            ->where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->orderBy('expiryDate', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->filter(fn($stock) => $stock->available_quantity > 0);

        $cart = session()->get('cart', []);
        $hasPrescription = $this->cartHasPrescription($cart);

        $subtotal = 0;
        foreach ($cart as $item) {
            $stock = Stock::find($item['stockID']);
            if ($stock) $subtotal += $stock->selling_price * $item['quantity'];
        }

        return view('sales.index', compact('stocks', 'search', 'hasPrescription', 'cart', 'subtotal'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);

        // Handle add, update, manual qty, remove
        if ($request->has('add_item')) {
            $stockID = $request->input('add_item');
            $stock = Stock::find($stockID);
            if ($stock) {
                $cart[$stockID]['quantity'] = isset($cart[$stockID])
                    ? min($cart[$stockID]['quantity'] + 1, $stock->quantity)
                    : 1;
                $cart[$stockID]['stockID'] = $stockID;
            }
        }

        if ($request->has('update_item')) {
            [$action, $stockID] = explode('-', $request->update_item);
            $stock = Stock::find($stockID);
            if ($stock && isset($cart[$stockID])) {
                if ($action === 'inc') $cart[$stockID]['quantity'] = min($cart[$stockID]['quantity'] + 1, $stock->quantity);
                if ($action === 'dec') $cart[$stockID]['quantity'] = max($cart[$stockID]['quantity'] - 1, 1);
            }
        }

        if ($request->has('items')) {
            foreach ($request->items as $stockID => $data) {
                $stock = Stock::find($stockID);
                if ($stock && isset($cart[$stockID])) {
                    $qty = max(1, min((int)$data['quantity'], $stock->quantity));
                    $cart[$stockID]['quantity'] = $qty;
                }
            }
        }

        if ($request->has('remove_item')) {
            unset($cart[$request->remove_item]);
        }

        session()->put('cart', $cart);

        $subtotal = 0;
        foreach ($cart as $item) {
            $stock = Stock::find($item['stockID']);
            if ($stock) $subtotal += $stock->selling_price * $item['quantity'];
        }

        $hasPrescription = $this->cartHasPrescription($cart);
        $grandTotal = $subtotal;
        $cash = (float) $request->input('cash', 0);

        $stocks = Stock::with('product')
            ->where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->orderBy('expiryDate', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->filter(fn($stock) => $stock->available_quantity > 0);

        return view('sales.index', [
            'stocks' => $stocks,
            'items' => $cart,
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'cash' => $cash,
            'hasPrescription' => $hasPrescription,
        ]);
    }

    public function updateCart(Request $request)
    {
        $cart = session()->get('cart', []);
        $stockID = $request->stockID;
        $stock = Stock::find($stockID);
        if (!$stock) return response()->json(['success' => false, 'message' => 'Product not found']);

        $qty = max(1, min((int)$request->quantity, $stock->available_quantity));
        if (isset($cart[$stockID])) $cart[$stockID]['quantity'] = $qty;
        session()->put('cart', $cart);

        $itemSubtotal = $stock->selling_price * $qty;
        $total = 0;
        foreach ($cart as $c) {
            $s = Stock::find($c['stockID']);
            if ($s) $total += $s->selling_price * $c['quantity'];
        }

        return response()->json([
            'success' => true,
            'itemSubtotal' => number_format($itemSubtotal, 2),
            'total' => number_format($total, 2),
            'quantity' => $qty,
            'maxQuantity' => $stock->available_quantity
        ]);
    }

    public function showConfirm(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->route('sales.index')->with('error', 'Cart is empty.');

        $cash = (float) ($request->input('cash') ?? session('confirm_cash', 0));
        $isDiscounted = (int) ($request->input('isDiscounted') ?? session('confirm_discount', 0));

        $subtotal = 0;
        $validCart = [];

        foreach ($cart as $item) {
            $stock = Stock::with('product')->find($item['stockID']);
            if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->available_quantity <= 0) continue;

            $lineTotal = $stock->selling_price * $item['quantity'];
            $subtotal += $lineTotal;

            $validCart[$stock->stockID] = [
                'stockID' => $stock->stockID,
                'name' => $stock->product->productName,
                'quantity' => $item['quantity'],
                'price' => $stock->selling_price,
            ];
        }

        if (empty($validCart)) return redirect()->route('sales.index')->with('error', 'No valid items in cart.');

        $grandTotal = $isDiscounted ? round($subtotal * 0.80, 2) : round($subtotal, 2);

        if ($cash < $grandTotal) return redirect()->route('sales.index')
            ->with('error', 'Insufficient cash received. Please enter at least ₱' . number_format($grandTotal, 2));

        $change = round($cash - $grandTotal, 2);

        return view('sales.confirm', [
            'items' => $validCart,
            'stocks' => Stock::getAvailableStock(),
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'cash' => $cash,
            'change' => $change,
            'isDiscounted' => $isDiscounted,
        ]);
    }

    public function confirm(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return back()->with('error', 'Cart is empty.');

        $cash = (float) $request->input('cash', 0);
        $isDiscounted = (int) ($request->input('isDiscounted') ?? $request->input('discountApplied') ?? $request->input('discounted') ?? 0);

        $subtotal = 0;
        $validCart = [];

        foreach ($cart as $item) {
            $stock = Stock::with('product')->find($item['stockID']);
            if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->available_quantity <= 0) continue;

            $lineTotal = $stock->selling_price * $item['quantity'];
            $subtotal += $lineTotal;

            $validCart[$stock->stockID] = [
                'stockID' => $stock->stockID,
                'name' => $stock->product->productName,
                'quantity' => $item['quantity'],
                'price' => $stock->selling_price,
            ];
        }

        if (empty($validCart)) return back()->with('error', 'No valid items in cart.');

        $grandTotal = $isDiscounted ? round($subtotal * 0.80, 2) : round($subtotal, 2);

        if ($cash < $grandTotal) return back()->with('error', 'Insufficient cash received. Please enter at least ₱' . number_format($grandTotal, 2));

        session()->put('confirm_cash', $cash);
        session()->put('confirm_discount', $isDiscounted);
        session()->put('cart', $validCart);

        return view('sales.confirm', [
            'items' => $validCart,
            'stocks' => Stock::getAvailableStock(),
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'cash' => $cash,
            'change' => round($cash - $grandTotal, 2),
            'isDiscounted' => $isDiscounted,
        ]);
    }

    public function finalize(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->route('sales.index')->with('error', 'Cart is empty.');

        $cash = (float) ($request->input('cash') ?? 0);
        $isDiscounted = (int) ($request->input('isDiscounted') ?? $request->input('discountApplied') ?? $request->input('discounted') ?? 0);

        $subtotal = 0;
        $validCart = [];

        foreach ($cart as $item) {
            $stock = Stock::with('product')->find($item['stockID']);
            if (!$stock || !$stock->availability || $stock->expiryDate <= now() || $stock->available_quantity <= 0) continue;

            $lineTotal = $stock->selling_price * $item['quantity'];
            $subtotal += $lineTotal;

            $validCart[$stock->stockID] = [
                'stockID' => $stock->stockID,
                'name' => $stock->product->productName,
                'quantity' => $item['quantity'],
                'price' => $stock->selling_price,
            ];
        }

        if (empty($validCart)) return redirect()->route('sales.index')->with('error', 'No valid items in cart.');

        $grandTotal = $isDiscounted ? round($subtotal * 0.80, 2) : round($subtotal, 2);

        if ($cash < $grandTotal) return back()->with('error', 'Insufficient cash received.');

        $change = round($cash - $grandTotal, 2);

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'employeeID' => Auth::user()->employeeID,
                'cash_received' => $cash,
                'change_given' => $change,
                'totalAmount' => $grandTotal,
                'isDiscounted' => $isDiscounted,
                'subtotal' => $subtotal,
                'discountAmount' => $isDiscounted ? round($subtotal * 0.20, 2) : 0,
                'saleDate' => now(),
            ]);

            foreach ($validCart as $item) {
                $stock = Stock::with('product')->find($item['stockID']);
                $quantity = $item['quantity'];

                if ($quantity > $stock->available_quantity) {
                    throw new \Exception("Not enough stock for {$stock->product->productName}");
                }

                Transaction::create([
                    'saleID' => $sale->saleID,
                    'stockID' => $stock->stockID,
                    'quantity' => $quantity,
                ]);

                Stock::create([
                    'supplierID' => $stock->supplierID,
                    'productID' => $stock->productID,
                    'employeeID' => Auth::user()->employeeID,
                    'type' => 'OUT',
                    'reason' => 'sold',
                    'purchase_price' => $stock->purchase_price,
                    'selling_price' => $stock->selling_price,
                    'quantity' => $quantity,
                    'availability' => false,
                    'batchNo' => $stock->batchNo,
                    'expiryDate' => $stock->expiryDate,
                    'movementDate' => now(),
                ]);

                if ($stock->available_quantity - $quantity <= 0) {
                    $stock->delete();
                }
            }

            session()->forget('cart');
            session()->forget('confirm_cash');
            session()->forget('confirm_discount');

            $receiptHTML = $request->input('receipt_html');

            if ($receiptHTML) {
                $filename = 'receipt_' . $sale->saleID . '.html';
                Storage::disk('s3')->put('receipts/' . $filename, $receiptHTML);
                $sale->update(['receipt_path' => 'receipts/' . $filename]);
            }

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale recorded successfully! Change: ₱' . number_format($change, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sale failed: ' . $e->getMessage());
        }
    }
}