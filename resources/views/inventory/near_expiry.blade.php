@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        Inventory Alerts
                    </h5>
                </div>

                <div class="card-body">
                    {{-- Success/Error Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        {{-- NEAR EXPIRY SECTION --}}
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-clock"></i> Near Expiry Items
                                        <span class="badge bg-light text-dark ms-2">{{ $nearExpiryStocks->count() }}</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($nearExpiryStocks->count() > 0)
                                        {{-- Pull Out All Button --}}
                                        <div class="mb-3">
                                            <div class="alert alert-warning py-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small><strong>Expiring within 6 months</strong></small>
                                                    </div>
                                                    <form id="pullOutAllForm" action="{{ route('inventory.stock-out') }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="pull_all_near_expiry" value="1">
                                                        <input type="hidden" name="months" value="6">
                                                        <button type="submit" class="btn btn-danger btn-sm" id="pullOutAllBtn" onclick="return confirmPullOut()">
                                                            <i class="fas fa-exclamation-triangle"></i> Pull Out All
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive" style="max-height: 400px;">
                                            <table class="table table-sm table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Batch</th>
                                                        <th>Qty</th>
                                                        <th>Expiry</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($nearExpiryStocks as $index => $stock)
                                                        @php
                                                            $expiryDate = \Carbon\Carbon::parse($stock->expiryDate);
                                                            $daysUntilExpiry = now()->diffInDays($expiryDate, false);
                                                            $isCritical = $daysUntilExpiry <= 30;
                                                            $isWarning = $daysUntilExpiry <= 90;
                                                        @endphp
                                                        <tr class="{{ $isCritical ? 'table-danger' : ($isWarning ? 'table-warning' : '') }}">
                                                            <td class="fw-bold">{{ $index + 1 }}</td>
                                                            <td>
                                                                <div>
                                                                    <small class="fw-bold">{{ $stock->product->productName ?? 'N/A' }}</small>
                                                                    <br>
                                                                    <small class="text-muted">{{ $stock->product->genericName ?? '' }}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">{{ $stock->batchNo ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-info">{{ $stock->available_quantity }}</span>
                                                            </td>
                                                            <td>
                                                                <small class="{{ $isCritical ? 'text-danger fw-bold' : ($isWarning ? 'text-warning' : '') }}">
                                                                    {{ $expiryDate->format('M d, Y') }}
                                                                </small>
                                                                <br>
                                                                <small class="text-muted">{{ $daysUntilExpiry }} days</small>
                                                            </td>
                                                            <td>
                                                                @if($isCritical)
                                                                    <span class="badge bg-danger">Critical</span>
                                                                @elseif($isWarning)
                                                                    <span class="badge bg-warning">Warning</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Monitor</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#pullOutModal{{ $stock->stockID }}">
                                                                    <i class="fas fa-external-link-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <p class="text-success mb-0">No near-expiry items</p>
                                            <small class="text-muted">All items are within safe expiry range</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- LOW STOCK SECTION --}}
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-boxes"></i> Low Stock Items
                                        <span class="badge bg-light text-dark ms-2">{{ $lowStocks->count() }}</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($lowStocks->count() > 0)
                                        <div class="table-responsive" style="max-height: 400px;">
                                            <table class="table table-sm table-hover">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Batch</th>
                                                        <th>Qty</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($lowStocks as $index => $stock)
                                                        @php
                                                            $expiryDate = $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate) : null;
                                                            $daysUntilExpiry = $expiryDate ? now()->diffInDays($expiryDate, false) : null;
                                                            $isVeryLow = $stock->available_quantity <= 5;
                                                            $isExpired = $expiryDate && $daysUntilExpiry < 0;
                                                            $isNearExpiry = $expiryDate && $daysUntilExpiry <= 180;
                                                        @endphp
                                                        <tr class="{{ $isVeryLow ? 'table-danger' : 'table-warning' }}">
                                                            <td class="fw-bold">{{ $index + 1 }}</td>
                                                            <td>
                                                                <div>
                                                                    <small class="fw-bold">{{ $stock->product->productName ?? 'N/A' }}</small>
                                                                    <br>
                                                                    <small class="text-muted">{{ $stock->product->genericName ?? '' }}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">{{ $stock->batchNo ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge {{ $isVeryLow ? 'bg-danger' : 'bg-warning' }}">
                                                                    {{ $stock->available_quantity }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($isVeryLow)
                                                                    <span class="badge bg-danger">Very Low</span>
                                                                @else
                                                                    <span class="badge bg-warning">Low Stock</span>
                                                                @endif
                                                                @if($expiryDate && $isNearExpiry)
                                                                    <br>
                                                                    <small class="text-warning">Near Expiry</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#restockModal{{ $stock->stockID }}">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <p class="text-success mb-0">No low stock items</p>
                                            <small class="text-muted">All items have sufficient stock</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Individual Pull Out Modals for Near Expiry Items --}}
                    @foreach($nearExpiryStocks as $stock)
                    <div class="modal fade" id="pullOutModal{{ $stock->stockID }}" tabindex="-1" aria-labelledby="pullOutModalLabel{{ $stock->stockID }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="pullOutModalLabel{{ $stock->stockID }}">
                                        <i class="fas fa-external-link-alt text-danger me-2"></i>
                                        Pull Out - {{ $stock->product->productName ?? 'N/A' }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('inventory.stock-out', $stock->stockID) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <small>
                                                <i class="fas fa-exclamation-triangle"></i>
                                                This item expires on: <strong>{{ \Carbon\Carbon::parse($stock->expiryDate)->format('M d, Y') }}</strong>
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="quantity{{ $stock->stockID }}" class="form-label">Quantity to Pull Out</label>
                                            <input type="number" class="form-control" id="quantity{{ $stock->stockID }}" name="quantity" min="1" max="{{ $stock->available_quantity }}" value="{{ $stock->available_quantity }}" required>
                                            <small class="form-text text-muted">Available: {{ $stock->available_quantity }} units</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reason{{ $stock->stockID }}" class="form-label">Reason</label>
                                            <select class="form-control" id="reason{{ $stock->stockID }}" name="reason" required>
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
                    <div class="modal fade" id="restockModal{{ $stock->stockID }}" tabindex="-1" aria-labelledby="restockModalLabel{{ $stock->stockID }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="restockModalLabel{{ $stock->stockID }}">
                                        <i class="fas fa-plus-circle text-success me-2"></i>
                                        Restock - {{ $stock->product->productName ?? 'N/A' }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('inventory.restock', $stock->stockID) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <small>
                                                <i class="fas fa-info-circle"></i>
                                                Current stock: <strong>{{ $stock->available_quantity }}</strong> units
                                            </small>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="additional_quantity{{ $stock->stockID }}" class="form-label">Additional Qty</label>
                                                <input type="number" class="form-control" id="additional_quantity{{ $stock->stockID }}" name="additional_quantity" min="1" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="batchNo{{ $stock->stockID }}" class="form-label">Batch No</label>
                                                <input type="text" class="form-control" id="batchNo{{ $stock->stockID }}" name="batchNo" value="{{ $stock->batchNo }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="purchase_price{{ $stock->stockID }}" class="form-label">Purchase Price</label>
                                                <input type="number" step="0.01" class="form-control" id="purchase_price{{ $stock->stockID }}" name="purchase_price" value="{{ $stock->purchase_price }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="selling_price{{ $stock->stockID }}" class="form-label">Selling Price</label>
                                                <input type="number" step="0.01" class="form-control" id="selling_price{{ $stock->stockID }}" name="selling_price" value="{{ $stock->selling_price }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label for="expiryDate{{ $stock->stockID }}" class="form-label">Expiry Date</label>
                                                <input type="date" class="form-control" id="expiryDate{{ $stock->stockID }}" name="expiryDate" value="{{ $stock->expiryDate ? \Carbon\Carbon::parse($stock->expiryDate)->format('Y-m-d') : '' }}" required>
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
            </div>
        </div>
    </div>
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
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Pulling Out...';
            this.disabled = true;
        });
    }
});
</script>
@endsection