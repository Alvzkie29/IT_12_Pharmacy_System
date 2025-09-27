@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 fw-bold">PRODUCTS LIST</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search + Add Button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" action="{{ route('products.index') }}" class="d-flex" style="max-width: 350px; flex: 1;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
        <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </button>
    </div>

    {{-- Products Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Product List</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Brand Name</th>
                            <th>Generic Name</th>
                            <th>Weight</th>
                            <th>Type</th>
                            <th>Supplier</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->productName }}</td>
                                <td>{{ $product->genericName ?? 'N/A' }}</td>
                                <td>{{ $product->productWeight ?? 'N/A' }}</td>
                                <td>{{ $product->dosageForm ?? 'N/A' }}</td>
                                <td>{{ $product->supplier->supplierName }}</td>
                                <td>{{ $product->category }}</td>
                                <td>{{ $product->description ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-end mt-3">
    {{ $products->links() }}
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('products.store') }}" 
              method="POST"
              onsubmit="return confirm('Confirm adding this new product?');">
            @csrf
            <div class="mb-3">
                <label for="supplierID" class="form-label">Supplier</label>
                <select name="supplierID" class="form-select" required>
                    <option value="" disabled selected>Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->supplierID }}">{{ $supplier->supplierName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="productName" class="form-label">Product Name</label>
                <input type="text" name="productName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="genericName" class="form-label">Generic Name</label>
                <input type="text" name="genericName" class="form-control">
            </div>
            <div class="mb-3">
                <label for="productWeight" class="form-label">Product Weight (e.g., 500mg)</label>
                <input type="text" name="productWeight" class="form-control">
            </div>
            <div class="mb-3">
                <label for="dosageForm" class="form-label">Dosage Form</label>
                <select name="dosageForm" class="form-select">
                    <option value="Tablet">Tablet</option>
                    <option value="Capsule">Capsule</option>
                    <option value="Syrup">Syrup</option>
                    <option value="Injection">Injection</option>
                    <option value="Cream">Cream</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="Antibiotic">Antibiotic</option>
                    <option value="Vitamins">Vitamins</option>
                    <option value="Prescription">Prescription</option>
                    <option value="Analgesic">Analgesic</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100">Save Product</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
