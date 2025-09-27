@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Confirm Sale</h1>

    <div class="row">
        {{-- Left: Items Table --}}
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    Items in Cart
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach($items as $item)
                                @php
                                    $stock = $stocks->firstWhere('stockID', $item['stockID']);
                                    $qty = $item['quantity'];
                                    $lineTotal = $stock->selling_price * $qty;
                                    $total += $lineTotal;
                                @endphp
                                <tr @if($stock->quantity <= 30) class="table-warning" @endif>
                                    <td>{{ $stock->product->productName }}</td>
                                    <td>₱{{ number_format($stock->selling_price, 2) }}</td>
                                    <td>{{ $qty }}</td>
                                    <td>₱{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(collect($items)->contains(fn($i) => $stock->firstWhere('stockID', $i['stockID'])->quantity <=5))
                        <p class="text-warning mt-2"><small>⚠ Some items are low in stock!</small></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Summary & Receipt --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    Payment Summary
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Cash Received:</span>
                        <span>₱{{ number_format($cash, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
                        <span>Change:</span>
                        <span>₱{{ number_format($change, 2) }}</span>
                    </div>

                    <form method="POST" action="{{ route('sales.finalize') }}">
                        @csrf
                        @foreach($items as $item)
                            <input type="hidden" name="items[{{ $loop->index }}][stockID]" value="{{ $item['stockID'] }}">
                            <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $item['quantity'] }}">
                        @endforeach
                        <input type="hidden" name="cash" value="{{ $cash }}">
                        <button type="submit" class="btn btn-success w-100 mb-3">Confirm Sale</button>
                    </form>

                    {{-- Mini Receipt --}}
                    <div class="card p-2" style="font-family: monospace; font-size: 0.9rem;">
                        <h6 class="text-center mb-2">Receipt</h6>
                        <table class="table table-borderless table-sm mb-2">
                            <tbody>
                                @foreach($items as $item)
                                    @php
                                        $stock = $stocks->firstWhere('stockID', $item['stockID']);
                                        $qty = $item['quantity'];
                                        $lineTotal = $stock->selling_price * $qty;
                                    @endphp
                                    <tr>
                                        <td>{{ $stock->product->productName }} x{{ $qty }}</td>
                                        <td class="text-end">₱{{ number_format($lineTotal,2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td class="text-end">₱{{ number_format($subtotal,2) }}</td>
                                </tr>
                                <tr>
                                    <td>Cash</td>
                                    <td class="text-end">₱{{ number_format($cash,2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Change</strong></td>
                                    <td class="text-end">₱{{ number_format($change,2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="text-center mb-0"><small>Thank you for your purchase!</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
