@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 0;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .inventory-card {
        border: none;
        border-radius: 0;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .inventory-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
    }
    
    .inventory-table tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
    }
    
    .product-main-info {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .product-secondary-info {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .stock-info-badge {
        background: #e9ecef;
        color: #495057;
        padding: 0.5rem 0.75rem;
        border-radius: 0;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .quantity-display {
        font-size: 1.1rem;
        font-weight: 700;
    }
    
    .quantity-low { color: #dc3545; }
    .quantity-medium { color: #fd7e14; }
    .quantity-high { color: #28a745; }
    
    .expiry-status {
        padding: 0.5rem 0.75rem;
        border-radius: 0;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .expiry-status.expired { background: #dc3545; color: white; }
    .expiry-status.warning { background: #ffc107; color: black; }
    .expiry-status.safe { background: #28a745; color: white; }
    
    .detail-row {
        background: #f8f9fa;
    }
    
    .detail-item {
        padding: 0.5rem 1rem;
        border-right: 1px solid #dee2e6;
    }
    
    .detail-item:last-child {
        border-right: none;
    }
    
    .action-buttons .btn {
        margin: 0.125rem;
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

    {{-- Search and Filters --}}
    <div class="card search-section mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="inventorySearch" class="form-control" 
                               placeholder="Search products...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select id="expiryFilter" class="form-select">
                        <option value="">All Expiry Status</option>
                        <option value="expired">Expired</option>
                        <option value="warning">Near Expiry</option>
                        <option value="safe">Safe</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="quantityFilter" class="form-select">
                        <option value="">All Stock Levels</option>
                        <option value="low">Low Stock</option>
                        <option value="medium">Medium Stock</option>
                        <option value="high">High Stock</option>
                    </select>
                </div>
                <!-- Manual update button removed as expiry status is now updated automatically -->
                <div class="col-md-2">
                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <i class="fas fa-plus me-2"></i>Add Stock
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="inventory-card">
        <div class="table-responsive">
            <table class="table inventory-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Product Information</th>
                        <th style="width: 15%;">Supplier</th>
                        <th style="width: 10%;">Stock Level</th>
                        <th style="width: 15%;">Pricing</th>
                        <th style="width: 15%;">Batch & Expiry</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        @php
                            // Expiry status calculation
                            $expiryStatus = 'safe';
                            $expiryText = 'Safe';
                            if ($stock->expiryDate) {
                                $expiry = \Carbon\Carbon::parse($stock->expiryDate);
                                $today = \Carbon\Carbon::today();
                                
                                // Mark as expired if today or earlier
                                if ($expiry->lte($today)) {
                                    $expiryStatus = 'expired';
                                    $expiryText = 'Expired';
                                } elseif ($expiry->lte($today->copy()->addMonths(6))) {
                                    $expiryStatus = 'warning';
                                    $expiryText = 'Near Expiry';
                                }
                            }

                            // Quantity status
                            $quantityClass = 'quantity-high';
                            if ($stock->available_quantity <= 30) {
                                $quantityClass = 'quantity-low';
                            } elseif ($stock->available_quantity <= 50) {
                                $quantityClass = 'quantity-medium';
                            }
                        @endphp

                        <tr class="main-row" data-stock-id="{{ $stock->stockID }}">
                            <td>
                                <div class="product-main-info">
                                    <i class="fas fa-pills me-2 text-primary"></i>
                                    {{ $stock->product->productName }}
                                </div>
                                <div class="product-secondary-info">
                                    {{ $stock->product->genericName }} • 
                                    {{ $stock->product->productWeight }} • 
                                    {{ $stock->product->dosageForm }}
                                </div>
                            </td>
                            <td>
                                <div class="product-secondary-info">
                                    <i class="fas fa-truck me-2"></i>
                                    {{ $stock->supplier->supplierName }}
                                    @if(!$stock->supplier || !$stock->supplier->is_active)
                                        <span class="badge bg-danger ms-1" title="Inactive supplier">!</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="quantity-display {{ $quantityClass }}">
                                    {{ $stock->available_quantity }} pcs
                                </span>
                            </td>
                            <td>
                                <div class="product-secondary-info">
                                    <div>Buy: ₱{{ number_format($stock->purchase_price, 2) }}</div>
                                    <div>Sell: ₱{{ number_format($stock->selling_price, 2) }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="product-secondary-info">
                                    <div>Batch: {{ $stock->batchNo ?? 'N/A' }}</div>
                                    <div>Exp: {{ $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate)->format('M Y') : 'N/A' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="expiry-status {{ $expiryStatus }}">{{ $expiryText }}</span>
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary toggle-details" 
                                        data-bs-toggle="tooltip" title="View Details">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Expandable Details Row --}}
                        <tr class="detail-row d-none" id="details-{{ $stock->stockID }}">
                            <td colspan="7">
                                <div class="row text-center">
                                    <div class="col detail-item">
                                        <small class="text-muted">Package Cost</small>
                                        <div class="fw-bold">₱{{ number_format($stock->package_total_cost ?? 0, 2) }}</div>
                                    </div>
                                    <div class="col detail-item">
                                        <small class="text-muted">Full Expiry</small>
                                        <div class="fw-bold">{{ $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate)->format('Y-m-d') : 'N/A' }}</div>
                                    </div>
                                    <div class="col detail-item">
                                        <small class="text-muted">Days to Expiry</small>
                                        <div class="fw-bold">
                                            @if($stock->expiryDate)
                                                {{ \Carbon\Carbon::parse($stock->expiryDate)->diffInDays(\Carbon\Carbon::today()) }} days
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col detail-item">
                                        <small class="text-muted">Stock Value</small>
                                        <div class="fw-bold">
                                            ₱{{ number_format($stock->available_quantity * $stock->purchase_price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
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

{{-- Add Stock Modal (keep your existing modal) --}}
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addStockForm" action="{{ route('inventory.stockIn') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="supplierID" class="form-label fw-semibold">
                                <i class="fas fa-truck me-2 text-primary"></i>Supplier
                            </label>
                            <select name="supplierID" id="supplierID" class="form-select form-select-lg" required>
                                <option value="" disabled selected>Select supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->supplierID }}">{{ $supplier->supplierName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
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
                            <label for="selling_price" class="form-label fw-semibold">
                                <i class="fas a-tag me-2 text-primary"></i>Selling Price / Piece
                            </label>
                            <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control form-control-lg" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="package_total_cost" class="form-label fw-semibold">
                                <i class="fas fa-boxes me-2 text-primary"></i>Package Total (incl. delivery)
                                <i class="fas fa-info-circle text-muted ms-1" 
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top"
                                title="Purchase price per piece will auto-calculate: package total / quantity per piece"></i>
                            </label>
                            <input type="number" step="0.01" name="package_total_cost" id="package_total_cost" class="form-control form-control-lg" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label for="quantity_in" class="form-label fw-semibold">
                                <i class="fas fa-boxes me-2 text-primary"></i>Quantity per Piece
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
                    Cancel
                </button>
                <button type="submit" form="addStockForm" class="btn btn-success btn-lg">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-calculate purchase price when package total or quantity changes
    const packageTotalInput = document.getElementById('package_total_cost');
    const quantityInput = document.getElementById('quantity_in');
    const purchasePriceInput = document.getElementById('purchase_price');

    function calculatePurchasePrice() {
        if (packageTotalInput.value && quantityInput.value) {
            const packageTotal = parseFloat(packageTotalInput.value);
            const quantity = parseInt(quantityInput.value);
            
            if (quantity > 0) {
                const purchasePricePerPiece = packageTotal / quantity;
                purchasePriceInput.value = purchasePricePerPiece.toFixed(2);
            }
        }
    }

    if (packageTotalInput && quantityInput && purchasePriceInput) {
        packageTotalInput.addEventListener('input', calculatePurchasePrice);
        quantityInput.addEventListener('input', calculatePurchasePrice);
    }

    // Your existing toggle-details and filter code...
    document.querySelectorAll('.toggle-details').forEach(button => {
        button.addEventListener('click', function() {
            const stockId = this.closest('tr').getAttribute('data-stock-id');
            const detailRow = document.getElementById(`details-${stockId}`);
            const icon = this.querySelector('i');
            
            detailRow.classList.toggle('d-none');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    });

    // Search functionality
    document.getElementById('inventorySearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        filterTable();
    });

    // Filter functionality
    document.getElementById('expiryFilter').addEventListener('change', filterTable);
    document.getElementById('quantityFilter').addEventListener('change', filterTable);

    function filterTable() {
        const searchQuery = document.getElementById('inventorySearch').value.toLowerCase();
        const expiryFilter = document.getElementById('expiryFilter').value;
        const quantityFilter = document.getElementById('quantityFilter').value;

        document.querySelectorAll('.main-row').forEach(row => {
            if (row.querySelector('.text-muted')) return; // Skip empty row

            const text = row.textContent.toLowerCase();
            const expiryStatus = row.querySelector('.expiry-status').classList[1];
            const quantity = parseInt(row.querySelector('.quantity-display').textContent);
            
            let quantityLevel = 'high';
            if (quantity <=30) quantityLevel = 'low';
            else if (quantity <= 50) quantityLevel = 'medium';

            const matchesSearch = text.includes(searchQuery);
            const matchesExpiry = !expiryFilter || expiryStatus === expiryFilter;
            const matchesQuantity = !quantityFilter || quantityLevel === quantityFilter;

            row.style.display = (matchesSearch && matchesExpiry && matchesQuantity) ? '' : 'none';
            
            // Hide corresponding detail row
            const stockId = row.getAttribute('data-stock-id');
            const detailRow = document.getElementById(`details-${stockId}`);
            if (detailRow) {
                detailRow.style.display = row.style.display;
            }
        });
    }
});
</script>
@endsection