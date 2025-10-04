@extends('layouts.app')

@section('content')
<style>
    .dashboard-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .dashboard-card .card-body {
        padding: 1.5rem;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .filter-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }
    
    .section-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }
    
    .section-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .section-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 1px solid #e9ecef;
        border-radius: 15px 15px 0 0;
        padding: 1.25rem 1.5rem;
    }
    
    .table-modern {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .table-modern thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
    }
    
    .table-modern tbody td {
        padding: 0.875rem 1rem;
        border: none;
        vertical-align: middle;
    }
    
    .table-modern tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge-rank {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #8b6914;
        font-weight: 700;
        padding: 0.5rem 0.75rem;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .badge-type {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-type.IN {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .badge-type.OUT {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back! Here's what's happening with your pharmacy.</p>
        </div>
        <div class="filter-section">
            <form method="GET" class="d-flex align-items-center gap-3">
                <label for="period" class="form-label mb-0 fw-semibold text-dark">Filter Sales:</label>
                <select name="period" id="period" onchange="this.form.submit()" class="form-select w-auto">
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>This Month</option>
                    <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
        </div>
    </div>


    {{-- Dashboard Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-center">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value">₱{{ number_format($salesTotal, 2) }}</div>
                    <div class="stat-label">Total Sales ({{ ucfirst($period) }})</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-center">
                    <div class="stat-icon">📦</div>
                    <div class="stat-value">{{ $totalProducts }}</div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card text-white" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #8b4513;">
                <div class="card-body text-center">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-value">{{ $lowStockCount }}</div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card text-white" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #8b0000;">
                <div class="card-body text-center">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value">{{ $totalExpiredDamaged }}</div>
                    <div class="stat-label">Expired & Damaged</div>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4 mb-4">
        {{-- Left column: Top Products --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">🏆 Top Products</h5>
                        <small class="text-muted">Based on quantity sold</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px;" class="text-center">Rank</th>
                                    <th>Product</th>
                                    <th class="text-end">Total Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProductsData as $i => $product)
                                    <tr>
                                        <td class="text-center">
                                            @if($i < 3)
                                                <span class="badge-rank">{{ $i+1 }}</span>
                                            @else
                                                <span class="badge bg-light text-dark rounded-circle" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">{{ $i+1 }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-medium">{{ $product['name'] }}</td>
                                        <td class="text-end fw-bold text-primary">{{ $product['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column: Latest Stock Movements --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">📦 Latest Stock Movements</h5>
                        <small class="text-muted">Recent inventory changes</small>
                    </div>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-end">Quantity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestStocks as $stock)
                                    <tr>
                                        <td class="fw-medium">{{ $stock->product->productName ?? 'Unknown' }}</td>
                                        <td class="text-center">
                                            <span class="badge-type {{ $stock->type }}">{{ $stock->type }}</span>
                                        </td>
                                        <td class="text-end fw-bold">{{ $stock->quantity }}</td>
                                        <td class="text-muted small">{{ $stock->created_at->format('M d, h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Summary Table --}}
    <div class="section-card mb-4">
        <div class="section-header">
            <h5 class="mb-0 fw-bold text-dark">📊 Sales Summary (Last 30 Days)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-center">Items Sold</th>
                            <th class="text-end">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesSummary as $summary)
                            <tr>
                                <td class="fw-medium">{{ $summary->saleDate }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">{{ $summary->transactions_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info rounded-pill">{{ $summary->items_sold }}</span>
                                </td>
                                <td class="text-end fw-bold text-success">₱{{ number_format($summary->total_sales, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Monthly Sales (with VAT deduction applied) --}}
    <div class="section-card">
        <div class="section-header">
            <h5 class="mb-0 fw-bold text-dark">📈 Monthly Sales (Net after VAT)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th class="text-end">Gross Sales</th>
                            <th class="text-end">Net Sales (after 1% VAT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySales as $month)
                            <tr>
                                <td class="fw-medium">{{ \Carbon\Carbon::create()->month($month->month)->format('F') }}</td>
                                <td class="text-muted">{{ $month->year }}</td>
                                <td class="text-end fw-bold text-warning">₱{{ number_format($month->total_sales, 2) }}</td>
                                <td class="text-end fw-bold text-success">₱{{ number_format($month->net_sales, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
