@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Point of Sale</h1>

    {{-- ✅ Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.store') }}">
        @csrf

        <div class="row">
            {{-- LEFT: Cart --}}
            <div class="col-md-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        Current Sale
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0 align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @forelse(old('items', []) as $i => $item)
                                    @php
                                        $stock = $stocks->firstWhere('stockID', $item['stockID'] ?? null);
                                        $qty = $item['quantity'] ?? 1;
                                    @endphp
                                    @if($stock)
                                        @php
                                            $lineTotal = $qty * $stock->selling_price;
                                            $subtotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $stock->product->productName }}</td>
                                            <td>₱{{ number_format($stock->selling_price, 2) }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center align-items-center">
                                                    {{-- Decrease button --}}
                                                    <button type="submit" name="update_item" value="dec-{{ $i }}" class="btn btn-sm btn-warning me-1">-</button>

                                                    <input type="number" 
                                                           name="items[{{ $i }}][quantity]" 
                                                           value="{{ $qty }}" 
                                                           min="1" 
                                                           class="form-control form-control-sm text-center w-50">

                                                    {{-- Increase button --}}
                                                    <button type="submit" name="update_item" value="inc-{{ $i }}" class="btn btn-sm btn-success ms-1">+</button>
                                                </div>
                                                <input type="hidden" name="items[{{ $i }}][stockID]" value="{{ $stock->stockID }}">
                                            </td>
                                            <td>₱{{ number_format($lineTotal, 2) }}</td>
                                            <td>
                                                {{-- Remove button --}}
                                                <button type="submit" name="remove_item" value="{{ $i }}" class="btn btn-danger btn-sm">x</button>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No items added</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Totals + Cash --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Cash Received</label>
                                <input type="number" step="0.01" name="cash" class="form-control" value="{{ old('cash') }}">
                            </div>
                            <div class="col-md-6 text-end">
                                <p><strong>Subtotal:</strong> ₱{{ number_format($subtotal, 2) }}</p>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            {{-- Checkout goes to confirmation page --}}
                            <button type="submit" class="btn btn-success">Proceed to Checkout</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Product List --}}
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        Products
                    </div>
                    <div class="card-body" style="max-height: 75vh; overflow-y: auto;">
                        <div class="row row-cols-2 g-2">
                            @php $hasProducts = false; @endphp
                            @foreach($stocks as $stock)
                                @if($stock->quantity > 0)
                                    @php $hasProducts = true; @endphp
                                    <div class="col">
                                        <div class="card h-100 border">
                                            <div class="card-body text-center p-2">
                                                <strong>{{ $stock->product->productName }}</strong>
                                                <p class="small mb-1">{{ $stock->product->genericName }}</p>
                                                <p class="small mb-1">{{ $stock->expiryDate }}</p>
                                                <p class="small mb-1">₱{{ number_format($stock->selling_price, 2) }}</p>
                                                <p class="small text-muted">Stock: {{ $stock->quantity }}</p>
                                                {{-- Add button --}}
                                                <button type="submit" name="add_item" value="{{ $stock->stockID }}" class="btn btn-primary btn-sm w-100">
                                                    Add
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Fallback --}}
                            @if(!$hasProducts)
                                <div class="col-12">
                                    <div class="alert alert-warning text-center m-2">
                                        No products available.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
