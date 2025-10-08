@php
    $vatRate = 0.12;
    $totalSalesWithVAT = $totalSales + ($totalSales * $vatRate);
    $totalVAT = $totalSales * $vatRate;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports for {{ $date }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .report-header { margin-bottom: 20px; }
        .table th, .table td { vertical-align: middle; }
        .card-summary { padding: 10px; margin-bottom: 15px; color: white; text-align: center; }
        .h-100 { height: 100%; }
    </style>
</head>
<body>
<div class="container">
    <div class="report-header text-center">
        <h2>{{ $reportTitle ?? 'Daily Report' }}</h2>
        @if($period == 'specific_date' || $period == 'today')
            <p>Date: {{ $date }}</p>
        @elseif($period == 'monthly')
            <p>Month: {{ \Carbon\Carbon::parse($date)->format('F Y') }}</p>
        @elseif($period == 'yearly')
            <p>Year: {{ \Carbon\Carbon::parse($date)->format('Y') }}</p>
        @endif
    </div>

    {{-- Totals Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h6>Stocked In</h6>
                    <h4>{{ $validReports->sum('quantity') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center text-dark">
                    <h6>Pulled Out</h6>
                    <h4>{{ $pulledOutReports->sum('quantity') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h6>Expired</h6>
                    <h4>{{ $expiredReports->sum('quantity') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h6>Total Sales</h6>
                    <h4>₱{{ number_format($totalDiscountedSales, 2) }}</h4>
                    <small>Discounts: -₱{{ number_format($totalDiscounts, 2) }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <h4 class="mt-5">Sales</h4>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Batch No</th>
                <th>Quantity</th>
                <th>Purchase Price</th>
                <th>Selling Price</th>
                <th>Date</th> 
                <th>Original Total</th>
                <th>Discounted Total</th>
                <th>Discount</th>
                <th>Original Profit</th>
                <th>Actual Profit</th>
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
                <tr>
                    <td>
                        {{ $sale['productName'] }}
                        @if($sale['isDiscounted'])
                            <span class="badge bg-warning text-dark">Discounted</span>
                        @endif
                    </td>
                    <td>{{ $sale['batchNo'] }}</td>
                    <td>{{ $sale['quantity'] }}</td>
                    <td>₱{{ number_format($sale['purchasePrice'], 2) }}</td>
                    <td>₱{{ number_format($sale['sellingPrice'], 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale['saleDate'])->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td> 
                    <td>₱{{ number_format($lineTotal, 2) }}</td>
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
                    <td colspan="11" class="text-center text-muted">No sales for this day.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="6" class="text-end">Totals:</td>
                <td>₱{{ number_format($totalSales, 2) }}</td>
                <td class="text-success">₱{{ number_format($totalDiscountedSales, 2) }}</td>
                <td class="text-danger">-₱{{ number_format($totalDiscounts, 2) }}</td>
                <td class="text-muted">₱{{ number_format($salesData->sum(function($s) { return ($s['sellingPrice'] - $s['purchasePrice']) * $s['quantity']; }), 2) }}</td>
                <td class="text-warning">₱{{ number_format($totalProfit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Stock Tables --}}
    @foreach (['validReports' => 'Stocked In', 'pulledOutReports' => 'Pulled Out', 'expiredReports' => 'Expired'] as $var => $title)
        <h5 class="mt-4">{{ $title }}</h5>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    @if($var == 'validReports')
                        <th>Purchase Price</th>
                        <th>Selling Price</th>
                        <th>Total Value</th>
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
                            <td>₱{{ number_format($report->purchase_price, 2) }}</td>
                            <td>₱{{ number_format($report->selling_price, 2) }}</td>
                            <td>₱{{ number_format($report->quantity * $report->selling_price, 2) }}</td>
                        @endif
                        @if($var == 'pulledOutReports')
                            <td>{{ ucwords(str_replace(['pulled_out_', '_'], ['Pulled Out - ', ' '], $report->reason)) }}</td>
                        @endif
                        <td>{{ $report->created_at->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="@if($var == 'pulledOutReports')4 @elseif($var == 'validReports')7 @else 3 @endif" class="text-center text-muted">
                            No items for this category.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</div>
</body>
</html>
