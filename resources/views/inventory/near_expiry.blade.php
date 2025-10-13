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
    
    .alert-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        background: white;
        margin-bottom: 2rem;
    }
    
    .alert-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 12px 12px 0 0;
        padding: 1.25rem 1.5rem;
    }
    
    .alert-table {
        margin: 0;
    }
    
    .alert-table thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .alert-table tbody td {
        padding: 0.875rem 1rem;
        border: none;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .alert-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }
    
    .alert-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid;
    }
    
    .status-badge.critical {
        background: #f8d7da;
        color: #721c24;
        border-color: #f1b0b7;
    }
    
    .status-badge.warning {
        background: #fff3cd;
        color: #856404;
        border-color: #ffeaa7;
    }
    
    .status-badge.monitor {
        background: #e2e3e5;
        color: #383d41;
        border-color: #d6d8db;
    }
    
    .status-badge.low-stock {
        background: #fff3cd;
        color: #856404;
        border-color: #ffeaa7;
    }
    
    .status-badge.very-low {
        background: #f8d7da;
        color: #721c24;
        border-color: #f1b0b7;
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
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Inventory Alerts</h1>
                <p class="mb-0 opacity-75">Monitor near-expiry and low stock items</p>
            </div>
            <div class="text-end">
                <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
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

    <div class="row">
        {{-- NEAR EXPIRY SECTION --}}
        <div class="col-lg-6 mb-4">
            <div class="alert-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Near Expiry Items</h5>
                        <small class="text-muted">Products expiring within 6 months</small>
                    </div>
                    <span class="badge bg-warning">{{ $nearExpiryStocks->count() }}</span>
                </div>
                <div class="card-body">
                    @if($nearExpiryStocks->count() > 0)
                        {{-- Bulk Action Section --}}
                        <div class="alert alert-warning mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small">
                                    <strong>Expiring within 6 months</strong>
                                </div>
                                <form id="pullOutAllForm" action="{{ route('inventory.stock-out') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="pull_all_near_expiry" value="1">
                                    <input type="hidden" name="months" value="6">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" id="pullOutAllBtn" onclick="return confirmPullOut()">
                                        Pull Out All
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table alert-table">
                                <thead>
                                    <tr>
                                        <th class="text-start">Product</th>
                                        <th class="text-center">Batch</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Expiry</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nearExpiryStocks as $stock)
                                        @php
                                            $expiryDate = \Carbon\Carbon::parse($stock->expiryDate);
                                            $daysUntilExpiry = now()->diffInDays($expiryDate, false);
                                            $isCritical = $daysUntilExpiry <= 30;
                                            $isWarning = $daysUntilExpiry <= 90;
                                        @endphp
                                        <tr>
                                            <td class="text-start">
                                                <div class="fw-medium small">{{ $stock->product->productName ?? 'N/A' }}</div>
                                                <div class="text-muted x-small">{{ $stock->product->genericName ?? '' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border">{{ $stock->batchNo ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $stock->available_quantity }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="small {{ $isCritical ? 'text-danger fw-bold' : ($isWarning ? 'text-warning' : '') }}">
                                                    {{ $expiryDate->format('M d, Y') }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($isCritical)
                                                    <span class="status-badge critical">Critical</span>
                                                @elseif($isWarning)
                                                    <span class="status-badge warning">Warning</span>
                                                @else
                                                    <span class="status-badge monitor">Monitor</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-action" data-bs-toggle="modal" data-bs-target="#pullOutModal{{ $stock->stockID }}" title="Pull Out Item">
                                                    Pull Out
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <h5 class="text-success mb-2">No Near-Expiry Items</h5>
                            <p class="text-muted mb-0">All items are within safe expiry range</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- LOW STOCK SECTION --}}
        <div class="col-lg-6 mb-4">
            <div class="alert-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Low Stock Items</h5>
                        <small class="text-muted">Products below minimum stock level</small>
                    </div>
                    <span class="badge bg-danger">{{ $lowStocks->count() }}</span>
                </div>
                <div class="card-body">
                    @if($lowStocks->count() > 0)
                        <div class="table-responsive">
                            <table class="table alert-table">
                                <thead>
                                    <tr>
                                        <th class="text-start">Product</th>
                                        <th class="text-center">Batch</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStocks as $stock)
                                        @php
                                            $expiryDate = $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate) : null;
                                            $daysUntilExpiry = $expiryDate ? now()->diffInDays($expiryDate, false) : null;
                                            $isVeryLow = $stock->available_quantity <= 5;
                                            $isExpired = $expiryDate && $daysUntilExpiry < 0;
                                            $isNearExpiry = $expiryDate && $daysUntilExpiry <= 180;
                                        @endphp
                                        <tr>
                                            <td class="text-start">
                                                <div class="fw-medium small">{{ $stock->product->productName ?? 'N/A' }}</div>
                                                <div class="text-muted x-small">{{ $stock->product->genericName ?? '' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border">{{ $stock->batchNo ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $isVeryLow ? 'bg-danger' : 'bg-warning' }}">
                                                    {{ $stock->available_quantity }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($isVeryLow)
                                                    <span class="status-badge very-low">Very Low</span>
                                                @else
                                                    <span class="status-badge low-stock">Low Stock</span>
                                                @endif
                                                @if($expiryDate && $isNearExpiry)
                                                    <div class="x-small text-warning mt-1">Near Expiry</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-success btn-action" data-bs-toggle="modal" data-bs-target="#restockModal{{ $stock->stockID }}" title="Restock Item">
                                                    Restock
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <h5 class="text-success mb-2">No Low Stock Items</h5>
                            <p class="text-muted mb-0">All items have sufficient stock</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Individual Pull Out Modals for Near Expiry Items --}}
    @foreach($nearExpiryStocks as $stock)
    <div class="modal fade" id="pullOutModal{{ $stock->stockID }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Pull Out Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('inventory.stock-out', $stock->stockID) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <div class="small">
                                <strong>{{ $stock->product->productName ?? 'N/A' }}</strong>
                                <div class="text-muted">Expires: {{ \Carbon\Carbon::parse($stock->expiryDate)->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quantity to Pull Out</label>
                            <input type="number" class="form-control" name="quantity" min="1" max="{{ $stock->available_quantity }}" value="{{ $stock->available_quantity }}" required>
                            <div class="form-text text-muted">Available: {{ $stock->available_quantity }} units</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reason</label>
                            <select class="form-select" name="reason" required>
                                <option value="pulled_out_near_expiry">Near Expiry</option>
                                <option value="pulled_out_damaged">Damaged</option>
                                <option value="pulled_out_other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Pull Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Restock Modals for Low Stock Items --}}
    @foreach($lowStocks as $stock)
    <div class="modal fade" id="restockModal{{ $stock->stockID }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Restock Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('inventory.restock', $stock->stockID) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <div class="small">
                                <strong>{{ $stock->product->productName ?? 'N/A' }}</strong>
                                <div class="text-muted">Current stock: {{ $stock->available_quantity }} units</div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Additional Quantity</label>
                                <input type="number" class="form-control" name="additional_quantity" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Batch Number</label>
                                <input type="text" class="form-control" name="batchNo" value="{{ $stock->batchNo }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Purchase Price</label>
                                <input type="number" step="0.01" class="form-control" name="purchase_price" value="{{ $stock->purchase_price }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Selling Price</label>
                                <input type="number" step="0.01" class="form-control" name="selling_price" value="{{ $stock->selling_price }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Expiry Date</label>
                                <input type="date" class="form-control" name="expiryDate" value="{{ $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate)->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
function confirmPullOut() {
    const itemCount = {{ $nearExpiryStocks->count() }};
    
    if (itemCount === 0) {
        alert('No near-expiry items found to pull out.');
        return false;
    }

    return confirm(`Are you sure you want to pull out ALL ${itemCount} near-expiry items? This action cannot be undone and will move all items to pulled out reports.`);
}

// Add loading state to the pull out button
document.addEventListener('DOMContentLoaded', function() {
    const pullOutBtn = document.getElementById('pullOutAllBtn');
    if (pullOutBtn) {
        pullOutBtn.addEventListener('click', function() {
            this.innerHTML = 'Pulling Out...';
            this.disabled = true;
        });
    }
});
</script>
@endsection