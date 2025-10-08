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
        border: 1px solid #dee2e6;
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
    
    .reports-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .reports-table {
        margin: 0;
    }
    
    .reports-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        text-align: center;
    }
    
    .reports-table tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
        text-align: center;
    }
    
    .reports-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }
    
    .reports-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .reports-table tfoot td {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 700;
        padding: 1rem;
        border: none;
    }
    
    .badge-discounted {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .form-control:focus {
        border-color: #6f42c1;
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
    }
    
    .pagination {
        justify-content: center;
    }
    
    .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: #6f42c1;
    }
    
    .page-link:hover {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }
    
    .page-item.active .page-link {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Reports & Analytics</h1>
                <p class="mb-0 opacity-75">Comprehensive reports for {{ $date ?? now()->toDateString() }}</p>
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
                                <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom Date</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="custom-date-container" style="display: {{ request('period') === 'custom' ? 'block' : 'none' }};">
                            <label for="custom-date" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>Custom Date
                            </label>
                            <input type="date" name="date" id="custom-date" value="{{ request('date') ?? now()->toDateString() }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-semibold">
                                <i class="fas fa-search me-2 text-primary"></i>Search
                            </label>
                            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by product, batch, type...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('reports.print', ['date' => $date ?? now()->toDateString(), 'period' => $period ?? 'today']) }}" target="_blank" class="btn btn-success btn-lg">
                    <i class="fas fa-print me-2"></i>Print Report
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stats-value">{{ $totalStockIn }}</div>
                    <div class="stats-label">Stocked In</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
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
        <div class="col-md-3 mb-3">
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
        <div class="col-md-3 mb-3">
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



    {{-- Sales Table --}}
    <div class="reports-card">
        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%); color: white;">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-shopping-cart me-2"></i>Sales Report
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table reports-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Product</th>
                            <th style="width: 100px;">Batch No</th>
                            <th style="width: 80px;">Qty</th>
                            <th style="width: 100px;">Purchase</th>
                            <th style="width: 100px;">Selling</th>
                            <th style="width: 120px;">Date</th>
                            <th style="width: 100px;">Original</th>
                            <th style="width: 100px;">Discounted</th>
                            <th style="width: 80px;">Discount</th>
                            <th style="width: 100px;">Original Profit</th>
                            <th style="width: 100px;">Actual Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesData as $sale)
                            @php
                                $lineTotal = $sale['total'];
                                $discountedTotal = $sale['discountedTotal'];
                                $itemDiscount = $lineTotal - $discountedTotal;
                                $originalProfit = ($sale['sellingPrice'] - $sale['purchasePrice']) * $sale['quantity'];
                                $actualProfit = $sale['profit'];
                            @endphp
                            <tr class="{{ $sale['isDiscounted'] ? 'table-warning' : '' }}">
                                <td class="text-start">
                                    <div class="fw-medium">{{ $sale['productName'] }}</div>
                                    @if($sale['isDiscounted'])
                                        <span class="badge-discounted">Discounted</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $sale['batchNo'] }}</td>
                                <td class="fw-bold">{{ $sale['quantity'] }}</td>
                                <td class="text-muted">₱{{ number_format($sale['purchasePrice'], 2) }}</td>
                                <td class="fw-medium">₱{{ number_format($sale['sellingPrice'], 2) }}</td>
                                <td class="text-muted small">{{ \Carbon\Carbon::parse($sale['saleDate'])->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                                <td class="text-muted">₱{{ number_format($lineTotal, 2) }}</td>
                                <td class="{{ $sale['isDiscounted'] ? 'text-success fw-bold' : '' }}">
                                    ₱{{ number_format($discountedTotal, 2) }}
                                </td>
                                <td class="{{ $sale['isDiscounted'] ? 'text-danger' : 'text-muted' }}">
                                    @if($sale['isDiscounted'])
                                        -₱{{ number_format($itemDiscount, 2) }}
                                    @else
                                        ₱0.00
                                    @endif
                                </td>
                                <td class="text-muted">₱{{ number_format($originalProfit, 2) }}</td>
                                <td class="{{ $sale['isDiscounted'] ? 'text-warning fw-bold' : 'text-success' }}">
                                    ₱{{ number_format($actualProfit, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                        <h5>No sales for this period</h5>
                                        <p>Sales data will appear here when transactions are made.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Totals:</td>
                            <td class="fw-bold">₱{{ number_format($totalSales, 2) }}</td>
                            <td class="text-success fw-bold">₱{{ number_format($totalDiscountedSales, 2) }}</td>
                            <td class="text-danger fw-bold">-₱{{ number_format($totalDiscounts, 2) }}</td>
                            <td class="text-muted fw-bold">₱{{ number_format($salesData->sum(function($s) { return ($s['sellingPrice'] - $s['purchasePrice']) * $s['quantity']; }), 2) }}</td>
                            <td class="text-warning fw-bold">₱{{ number_format($totalProfit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    {{-- Stock Tables --}}
    @php
        $stockTables = [
            'validReports' => ['title' => 'Stocked In', 'paginated' => $validReportsPaginated, 'id' => 'stocked-in-table', 'icon' => 'fas fa-arrow-up', 'color' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)'],
            'pulledOutReports' => ['title' => 'Pulled Out', 'paginated' => $pulledOutReportsPaginated, 'id' => 'pulled-out-table', 'icon' => 'fas fa-arrow-down', 'color' => 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)'],
            'expiredReports' => ['title' => 'Expired', 'paginated' => $expiredReportsPaginated, 'id' => 'expired-table', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'linear-gradient(135deg, #dc3545 0%, #fd7e14 100%)']
        ];
    @endphp
    
    @foreach ($stockTables as $var => $config)
        <div class="reports-card" id="{{ $config['id'] }}">
            <div class="card-header" style="background: {{ $config['color'] }}; color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="{{ $config['icon'] }} me-2"></i>{{ $config['title'] }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table reports-table">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Product</th>
                                <th style="width: 100px;">Quantity</th>
                                @if($var == 'validReports')
                                    <th style="width: 120px;">Value</th>
                                @endif
                                @if($var == 'pulledOutReports')
                                    <th style="width: 150px;">Reason</th>
                                @endif
                                <th style="width: 150px;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($config['paginated'] as $report)
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-medium">{{ $report->product->productName }}</div>
                                        <small class="text-muted">{{ $report->product->genericName }}</small>
                                    </td>
                                    <td class="fw-bold text-center">{{ $report->quantity }}</td>
                                    @if($var == 'validReports')
                                        <td class="text-center fw-bold text-success">₱{{ number_format($report->quantity * $report->selling_price, 2) }}</td>
                                    @endif
                                    @if($var == 'pulledOutReports')
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">
                                                {{ ucwords(str_replace(['pulled_out_', '_'], ['Pulled Out - ', ' '], $report->reason)) }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="text-center text-muted small">{{ $report->created_at->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="@if($var == 'pulledOutReports')4 @elseif($var == 'validReports')4 @else 3 @endif" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="{{ $config['icon'] }} fa-3x mb-3"></i>
                                            <h5>No {{ strtolower($config['title']) }} items</h5>
                                            <p>Data will appear here when {{ strtolower($config['title']) }} transactions occur.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Pagination for this table --}}
            <div class="d-flex justify-content-center p-3">
                {{ $config['paginated']->appends(request()->query())->fragment($config['id'])->links() }}
            </div>
        </div>
    @endforeach

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom date toggle functionality
    function toggleCustomDate() {
        let period = document.getElementById('period').value;
        let customDate = document.getElementById('custom-date-container');
        customDate.style.display = (period === 'custom') ? 'block' : 'none';
    }

    // Add event listener for period change
    const periodSelect = document.getElementById('period');
    if (periodSelect) {
        periodSelect.addEventListener('change', toggleCustomDate);
    }

    // Check if there's a fragment in the URL and scroll to it
    const hash = window.location.hash;
    if (hash) {
        // Remove the # from the hash
        const targetId = hash.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            // Scroll to the element after a short delay to ensure page is loaded
            setTimeout(() => {
                targetElement.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 200);
        }
    }
    
    // Also handle pagination clicks for immediate feedback
    const paginationLinks = document.querySelectorAll('.pagination a');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Get the table ID from the fragment in the href
            const url = new URL(this.href);
            const fragment = url.hash.substring(1); // Remove the #
            
            if (fragment) {
                // Scroll to the target table immediately
                const targetTable = document.getElementById(fragment);
                if (targetTable) {
                    targetTable.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});
</script>

@endsection
