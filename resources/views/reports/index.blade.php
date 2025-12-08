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
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .stats-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
        background: white;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    
    .stats-card .card-body {
        padding: 1.5rem;
    }
    
    .stats-icon {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        opacity: 0.8;
    }
    
    .stats-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-align: right;
    }
    
    .stats-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
        text-align: left;
    }
    
    .chart-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        overflow: hidden;
        background: white;
    }
    
    .chart-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }
    
    .chart-card .card-body {
        padding: 1.5rem;
    }
    
    .summary-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        overflow: hidden;
        background: white;
    }
    
    .summary-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }
    
    .movement-item {
        border-left: 4px solid #28a745;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: #f8f9fa;
        border-radius: 0 8px 8px 0;
        transition: all 0.3s ease;
    }
    
    .movement-item:hover {
        background: #e9ecef;
        transform: translateX(4px);
    }
    
    .movement-item.out {
        border-left-color: #dc3545;
    }
    
    .movement-item.in {
        border-left-color: #28a745;
    }
    
    .movement-item.expired {
        border-left-color: #ffc107;
    }
    
    .movement-item.sold {
        border-left-color: #007bff;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .top-products-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .product-item:hover {
        background-color: #f8f9fa;
    }
    
    .product-item:last-child {
        border-bottom: none;
    }
    
    .rank-badge {
        background: #28a745;
        color: white;
        font-weight: 600;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        min-width: 35px;
        text-align: center;
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
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Reports & Analytics</h1>
                <p class="mb-0 opacity-75">Comprehensive business insights and performance metrics</p>
            </div>
            <div class="text-end">
                <i class="fas fa-chart-line fa-2x opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <form action="{{ route('reports.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="period" class="form-label fw-semibold">Period</label>
                            <select name="period" id="period" class="form-select">
                                <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="monthly" {{ request('period') === 'monthly' ? 'selected' : '' }}>This Month</option>
                                <option value="yearly" {{ request('period') === 'yearly' ? 'selected' : '' }}>This Year</option>
                                <option value="custom_range" {{ request('period') === 'custom_range' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="from-date-container" style="display: {{ request('period') === 'custom_range' ? 'block' : 'none' }};">
                            <label for="from_date" class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" id="from_date" value="{{ request('from_date') ?? now()->toDateString() }}" class="form-control">
                        </div>
                        <div class="col-md-3" id="to-date-container" style="display: {{ request('period') === 'custom_range' ? 'block' : 'none' }};">
                            <label for="to_date" class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" id="to_date" value="{{ request('to_date') ?? now()->toDateString() }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100">
                                Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('reports.print', ['date' => $date ?? now()->toDateString(), 'period' => $period ?? 'today']) }}" class="btn btn-outline-success">
                    Print Report
                </a>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-100">
                            <div class="stats-label text-start">Stock In</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stats-value metric-positive">{{ $totalStockIn }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-start">Total incoming stock</small>
                            </div>
                        </div>
                        <div class="stats-icon text-success ms-3">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-100">
                            <div class="stats-label text-start">Pulled Out</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stats-value metric-neutral">{{ $totalPulledOut }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-start">Items removed</small>
                            </div>
                        </div>
                        <div class="stats-icon text-warning ms-3">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-100">
                            <div class="stats-label text-start">Expired Items</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stats-value metric-negative">{{ $totalExpired }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-start">Stock losses</small>
                            </div>
                        </div>
                        <div class="stats-icon text-danger ms-3">
                            <i class="fas fa-hourglass-end"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-100">
                            <div class="stats-label text-start">Total Profit</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stats-value metric-positive">₱{{ number_format($totalProfit, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-start">Net profit</small>
                            </div>
                        </div>
                        <div class="stats-icon text-success ms-3">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Summary --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="chart-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Revenue Summary</h5>
                <span class="badge bg-success">Period: {{ ucfirst($period ?? 'today') }}</span>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="border-end">
                            <div class="h4 metric-positive fw-bold">₱{{ number_format($totalSales, 2) }}</div>
                            <small class="text-muted">Gross Sales</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border-end">
                            <div class="h4 metric-negative fw-bold">₱{{ number_format($totalDiscounts, 2) }}</div>
                            <small class="text-muted">Total Discounts</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div>
                            <div class="h4 metric-positive fw-bold">₱{{ number_format($totalDiscountedSales, 2) }}</div>
                            <small class="text-muted">Net Sales</small>
                        </div>
                    </div>
                </div>
                @if($period === 'monthly' && isset($netSales))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-success">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Monthly Net Revenue:</strong>
                                <span class="h5 mb-0 metric-positive">₱{{ number_format($netSales, 2) }}</span>
                            </div>
                            <small class="text-muted">Gross sales after all deductions</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

    {{-- Charts and Analytics Row --}}
    <div class="row mb-4">
        {{-- Sales Performance Chart --}}
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">Sales Performance</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Top Products --}}
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">Top Products</h5>
                </div>
                <div class="card-body">
                    <div class="top-products-list">
                        @php
                            $topProducts = $salesData->groupBy('productName')->map(function ($items) {
                                return [
                                    'name' => $items->first()['productName'],
                                    'total' => (int) $items->sum('quantity'),
                                    'revenue' => (float) $items->sum('discountedTotal')
                                ];
                            })->sortByDesc('total')->take(5);
                        @endphp
                        
                        @foreach($topProducts as $index => $product)
                            <div class="product-item">
                                <div class="d-flex align-items-center">
                                    <span class="rank-badge me-3">{{ (int)$index + 1 }}</span>
                                    <div>
                                        <div class="fw-medium">{{ $product['name'] }}</div>
                                        <small class="text-muted">₱{{ number_format($product['revenue'], 2) }} revenue</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold metric-positive">{{ $product['total'] }}</div>
                                    <small class="text-muted">sold</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Analytics --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">Discount Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="discountChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">Stock Movement</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Product Movements --}}
    <div class="chart-card">
        <div class="card-header">
            <h5 class="mb-0 fw-bold">Recent Product Movements</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $recentMovements = collect();
                    
                    if(isset($validReportsPaginated)) {
                        $recentMovements = $recentMovements->merge($validReportsPaginated->map(function($item) {
                            return [
                                'type' => 'in', 
                                'item' => $item, 
                                'class' => 'in',
                                'date' => $item->created_at,
                                'productName' => $item->product->productName ?? 'Unknown Product',
                                'genericName' => $item->product->genericName ?? '',
                                'batchNo' => $item->batchNo ?? 'N/A',
                                'quantity' => $item->quantity,
                                'description' => 'Stock In'
                            ];
                        }));
                    }
                    if(isset($expiredReportsPaginated)) {
                        $recentMovements = $recentMovements->merge($expiredReportsPaginated->map(function($item) {
                            return [
                                'type' => 'expired', 
                                'item' => $item, 
                                'class' => 'expired',
                                'date' => $item->created_at,
                                'productName' => $item->product->productName ?? 'Unknown Product',
                                'genericName' => $item->product->genericName ?? '',
                                'batchNo' => $item->batchNo ?? 'N/A',
                                'quantity' => $item->quantity,
                                'description' => 'Expired'
                            ];
                        }));
                    }
                    if(isset($pulledOutReportsPaginated)) {
                        $recentMovements = $recentMovements->merge($pulledOutReportsPaginated->map(function($item) {
                            return [
                                'type' => 'out', 
                                'item' => $item, 
                                'class' => 'out',
                                'date' => $item->created_at,
                                'productName' => $item->product->productName ?? 'Unknown Product',
                                'genericName' => $item->product->genericName ?? '',
                                'batchNo' => $item->batchNo ?? 'N/A',
                                'quantity' => $item->quantity,
                                'description' => 'Pulled Out'
                            ];
                        }));
                    }
                    
                    if(isset($salesData)) {
                        $recentMovements = $recentMovements->merge($salesData->map(function($sale) {
                            return [
                                'type' => 'sold',
                                'item' => $sale,
                                'class' => 'sold',
                                'date' => $sale['saleDate'],
                                'productName' => $sale['productName'],
                                'genericName' => $sale['genericName'] ?? '', 
                                'batchNo' => $sale['batchNo'],
                                'quantity' => $sale['quantity'],
                                'description' => 'Sold',
                                'amount' => $sale['discountedTotal'] ?? $sale['total']
                            ];
                        }));
                    }
                    
                    $recentMovements = $recentMovements->sortByDesc(function($movement) {
                        return $movement['date'];
                    })->take(10);
                @endphp
                
                @if($recentMovements->count() > 0)
                    @foreach($recentMovements as $movement)
                        <div class="col-md-6 mb-3">
                            <div class="movement-item {{ $movement['class'] }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $movement['productName'] }}</div>
                                        @if($movement['genericName'])
                                            <div class="text-muted small">{{ $movement['genericName'] }}</div>
                                        @endif
                                        <div class="text-muted small">
                                            Batch: {{ $movement['batchNo'] }} | 
                                            Qty: {{ $movement['quantity'] }}
                                            @if($movement['type'] === 'sold')
                                                | Amount: ₱{{ number_format($movement['amount'], 2) }}
                                            @endif
                                        </div>
                                        <div class="text-muted small mt-1">
                                            {{ $movement['description'] }}
                                        </div>
                                    </div>
                                    <div class="text-end ms-2">
                                        <span class="badge bg-{{ 
                                            $movement['class'] === 'in' ? 'success' : 
                                            ($movement['class'] === 'sold' ? 'primary' : 
                                            ($movement['class'] === 'out' ? 'warning' : 'danger')) 
                                        }}">
                                            {{ strtoupper($movement['type']) }}
                                        </span>
                                        <div class="text-muted small mt-1">
                                            @if($movement['date'] instanceof \Carbon\Carbon)
                                                {{ $movement['date']->format('M d, h:i A') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($movement['date'])->format('M d, h:i A') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent product movements</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom date toggle functionality
    function toggleCustomDate() {
        let period = document.getElementById('period').value;
        let fromContainer = document.getElementById('from-date-container');
        let toContainer = document.getElementById('to-date-container');
        
        if (period === 'custom_range') {
            fromContainer.style.display = 'block';
            toContainer.style.display = 'block';
        } else {
            fromContainer.style.display = 'none';
            toContainer.style.display = 'none';
        }
    }

    const periodSelect = document.getElementById('period');
    if (periodSelect) {
        periodSelect.addEventListener('change', toggleCustomDate);
    }

    // Sales Performance Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Sales Revenue',
                data: [{{ (float) $totalSales * 0.8 }}, {{ (float) $totalSales * 0.9 }}, {{ (float) $totalSales * 1.1 }}, {{ (float) $totalSales }}],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Discount Analysis Chart
    const discountCtx = document.getElementById('discountChart').getContext('2d');
    new Chart(discountCtx, {
        type: 'doughnut',
        data: {
            labels: ['Regular Sales', 'Discounted Sales'],
            datasets: [{
                data: [{{ (float) $totalSales - (float) $totalDiscounts }}, {{ (float) $totalDiscounts }}],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Stock Movement Chart
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'bar',
        data: {
            labels: ['Stock In', 'Pulled Out', 'Expired'],
            datasets: [{
                label: 'Quantity',
                data: [{{ (int) $totalStockIn }}, {{ (int) $totalPulledOut }}, {{ (int) $totalExpired }}],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

@endsection