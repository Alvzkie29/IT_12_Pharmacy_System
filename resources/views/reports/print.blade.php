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
    </style>
</head>
<body>
<div class="container">
    <div class="report-header text-center">
        <h2>Daily Report</h2>
        <p>Date: {{ $date }}</p>
    </div>

    {{-- Stock Summary --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="border p-2 text-center bg-success text-white">
                <h6>Stocked In</h6>
                <h4>{{ $validReports->sum('quantity') }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border p-2 text-center bg-warning text-dark">
                <h6>Pulled Out</h6>
                <h4>{{ $pulledOutReports->sum('quantity') }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border p-2 text-center bg-danger text-white">
                <h6>Expired</h6>
                <h4>{{ $expiredReports->sum('quantity') }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border p-2 text-center bg-primary text-white">
                <h6>Total Profit</h6>
                <h4>₱{{ number_format($totalProfit, 2) }}</h4>
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
                <th>Total</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salesData as $sale)
                <tr>
                    <td>{{ $sale['productName'] }}</td>
                    <td>{{ $sale['batchNo'] }}</td>
                    <td>{{ $sale['quantity'] }}</td>
                    <td>₱{{ number_format($sale['purchasePrice'], 2) }}</td>
                    <td>₱{{ number_format($sale['sellingPrice'], 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale['saleDate'])->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td> {{-- Added --}}
                    <td>₱{{ number_format($sale['total'], 2) }}</td>
                    <td>₱{{ number_format($sale['profit'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No sales for this day.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="6" class="text-end">Totals:</td>
                <td>₱{{ number_format($totalSales, 2) }}</td>
                <td>₱{{ number_format($totalProfit, 2) }}</td>
                <td></td>
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
                        <th>Profit</th>
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
                            <td>₱{{ number_format($report->profit, 2) }}</td>
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
