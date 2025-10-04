@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .search-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }
    
    .products-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .products-table {
        margin: 0;
    }
    
    .products-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        text-align: center;
    }
    
    .products-table tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
        text-align: center;
    }
    
    .products-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }
    
    .products-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .btn-action {
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .category-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .category-badge.Antibiotic {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }
    
    .category-badge.Vitamins {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .category-badge.Prescription {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
    }
    
    .category-badge.Analgesic {
        background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%);
        color: white;
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
                <i class="fas fa-pills fa-3x opacity-50"></i>
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
            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i>Add New Product
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="products-card">
        <div class="table-responsive">
            <table class="table products-table">
                <thead>
                    <tr>
                        <th style="width: 200px;">Brand Name</th>
                        <th style="width: 150px;">Generic Name</th>
                        <th style="width: 100px;">Weight</th>
                        <th style="width: 100px;">Type</th>
                        <th style="width: 150px;">Supplier</th>
                        <th style="width: 120px;">Category</th>
                        <th>Description</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="fw-medium text-start">{{ $product->productName }}</td>
                            <td class="text-start">{{ $product->genericName ?? 'N/A' }}</td>
                            <td class="text-muted">{{ $product->productWeight ?? 'N/A' }}</td>
                            <td class="text-muted">{{ $product->dosageForm ?? 'N/A' }}</td>
                            <td class="text-start">
                                <i class="fas fa-truck me-2 text-muted"></i>{{ $product->supplier->supplierName }}
                            </td>
                            <td class="text-center">
                                <span class="category-badge {{ $product->category }}">{{ $product->category }}</span>
                            </td>
                            <td class="text-start text-muted small">{{ Str::limit($product->description ?? 'No description', 50) }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->productID }}" title="Edit Product">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('products.destroy', $product->productID) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-action" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete Product">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                            {{-- Edit Product Modal --}}
                            <div class="modal fade" id="editProductModal{{ $product->productID }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content shadow-sm">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Edit Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('products.update', $product->productID) }}" method="POST" onsubmit="return confirm('Confirm updating this product?');">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label for="supplierID{{ $product->productID }}" class="form-label">Supplier</label>
                                                    <select name="supplierID" class="form-select" required>
                                                        <option value="" disabled>Select supplier</option>
                                                        @foreach($suppliers as $supplier)
                                                            <option value="{{ $supplier->supplierID }}" {{ $product->supplierID == $supplier->supplierID ? 'selected' : '' }}>
                                                                {{ $supplier->supplierName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productName{{ $product->productID }}" class="form-label">Product Name</label>
                                                    <input type="text" name="productName" class="form-control" value="{{ $product->productName }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="genericName{{ $product->productID }}" class="form-label">Generic Name</label>
                                                    <input type="text" name="genericName" class="form-control" value="{{ $product->genericName }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productWeight{{ $product->productID }}" class="form-label">Product Weight (e.g., 500mg)</label>
                                                    <input type="text" name="productWeight" class="form-control" value="{{ $product->productWeight }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="dosageForm{{ $product->productID }}" class="form-label">Dosage Form</label>
                                                    <select name="dosageForm" class="form-select">
                                                        <option value="Tablet" {{ $product->dosageForm == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                                                        <option value="Capsule" {{ $product->dosageForm == 'Capsule' ? 'selected' : '' }}>Capsule</option>
                                                        <option value="Syrup" {{ $product->dosageForm == 'Syrup' ? 'selected' : '' }}>Syrup</option>
                                                        <option value="Injection" {{ $product->dosageForm == 'Injection' ? 'selected' : '' }}>Injection</option>
                                                        <option value="Cream" {{ $product->dosageForm == 'Cream' ? 'selected' : '' }}>Cream</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="category{{ $product->productID }}" class="form-label">Category</label>
                                                    <select name="category" class="form-select" required>
                                                        <option value="Antibiotic" {{ $product->category == 'Antibiotic' ? 'selected' : '' }}>Antibiotic</option>
                                                        <option value="Vitamins" {{ $product->category == 'Vitamins' ? 'selected' : '' }}>Vitamins</option>
                                                        <option value="Prescription" {{ $product->category == 'Prescription' ? 'selected' : '' }}>Prescription</option>
                                                        <option value="Analgesic" {{ $product->category == 'Analgesic' ? 'selected' : '' }}>Analgesic</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description{{ $product->productID }}" class="form-label">Description (optional)</label>
                                                    <textarea name="description" class="form-control">{{ $product->description }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100">Update Product</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-pills fa-3x mb-3"></i>
                                    <h5>No products found</h5>
                                    <p>Start by adding your first product using the button above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addProductForm" action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="supplierID" class="form-label fw-semibold">
                                <i class="fas fa-truck me-2 text-primary"></i>Supplier
                            </label>
                            <select name="supplierID" class="form-select form-select-lg" required>
                                <option value="" disabled selected>Select supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->supplierID }}">{{ $supplier->supplierName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="productName" class="form-label fw-semibold">
                                <i class="fas fa-pills me-2 text-primary"></i>Product Name
                            </label>
                            <input type="text" name="productName" class="form-control form-control-lg" placeholder="Enter product name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="genericName" class="form-label fw-semibold">
                                <i class="fas fa-tag me-2 text-primary"></i>Generic Name
                            </label>
                            <input type="text" name="genericName" class="form-control form-control-lg" placeholder="Enter generic name">
                        </div>
                        <div class="col-md-6">
                            <label for="productWeight" class="form-label fw-semibold">
                                <i class="fas fa-weight me-2 text-primary"></i>Product Weight
                            </label>
                            <input type="text" name="productWeight" class="form-control form-control-lg" placeholder="e.g., 500mg">
                        </div>
                        <div class="col-md-6">
                            <label for="dosageForm" class="form-label fw-semibold">
                                <i class="fas fa-capsules me-2 text-primary"></i>Dosage Form
                            </label>
                            <select name="dosageForm" class="form-select form-select-lg">
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Cream">Cream</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label fw-semibold">
                                <i class="fas fa-list me-2 text-primary"></i>Category
                            </label>
                            <select name="category" class="form-select form-select-lg" required>
                                <option value="Antibiotic">Antibiotic</option>
                                <option value="Vitamins">Vitamins</option>
                                <option value="Prescription">Prescription</option>
                                <option value="Analgesic">Analgesic</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-2 text-primary"></i>Description (optional)
                            </label>
                            <textarea name="description" class="form-control form-control-lg" rows="3" placeholder="Enter product description"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="addProductForm" class="btn btn-success btn-lg">
                    <i class="fas fa-save me-2"></i>Save Product
                </button>
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
