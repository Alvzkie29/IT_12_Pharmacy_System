@extends('layouts.app')

@section('content')
<style>
    .dashboard-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        background: white;
    }
    
    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    
    .dashboard-card .card-body {
        padding: 1.5rem;
    }
    
    .stat-icon {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        opacity: 0.8;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-align: right;
    }
    
    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
        text-align: left;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .section-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        background: white;
    }
    
    .section-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .section-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 12px 12px 0 0;
        padding: 1.25rem 1.5rem;
    }
    
    .table-modern {
        border-radius: 8px;
        overflow: hidden;
        margin: 0;
    }
    
    .table-modern thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .table-modern tbody td {
        padding: 0.875rem 1rem;
        border: none;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .table-modern tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .alert-item {
        border-left: 4px solid #dc3545;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: #f8f9fa;
        border-radius: 0 8px 8px 0;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .alert-item:hover {
        background: #e9ecef;
        transform: translateX(4px);
    }
    
    .alert-item.warning {
        border-left-color: #ffc107;
    }
    
    .alert-item.info {
        border-left-color: #17a2b8;
    }
    
    .quick-action {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8f9fa;
        cursor: pointer;
    }
    
    .quick-action:hover {
        border-color: #28a745;
        background: white;
        transform: translateY(-2px);
    }
    
    .metric-positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .metric-negative {
        color: #dc3545;
        font-weight: 600;
    }
    
    .metric-neutral {
        color: #6c757d;
        font-weight: 600;
    }
    
    .badge-alert {
        background: #dc3545;
        color: white;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">Dashboard Overview</h1>
            <p class="text-muted mb-0">Welcome back! Here's your business at a glance</p>
        </div>
        <div class="filter-section">
            <form method="GET" class="d-flex align-items-center gap-3">
                <label for="period" class="form-label mb-0 fw-semibold text-dark">Reporting Period:</label>
                <select name="period" id="period" onchange="this.form.submit()" class="form-select w-auto">
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>This Month</option>
                    <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Key Performance Indicators --}}
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-start w-100">
                        <div class="stat-label text-start">Today's Revenue</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="stat-value metric-positive">₱{{ number_format($todaySales, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted text-start">From {{ $todayTransactions }} transactions</small>
                        </div>
                    </div>
                    <div class="stat-icon text-success ms-3">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-start w-100">
                        <div class="stat-label text-start">Active Products</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="stat-value metric-neutral">{{ $totalProducts }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted text-start">In inventory</small>
                        </div>
                    </div>
                    <div class="stat-icon text-primary ms-3">
                        <i class="fas fa-pills"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('inventory.nearExpiry') }}" class="text-decoration-none">
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-100">
                            <div class="stat-label text-start">Low Stock Items</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stat-value metric-negative">{{ $lowStockCount }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-start">Requires restocking</small>
                            </div>
                        </div>
                        <div class="stat-icon text-warning ms-3">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('inventory.nearExpiry') }}" class="text-decoration-none">
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-100">
                            <div class="stat-label text-start">Near Expiry</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stat-value metric-negative">{{ $nearExpiryCount }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-start">Within 6 months</small>
                            </div>
                        </div>
                        <div class="stat-icon text-danger ms-3">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

    <div class="row g-4 mb-4">
        {{-- Critical Alerts --}}
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">Critical Alerts</h5>
                    <small class="text-muted">Items requiring immediate attention</small>
                </div>
                <span class="badge bg-{{ $criticalAlertsCount > 0 ? 'danger' : 'success' }}">
                    {{ $criticalAlertsCount }} Alert{{ $criticalAlertsCount !== 1 ? 's' : '' }}
                </span>
            </div>
            <div class="card-body">
                @foreach($criticalAlerts as $alert)
                    <div class="alert-item {{ $alert['type'] }}" onclick="@if($alert['link']) window.location.href='{{ $alert['link'] }}' @endif">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="fw-bold me-2">{{ $alert['title'] }}</div>
                                    <span class="badge bg-{{ $alert['priority'] == 'high' ? 'danger' : ($alert['priority'] == 'medium' ? 'warning' : 'success') }} fs-xxsmall">
                                        {{ ucfirst($alert['priority']) }}
                                    </span>
                                </div>
                                <div class="text-muted small mb-1">{{ $alert['description'] }}</div>
                                <small class="text-muted">{{ $alert['date'] }}</small>
                            </div>
                            @if($alert['link'])
                            <div class="text-end ms-3">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

        {{-- Quick Actions --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="section-header">
                    <h5 class="mb-1 fw-bold text-dark">Quick Actions</h5>
                    <small class="text-muted">Frequently used tasks</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('sales.index') }}" class="text-decoration-none">
                                <div class="quick-action">
                                    <div class="text-success mb-2">
                                        <i class="fas fa-cash-register fa-2x"></i>
                                    </div>
                                    <div class="fw-bold text-dark">New Sale</div>
                                    <small class="text-muted">Process customer purchase</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('inventory.index') }}" class="text-decoration-none">
                                <div class="quick-action">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-arrow-down fa-2x"></i>
                                    </div>
                                    <div class="fw-bold text-dark">Stock In</div>
                                    <small class="text-muted">Add new inventory</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('reports.index') }}" class="text-decoration-none">
                                <div class="quick-action">
                                    <div class="text-info mb-2">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                    <div class="fw-bold text-dark">View Reports</div>
                                    <small class="text-muted">Analytics & insights</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('products.index') }}" class="text-decoration-none">
                                <div class="quick-action">
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-pills fa-2x"></i>
                                    </div>
                                    <div class="fw-bold text-dark">Manage Products</div>
                                    <small class="text-muted">Product catalog</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Today's Activity --}}
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Today's Activity</h5>
                        <small class="text-muted">Recent transactions and movements</small>
                    </div>
                    <a href="{{ route('transaction-details.index') }}" class="btn btn-outline-secondary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Product</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayActivities as $activity)
                                    <tr>
                                        <td class="text-muted small">{{ $activity['time'] }}</td>
                                        <td>
                                            <span class="badge bg-{{ $activity['type_color'] }}">{{ $activity['type'] }}</span>
                                        </td>
                                        <td class="fw-medium">{{ $activity['product_name'] }}</td>
                                        <td class="text-end fw-bold {{ $activity['amount_class'] }}">
                                            {{ $activity['amount'] }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $activity['status_color'] }}">{{ $activity['status'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No activity today</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Status --}}
        <div class="col-lg-4">
            <div class="section-card h-100">
                <div class="section-header">
                    <h5 class="mb-1 fw-bold text-dark">System Status</h5>
                    <small class="text-muted">Application health check</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Total Products</span>
                            <span class="badge bg-success">{{ $totalProducts }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ min(($totalProducts/500)*100, 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Active Stock</span>
                            <span class="badge bg-info">{{ $totalStocks }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ min(($totalStocks/1000)*100, 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Today's Performance</span>
                            <span class="badge bg-{{ $todaySales > 0 ? 'success' : 'secondary' }}">
                                {{ $todayTransactions }} sales
                            </span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-{{ $todaySales > 0 ? 'success' : 'secondary' }}" style="width: {{ min(($todayTransactions/50)*100, 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">Current Period</span>
                            <span class="text-muted small text-capitalize">{{ $period }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-medium">Period Revenue</span>
                            <span class="text-success fw-bold">₱{{ number_format($salesTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Quarterly Sales (3% Deduction every quarter) --}}
        <div class="section-card">
            <div class="section-header">
                <h5 class="mb-0 fw-bold text-dark">Quarterly Sales</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Quarter</th>
                                <th>Year</th>
                                <th class="text-end">Gross Sales</th>
                                <th class="text-end">Net Sales (after 3% Deduction)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quarterlySales as $quarter)
                                <tr>
                                    <td class="fw-medium">Q{{ $quarter->quarter }}</td>
                                    <td class="text-muted">{{ $quarter->year }}</td>
                                    <td class="text-end fw-bold text-warning">₱{{ number_format($quarter->total_sales, 2) }}</td>
                                    <td class="text-end fw-bold text-success">₱{{ number_format($quarter->net_sales, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection