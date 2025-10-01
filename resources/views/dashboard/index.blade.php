@extends('layouts.app')

@section('content')
<div class="container">
    <form method="GET" class="mb-3">
        <label for="period" class="me-2">Filter Sales:</label>
        <select name="period" id="period" onchange="this.form.submit()" class="form-select d-inline w-auto">
            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>This Month</option>
            <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>This Year</option>
        </select>
    </form>


    {{-- Dashboard Cards --}}
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Sales ({{ ucfirst($period) }})</h5>
                    <h3 class="fw-bold textt">₱{{ number_format($salesTotal, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <h3 class="fw-bold text">{{ $totalProducts }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Low Stock</h5>
                    <h3 class="fw-bold text">{{ $lowStockCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Expired & Damaged Items</h5>
                        <h3 class="fw-bold text">{{ $totalExpiredDamaged }}</h3>
                    </div>
                </div>
        </div>

    </div>


<div class="row g-4 mt-3">
    {{-- Left column: Top Products --}}
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">🏆 Top Products</h5>
                <small class="text-muted">Based on quantity sold</small>
            </div>
            <div class="card-body p-0">
                <div style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Product</th>
                                <th class="text-end">Total Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProductsData as $i => $product)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $product['name'] }}</td>
                                    <td class="text-end">{{ $product['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


   {{-- Right column: Latest Stock Movements --}}
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">📦 Latest Stock Movements</h5>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestStocks as $stock)
                                <tr>
                                    <td>{{ $stock->product->productName ?? 'Unknown' }}</td>
                                    <td>{{ $stock->type }}</td>
                                    <td class="text-end">{{ $stock->quantity }}</td>
                                    <td>
                                        {{-- Show full timestamp --}}
                                        {{  
                                        $stock->created_at->format('M d, Y h:i A') }}
                                    </td>
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
<div class="card mt-4">
    <div class="card-header">Sales Summary (Last 30 Days)</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transactions</th>
                    <th>Items Sold</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
            @foreach($salesSummary as $summary)
                <tr>
                    <td>{{ $summary->saleDate }}</td>
                    <td>{{ $summary->transactions_count }}</td>
                    <td>{{ $summary->items_sold }}</td>
                    <td>₱{{ number_format($summary->total_sales, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

    {{-- Monthly Sales (with VAT deduction applied) --}}
    <div class="card mt-4">
        <div class="card-header">Monthly Sales (Net after VAT)</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Gross Sales</th>
                        <th>Net Sales (after 1% VAT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlySales as $month)
                        <tr>
                            <td>{{ \Carbon\Carbon::create()->month($month->month)->format('F') }}</td>
                            <td>{{ $month->year }}</td>
                            <td>₱{{ number_format($month->total_sales, 2) }}</td>
                            <td>₱{{ number_format($month->net_sales, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
