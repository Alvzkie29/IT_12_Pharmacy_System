@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .filter-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: none;
    }
    
    .stats-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }
    
    .stats-card .card-body {
        padding: 2rem;
        text-align: center;
    }
    
    .stats-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.8;
    }
    
    .stats-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-label {
        font-size: 1.1rem;
        font-weight: 600;
        opacity: 0.9;
    }
    
    .chart-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .chart-card .card-header {
        background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }
    
    .chart-card .card-body {
        padding: 2rem;
    }
    
    .summary-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .summary-card .card-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }
    
    .movement-item {
        border-left: 4px solid #007bff;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 0 10px 10px 0;
        transition: all 0.3s ease;
    }
    
    .movement-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
        font-size: 0.9rem;
    }
    
    .rank-badge.top-3 {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    }
    
    .rank-badge.other {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">📊 Reports & Analytics</h1>
                <p class="mb-0 opacity-75">Comprehensive insights for {{ $date ?? now()->toDateString() }}</p>
            </div>
            <div class="text-end">
                <i class="fas fa-chart-line fa-3x opacity-50"></i>
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
                            <label for="period" class="form-label fw-semibold">
                                <i class="fas fa-calendar me-2 text-primary"></i>Period
                            </label>
                            <select name="period" id="period" class="form-select">
                                <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="monthly" {{ request('period') === 'monthly' ? 'selected' : '' }}>This Month</option>
                                <option value="yearly" {{ request('period') === 'yearly' ? 'selected' : '' }}>This Year</option>
                                <option value="custom_range" {{ request('period') === 'custom_range' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="from-date-container" style="display: {{ request('period') === 'custom_range' ? 'block' : 'none' }};">
                            <label for="from_date" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>From
                            </label>
                            <input type="date" name="from_date" id="from_date" value="{{ request('from_date') ?? now()->toDateString() }}" class="form-control">
                        </div>
                        <div class="col-md-3" id="to-date-container" style="display: {{ request('period') === 'custom_range' ? 'block' : 'none' }};">
                            <label for="to_date" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>To
                            </label>
                            <input type="date" name="to_date" id="to_date" value="{{ request('to_date') ?? now()->toDateString() }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('reports.print', ['date' => $date ?? now()->toDateString(), 'period' => $period ?? 'today']) }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-print me-2"></i>Print Report
                </a>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stats-value">{{ $totalStockIn }}</div>
                    <div class="stats-label">Stock In</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white;">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stats-value">{{ $totalPulledOut }}</div>
                    <div class="stats-label">Pulled Out</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white;">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-value">{{ $totalExpired }}</div>
                    <div class="stats-label">Expired</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white;">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stats-value">₱{{ number_format($totalProfit, 2) }}</div>
                    <div class="stats-label">Total Profit</div>
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
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-line me-2"></i>Sales Performance
                    </h5>
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
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-trophy me-2"></i>Top Products
                    </h5>
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
                                    <span class="rank-badge {{ $index < 3 ? 'top-3' : 'other' }} me-3">
                                        {{ (int)$index + 1 }}
                                    </span>
                                    <div>
                                        <div class="fw-medium">{{ $product['name'] }}</div>
                                        <small class="text-muted">₱{{ number_format($product['revenue'], 2) }} revenue</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary">{{ $product['total'] }}</div>
                                    <small class="text-muted">sold</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards Row --}}
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-money-bill-wave me-2"></i>Revenue Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 text-success fw-bold">₱{{ number_format($totalSales, 2) }}</div>
                                <small class="text-muted">Gross Sales</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-primary fw-bold">₱{{ number_format($totalDiscountedSales, 2) }}</div>
                            <small class="text-muted">Net Sales</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="h5 text-warning fw-bold">₱{{ number_format($totalDiscounts, 2) }}</div>
                        <small class="text-muted">Total Discounts</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-percentage me-2"></i>Discount Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="discountChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-boxes me-2"></i>Stock Movement
                    </h5>
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
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-history me-2"></i>Recent Product Movements
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $recentMovements = collect();
                    
                    // Add stock movements (IN, expired, pulled out)
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
                    
                    // Add sales transactions
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
                    
                    // Sort by date and take latest 10
                    $recentMovements = $recentMovements->sortByDesc(function($movement) {
                        return $movement['date'];
                    })->take(10);
                @endphp
                
                @if($recentMovements->count() > 0)
                    @foreach($recentMovements as $movement)
                        <div class="col-md-6 mb-3">
                            <div class="movement-item {{ $movement['class'] }} p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-truncate">{{ $movement['productName'] }}</div>
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

    // Add event listener for period change
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
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
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
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
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