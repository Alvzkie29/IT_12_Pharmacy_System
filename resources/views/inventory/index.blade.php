@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 fw-bold">STOCK LIST</h1>
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Title + Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" action="{{ route('inventory.index') }}" class="d-flex" style="max-width: 350px; flex: 1;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search stock..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
        <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#addStockModal">
            <i class="bi bi-plus-circle me-1"></i> Add Stock
        </button>
    </div>

    {{-- Inventory Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Inventory List</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Batch No</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Expiry Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td>{{ $stock->product->productName }}</td>
                                <td>{{ $stock->batchNo ?? 'N/A' }}</td>
                                <td>{{ $stock->quantity }}</td>
                                <td>₱{{ number_format($stock->price, 2) }}</td>
                                <td>{{ $stock->expiryDate ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $stock->type == 'IN' ? 'success' : 'danger' }}">
                                        {{ $stock->type }}
                                    </span>
                                </td>
                                <td>
                                    @if($stock->availability)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-secondary">Unavailable</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stock->availability && $stock->type == 'IN')
                                        <form action="{{ route('inventory.stockOut', $stock->stockID) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-box-arrow-up"></i> Out of Stock
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No stock records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        {{ $stocks->links() }}
    </div>
</div>

{{-- Add Stock Modal --}}
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-sm">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('inventory.stockIn') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="productID" class="form-label">Product</label>
                <select name="productID" class="form-select" required>
                    <option value="" disabled selected>Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->productID }}">{{ $product->productName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Batch Price</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="batchNo" class="form-label">Batch Number</label>
                <input type="text" name="batchNo" class="form-control">
            </div>
            <div class="mb-3">
                <label for="expiryDate" class="form-label">Expiry Date</label>
                <input type="date" name="expiryDate" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Stock</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
