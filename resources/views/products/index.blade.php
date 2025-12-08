@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .search-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .products-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        background: white;
    }
    
    .products-table {
        margin: 0;
    }
    
    .products-table thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .products-table tbody td {
        padding: 0.875rem 1rem;
        border: none;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .products-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }
    
    .products-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .btn-action {
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 12px 12px 0 0;
        padding: 1.25rem 1.5rem;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .category-badge {
        padding: 0.5rem 0.75rem;
        
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid;
    }
    
    .category-badge.Antibiotic {
        background: #e8f5e8;
        color: #2e7d32;
        border-color: #c8e6c9;
    }
    
    .category-badge.Vitamins {
        background: #fff3e0;
        color: #ef6c00;
        border-color: #ffe0b2;
    }
    
    .category-badge.Prescription {
        background: #f3e5f5;
        color: #7b1fa2;
        border-color: #e1bee7;
    }
    
    .category-badge.Analgesic {
        background: #e3f2fd;
        color: #1565c0;
        border-color: #bbdefb;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Products Management</h1>
                <p class="mb-0 opacity-75">Manage your pharmacy products and inventory</p>
            </div>
            <div class="text-end">
                <i class="fas fa-pills fa-2x opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search Section --}}
    <div class="search-section">
        <div class="d-flex justify-content-between align-items-center">
            <div class="input-group" style="max-width: 500px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input 
                    type="text" 
                    id="searchInput" 
                    class="form-control border-start-0" 
                    placeholder="Search products by name, generic name, or category...">
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                Add
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="products-card">
        <div class="table-responsive">
            <table class="table products-table">
                <thead>
                    <tr>
                        <th class="text-start">Brand Name</th>
                        <th class="text-start">Generic Name</th>
                        <th class="text-start">Weight</th>
                        <th class="text-start">Type</th>
                        <th class="text-center">Category</th>
                        <th class="text-start">Description</th>
                        <th style="width: 140px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="fw-medium text-start">{{ $product->productName }}</td>
                            <td class="text-start">{{ $product->genericName ?? 'N/A' }}</td>
                            <td class="text-muted text-start">{{ $product->productWeight ?? 'N/A' }}</td>
                            <td class="text-muted text-start">{{ $product->dosageForm ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="category-badge {{ $product->category }}">{{ $product->category }}</span>
                            </td>
                            <td class="text-start text-muted small">{{ Str::limit($product->description ?? 'No description', 50) }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->productID }}" title="Edit Product">
                                        Edit
                                    </button>
                                    <form action="{{ route('products.destroy', $product->productID) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-action" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete Product">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Product Modal --}}
                        <div class="modal fade" id="editProductModal{{ $product->productID }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('products.update', $product->productID) }}" method="POST" onsubmit="return confirm('Confirm updating this product?');">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="productName{{ $product->productID }}" class="form-label fw-semibold">Product Name</label>
                                                <input type="text" name="productName" class="form-control" value="{{ $product->productName }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="genericName{{ $product->productID }}" class="form-label fw-semibold">Generic Name</label>
                                                <input type="text" name="genericName" class="form-control" value="{{ $product->genericName }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="productWeight{{ $product->productID }}" class="form-label fw-semibold">Product Weight</label>
                                                <input type="text" name="productWeight" class="form-control" value="{{ $product->productWeight }}" placeholder="e.g., 500mg">
                                            </div>
                                            <div class="mb-3">
                                                <label for="dosageForm{{ $product->productID }}" class="form-label fw-semibold">Dosage Form</label>
                                                <select name="dosageForm" class="form-select">
                                                    <option value="Tablet" {{ $product->dosageForm == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                                                    <option value="Capsule" {{ $product->dosageForm == 'Capsule' ? 'selected' : '' }}>Capsule</option>
                                                    <option value="Syrup" {{ $product->dosageForm == 'Syrup' ? 'selected' : '' }}>Syrup</option>
                                                    <option value="Injection" {{ $product->dosageForm == 'Injection' ? 'selected' : '' }}>Injection</option>
                                                    <option value="Cream" {{ $product->dosageForm == 'Cream' ? 'selected' : '' }}>Cream</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category{{ $product->productID }}" class="form-label fw-semibold">Category</label>
                                                <select name="category" class="form-select" required>
                                                    <option value="Antibiotic" {{ $product->category == 'Antibiotic' ? 'selected' : '' }}>Antibiotic</option>
                                                    <option value="Vitamins" {{ $product->category == 'Vitamins' ? 'selected' : '' }}>Vitamins</option>
                                                    <option value="Prescription" {{ $product->category == 'Prescription' ? 'selected' : '' }}>Prescription</option>
                                                    <option value="Analgesic" {{ $product->category == 'Analgesic' ? 'selected' : '' }}>Analgesic</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description{{ $product->productID }}" class="form-label fw-semibold">Description</label>
                                                <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success w-100">Update Product</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-pills fa-3x mb-3"></i>
                                    <h5>No products found</h5>
                                    <p class="mb-0">Start by adding your first product using the button above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
    @endif
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addProductForm" action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="productName" class="form-label fw-semibold">Product Name</label>
                            <input type="text" name="productName" class="form-control" placeholder="Enter product name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="genericName" class="form-label fw-semibold">Generic Name</label>
                            <input type="text" name="genericName" class="form-control" placeholder="Enter generic name">
                        </div>
                        <div class="col-md-6">
                            <label for="productWeight" class="form-label fw-semibold">Product Weight</label>
                            <input type="text" name="productWeight" class="form-control" placeholder="e.g., 500mg">
                        </div>
                        <div class="col-md-6">
                            <label for="dosageForm" class="form-label fw-semibold">Dosage Form</label>
                            <select name="dosageForm" class="form-select">
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Cream">Cream</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="Antibiotic">Antibiotic</option>
                                <option value="Vitamins">Vitamins</option>
                                <option value="Prescription">Prescription</option>
                                <option value="Analgesic">Analgesic</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter product description"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addProductForm" class="btn btn-success">Save Product</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("searchInput").addEventListener("input", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

@endsection