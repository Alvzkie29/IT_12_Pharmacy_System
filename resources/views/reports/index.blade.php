@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Reports for {{ $date ?? now()->toDateString() }}</h1>

    {{-- Search + Print Row --}}
    <div class="row mb-4 align-items-center justify-content-between">
        <div class="col-md-6">
            <form action="{{ route('reports.index') }}" method="GET" class="d-flex">
                <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" class="form-control me-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control me-2" placeholder="Search by product, batch, type...">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
            </form>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('reports.print', ['date' => $date ?? now()->toDateString()]) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </a>
        </div>
    </div>

    {{-- Totals cards --}}
    @php
        $vatRate = 0.12;
        $totalSalesWithVAT = $totalSales + ($totalSales * $vatRate);
        $totalVAT = $totalSales * $vatRate;
    @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h5>Stocked In</h5>
                    <h2>{{ $totalStockIn }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h5>Pulled Out</h5>
                    <h2>{{ $totalPulledOut }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h5>Expired</h5>
                    <h2>{{ $totalExpired }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h5>Total Sales (with VAT)</h5>
                    <h2>₱{{ number_format($totalSalesWithVAT, 2) }}</h2>
                    <small class="mt-1">VAT: ₱{{ number_format($totalVAT, 2) }}</small>
                </div>
            </div>
        </div>
    </div>



    {{-- Sales Table --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">Sales</div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Batch No</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Selling Price</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>VAT (12%)</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesData as $sale)
                        @php
                            $lineTotal = $sale['sellingPrice'] * $sale['quantity'];
                            $taxAmount = $lineTotal * 0.12; 
                        @endphp
                        <tr>
                            <td class="text-start">{{ $sale['productName'] }}</td>
                            <td>{{ $sale['batchNo'] }}</td>
                            <td>{{ $sale['quantity'] }}</td>
                            <td>₱{{ number_format($sale['purchasePrice'], 2) }}</td>
                            <td>₱{{ number_format($sale['sellingPrice'], 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale['saleDate'])->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                            <td>₱{{ number_format($lineTotal + $taxAmount, 2) }}</td>
                            <td>₱{{ number_format($taxAmount, 2) }}</td>
                            <td>₱{{ number_format($sale['profit'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No sales for this day.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    @php
                        $totalVAT = $totalSales * 0.12; 
                    @endphp
                    <tr class="fw-bold">
                        <td colspan="6" class="text-end">Totals:</td>
                        <td>₱{{ number_format($totalSales + $totalVAT, 2) }}</td>
                        <td>₱{{ number_format($totalVAT, 2) }}</td>
                        <td>₱{{ number_format($totalProfit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


    {{-- Stock Tables --}}
    @foreach (['validReports' => 'Stocked In', 'pulledOutReports' => 'Pulled Out', 'expiredReports' => 'Expired'] as $var => $title)
        <div class="card shadow-sm mb-4">
            <div class="card-header
                @if($var == 'validReports') bg-success text-white
                @elseif($var == 'pulledOutReports') bg-warning text-dark
                @else bg-danger  @endif">
                {{ $title }}
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            @if($var == 'validReports')
                                <th>Value</th>
                            @endif
                            @if($var == 'pulledOutReports')
                                <th>Reason</th>
                            @endif
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($$var as $report)
                            <tr>
                                <td>{{ $report->product->productName }}</td>
                                <td>{{ $report->quantity }}</td>
                                @if($var == 'validReports')
                                    <td>₱{{ number_format($report->quantity * $report->selling_price, 2) }}</td>
                                @endif
                                @if($var == 'pulledOutReports')
                                    <td>{{ ucwords(str_replace(['pulled_out_', '_'], ['Pulled Out - ', ' '], $report->reason)) }}</td>
                                @endif
                                <td>{{ $report->created_at->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="@if($var == 'pulledOutReports')4 @elseif($var == 'validReports')4 @else 3 @endif" class="text-center text-muted">
                                    No items for this category today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

</div>
@endsection
