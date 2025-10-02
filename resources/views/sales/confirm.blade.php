@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Confirm Sale</h1>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            Order Summary
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0; @endphp
                    @foreach($items as $item)
                        @php
                            $stock = \App\Models\Stock::with('product')->find($item['stockID']);
                            if (!$stock) continue;
                            $lineTotal = $stock->selling_price * $item['quantity'];
                            $subtotal += $lineTotal;
                        @endphp
                        <tr>
                            <td>{{ $stock->product->productName }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>₱{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">Subtotal</td>
                        <td>₱{{ number_format($subtotal, 2) }}</td>
                    </tr>

                    {{-- ✅ Discount row --}}
                    @php
                        $discount = 0;
                        if($isDiscounted){
                            $discount = $subtotal * 0.20; // 20% off
                        }
                        $grandTotal = $subtotal - $discount;
                        $change = $cash - $grandTotal;
                    @endphp

                    @if($isDiscounted)
                    <tr class="text-success fw-bold">
                        <td colspan="2">Discount (20% - Senior/PWD)</td>
                        <td>-₱{{ number_format($discount, 2) }}</td>
                    </tr>
                    @endif

                    <tr class="fw-bold">
                        <td colspan="2">Grand Total</td>
                        <td>₱{{ number_format($grandTotal, 2) }}</td>
                    </tr>

                    <tr>
                        <td colspan="2">Cash Received</td>
                        <td>₱{{ number_format($cash, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Change</td>
                        <td>₱{{ number_format($change, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ✅ Confirm Button --}}
    <div class="mt-3">
        <form method="POST" action="{{ route('sales.finalize') }}">
            @csrf
            <input type="hidden" name="cash" value="{{ $cash }}">
            <input type="hidden" name="isDiscounted" value="{{ $isDiscounted }}">
            <button type="submit" class="btn btn-success w-100">Confirm & Complete Sale</button>
        </form>
    </div>
</div>
@endsection
