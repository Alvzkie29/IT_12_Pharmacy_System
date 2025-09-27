@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Confirm Checkout</h2>

    <form method="POST" action="{{ route('sales.confirm') }}">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                    @php
                        $stock = \App\Models\Stock::with('product')->find($item['stockID']);
                        $qty = $item['quantity'];
                        $lineTotal = $stock->selling_price * $qty;
                    @endphp
                    <tr>
                        <td>{{ $stock->product->productName }}</td>
                        <td>₱{{ number_format($stock->selling_price, 2) }}</td>
                        <td>{{ $qty }}</td>
                        <td>₱{{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    <input type="hidden" name="items[{{ $i }}][stockID]" value="{{ $stock->stockID }}">
                    <input type="hidden" name="items[{{ $i }}][quantity]" value="{{ $qty }}">
                @endforeach
            </tbody>
        </table>

        <div class="text-end">
            <h5>Subtotal: ₱{{ number_format($subtotal, 2) }}</h5>
            <h5>Cash: ₱{{ number_format($cash, 2) }}</h5>
            <h4 class="text-success">Change: ₱{{ number_format($change, 2) }}</h4>
        </div>

        <input type="hidden" name="cash" value="{{ $cash }}">

        <div class="mt-3 d-flex justify-content-between">
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success">Confirm Sale</button>
        </div>
    </form>
</div>
@endsection
