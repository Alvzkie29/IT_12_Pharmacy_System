@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    
    .inventory-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .inventory-table {
        margin: 0;
    }
    
    .inventory-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        text-align: center;
    }
    
    .inventory-table tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
        text-align: center;
    }
    
    .inventory-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }
    
    .inventory-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .expiry-status {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .expiry-status.expired {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }
    
    .expiry-status.warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
    }
    
    .expiry-status.safe {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .price-badge {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Inventory Management</h1>
                <p class="mb-0 opacity-75">Manage your pharmacy stock and inventory levels</p>
            </div>
            <div class="text-end">
                <i class="fas fa-boxes fa-3x opacity-50"></i>
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
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
                    id="inventorySearch" 
                    class="form-control border-start-0" 
                    placeholder="Search inventory by product name, batch number, or expiry date...">
            </div>
            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addStockModal">
                <i class="fas fa-plus me-2"></i>Add New Stock
            </button>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="inventory-card">
        <div class="table-responsive">
            <table class="table inventory-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">Product</th>
                        <th style="width: 120px;">Generic</th>
                        <th style="width: 80px;">Weight</th>
                        <th style="width: 80px;">Type</th>
                        <th style="width: 80px;">Quantity</th>
                        <th style="width: 100px;">Purchase Price</th>
                        <th style="width: 100px;">Selling Price</th>
                        <th style="width: 100px;">Batch No</th>
                        <th style="width: 120px;">Expiry Date</th>
                        <th style="width: 100px;">Expiry Status</th>
                        <th style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        @php
                            $expiryStatus = 'safe';
                            $expiryText = 'Safe';
                            if ($stock->expiryDate) {
                                $expiry = \Carbon\Carbon::parse($stock->expiryDate);
                                $today = \Carbon\Carbon::today();
                                $monthsDiff = $today->diffInMonths($expiry, false);

                                if ($monthsDiff < 0) {
                                    $expiryStatus = 'expired';
                                    $expiryText = 'Expired';
                                } elseif ($monthsDiff <= 6) {
                                    $expiryStatus = 'warning';
                                    $expiryText = 'Near Expiry';
                                }
                            }
                        @endphp

                        <tr>
                            <td class="fw-medium text-start">
                                <div class="d-flex align-items-center">
                                    <i class="me-2 text-primary"></i>
                                    {{ $stock->product->productName }}
                                </div>
                            </td>
                            <td class="text-start text-muted">{{ $stock->product->genericName }}</td>
                            <td class="text-muted">{{ $stock->product->productWeight }}</td>
                            <td class="text-muted">{{ $stock->product->dosageForm }}</td>
                            <td class="fw-bold text-primary">{{ $stock->available_quantity }}</td>
                            <td class="text-center">
                                <span class="price-badge">₱{{ number_format($stock->purchase_price, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="price-badge">₱{{ number_format($stock->selling_price, 2) }}</span>
                            </td>
                            <td class="text-muted">{{ $stock->batchNo ?? 'N/A' }}</td>
                            <td class="text-muted">
                                {{ $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate)->format('Y-m-d') : 'N/A' }}
                            </td>
                            <td class="text-center">
                                <span class="expiry-status {{ $expiryStatus }}">{{ $expiryText }}</span>
                            </td>
                            <td>
                                <button 
                                    class="btn btn-warning btn-sm"  
                                    data-bs-toggle="modal"
                                    data-bs-target="#stockOutModal{{ $stock->stockID }}"
                                    title="Stock Out">
                                    <i class="fas fa-box-arrow-down me-1"></i>Stock Out
                                </button>
                            </td>
                        </tr>

                            {{-- Stock Out Modal --}}
                            <div class="modal fade" id="stockOutModal{{ $stock->stockID }}" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog">
                                <form action="{{ route('inventory.stockOut', $stock->stockID) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Confirm stock out for this product?');">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                      <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title">Stock Out Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                      </div>

                                      <div class="modal-body">
                                          <p><strong>Product:</strong> {{ $stock->product->productName }} ({{ $stock->product->genericName }})</p>
                                          <p><strong>Available:</strong> {{ $stock->available_quantity }}</p>

                                          <div class="mb-3">
                                              <label class="form-label">Quantity to Stock Out</label>
                                              <input type="number" name="quantity" class="form-control" min="1" max="{{ $stock->available_quantity }}" required>
                                          </div>

                                          <div class="mb-3">
                                                <label class="form-label">Reason</label>
                                                <select name="reason" class="form-select" required>
                                                    <option value="expired">Expired</option>
                                                    <option value="pulled_out_near_expiry">Pulled Out - Near Expiry</option>
                                                    <option value="pulled_out_damaged">Pulled Out - Damaged</option>
                                                    <option value="pulled_out_contaminated">Pulled Out - Contaminated</option>
                                                    <option value="pulled_out_other">Pulled Out - Other</option>
                                                </select>
                                            </div>
                                      </div>

                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Confirm Stock Out</button>
                                      </div>
                                    </div>
                                </form>
                              </div>
                            </div>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-boxes fa-3x mb-3"></i>
                                    <h5>No inventory found</h5>
                                    <p>Start by adding your first stock using the button above.</p>
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
        {{ $stocks->links() }}
    </div>
</div>

{{-- Add Stock Modal --}}
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addStockForm" action="{{ route('inventory.stockIn') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="productID" class="form-label fw-semibold">
                                <i class="fas fa-pills me-2 text-primary"></i>Product
                            </label>
                            <select name="productID" id="productID" class="form-select form-select-lg" required>
                                <option value="" disabled selected>Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->productID }}">
                                        {{ $product->productName }} ({{ $product->genericName }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_price" class="form-label fw-semibold">
                                <i class="fas fa-dollar-sign me-2 text-primary"></i>Purchase Price
                            </label>
                            <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control form-control-lg" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="selling_price" class="form-label fw-semibold">
                                <i class="fas fa-tag me-2 text-primary"></i>Selling Price
                            </label>
                            <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control form-control-lg" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="quantity_in" class="form-label fw-semibold">
                                <i class="fas fa-boxes me-2 text-primary"></i>Quantity
                            </label>
                            <input type="number" name="quantity" id="quantity_in" class="form-control form-control-lg" min="1" placeholder="Enter quantity" required>
                        </div>
                        <div class="col-md-6">
                            <label for="batchNo" class="form-label fw-semibold">
                                <i class="fas fa-barcode me-2 text-primary"></i>Batch Number
                            </label>
                            <input type="text" name="batchNo" id="batchNo" class="form-control form-control-lg" placeholder="Enter batch number">
                        </div>
                        <div class="col-12">
                            <label for="expiryDate" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>Expiry Date
                            </label>
                            <input type="date" name="expiryDate" id="expiryDate" class="form-control form-control-lg">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="addStockForm" class="btn btn-success btn-lg">
                    <i class="fas fa-save me-2"></i>Add Stock
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('inventorySearch').addEventListener('keyup', function () {
    let query = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        // skip "No inventory found" row
        if (row.querySelector("td")?.classList.contains("text-muted")) {
            row.style.display = query === "" ? "" : "none";
            return;
        }

        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? "" : "none";
    });
});
</script>

@endsection
