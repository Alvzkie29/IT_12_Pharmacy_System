@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Near Expiry (≤ {{ $months }} months)</h3>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Back to Inventory</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Selling</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td class="text-start">{{ $stock->product->productName }} <small class="text-muted">({{ $stock->product->genericName }})</small></td>
                                <td class="text-center fw-bold">{{ $stock->available_quantity }}</td>
                                <td class="text-end">₱{{ number_format($stock->selling_price, 2) }}</td>
                                <td class="text-muted">{{ $stock->batchNo ?? 'N/A' }}</td>
                                <td class="text-danger fw-semibold">{{ \Carbon\Carbon::parse($stock->expiryDate)->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No near-expiry items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


