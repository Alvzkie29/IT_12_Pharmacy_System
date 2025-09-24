@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Reports</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('reports.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by product, batch, or reason..." 
                   value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    {{-- Stock Movements Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Stock Movements
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Batch</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Quantity</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $movement)
                            <tr>
                                <td>{{ $movement->product->productName ?? 'Unknown' }}</td>
                                <td>{{ $movement->batchNo ?? 'N/A' }}</td>
                                <td>
                                    @if($movement->type === 'IN')
                                        <span class="badge bg-success">IN</span>
                                    @else
                                        <span class="badge bg-danger">OUT</span>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->reason === 'expired')
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($movement->reason === 'damaged')
                                        <span class="badge bg-warning">Damaged</span>
                                    @elseif($movement->reason === 'pullout')
                                        <span class="badge bg-info">Pullout</span>
                                    @elseif($movement->reason === 'sale')
                                        <span class="badge bg-primary">Sale</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $movement->reason ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->movementDate->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No stock movements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
