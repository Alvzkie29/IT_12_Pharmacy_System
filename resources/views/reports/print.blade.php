@php
    // Group sales data by sale
    $groupedSales = [];
    foreach($salesData as $saleItem) {
        $saleDate = \Carbon\Carbon::parse($saleItem['saleDate'])->format('Y-m-d H:i:s');
        if (!isset($groupedSales[$saleDate])) {
            $groupedSales[$saleDate] = [
                'saleDate' => \Carbon\Carbon::parse($saleItem['saleDate']),
                'items' => [],
                'subtotal' => 0,
                'totalDiscount' => 0,
                'finalTotal' => 0,
                'totalProfit' => 0,
                'isDiscounted' => false,
                'cashReceived' => $saleItem['cashReceived'] ?? 0,
                'changeGiven' => $saleItem['changeGiven'] ?? 0
            ];
        }
        $groupedSales[$saleDate]['items'][] = $saleItem;
        $groupedSales[$saleDate]['subtotal'] += $saleItem['total'];
        $groupedSales[$saleDate]['totalDiscount'] += ($saleItem['total'] - $saleItem['discountedTotal']);
        $groupedSales[$saleDate]['finalTotal'] += $saleItem['discountedTotal'];
        $groupedSales[$saleDate]['totalProfit'] += $saleItem['profit'];
        if ($saleItem['isDiscounted']) $groupedSales[$saleDate]['isDiscounted'] = true;
    }
    ksort($groupedSales);

    $totalCashReceived = $salesData->sum(fn($s) => $s['cashReceived'] ?? 0);
    $totalChangeGiven = $salesData->sum(fn($s) => $s['changeGiven'] ?? 0);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle ?? 'Pharmacy Inventory Report' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 11px; }
        .report-header { margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table th, .table td { vertical-align: middle; padding: 4px 6px; }
        .section-title { 
            background-color: #f8f9fa; 
            padding: 6px 10px; 
            margin: 15px 0 8px 0; 
            border-left: 4px solid #007bff; 
            font-weight: bold;
            page-break-after: avoid;
        }
        .table-sm th, .table-sm td { padding: 3px 5px; }
        .badge { font-size: 0.7em; }
        .page-break { page-break-before: always; }
        .summary-table th { background-color: #f8f9fa; }
        .compact-table td { padding: 2px 4px; }
        @media print {
            .container { max-width: 100% !important; }
            .table { border: 1px solid #000 !important; }
            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
            .section-title { background-color: #f8f9fa !important; }
        }
    </style>
</head>
<body>
<div class="container">
    {{-- HEADER --}}
    <div class="report-header text-center">
        <h2 class="mb-1">{{ $reportTitle ?? 'Pharmacy Inventory Report' }}</h2>
        @if($period == 'specific_date' || $period == 'today')
            <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
        @elseif($period == 'monthly')
            <p class="mb-1"><strong>Period:</strong> {{ \Carbon\Carbon::parse($date)->format('F Y') }}</p>
        @elseif($period == 'yearly')
            <p class="mb-1"><strong>Period:</strong> {{ \Carbon\Carbon::parse($date)->format('Y') }}</p>
        @endif
        <p class="text-muted mb-0">Generated on: {{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
    </div>

    {{-- SUMMARY --}}
    <div class="section-title">SUMMARY</div>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm summary-table">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Count</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold text-success">Stocked In</td>
                    <td class="text-center">{{ $validReports->count() }}</td>
                    <td class="text-center">{{ number_format($validReports->sum('quantity')) }}</td>
                    <td class="fw-bold">₱{{ number_format($validReports->sum(fn($r)=>$r->quantity*$r->selling_price),2) }}</td>
                    <td class="text-muted">Items added to inventory</td>
                </tr>
                <tr>
                    <td class="fw-bold text-warning">Pulled Out</td>
                    <td class="text-center">{{ $pulledOutReports->count() }}</td>
                    <td class="text-center">{{ number_format($pulledOutReports->sum('quantity')) }}</td>
                    <td>-</td>
                    <td class="text-muted">Items removed from inventory</td>
                </tr>
                <tr>
                    <td class="fw-bold text-danger">Expired</td>
                    <td class="text-center">{{ $expiredReports->count() }}</td>
                    <td class="text-center">{{ number_format($expiredReports->sum('quantity')) }}</td>
                    <td>-</td>
                    <td class="text-muted">Items expired</td>
                </tr>
                <tr>
                    <td class="fw-bold text-primary">Sales</td>
                    <td class="text-center">{{ count($groupedSales) }}</td>
                    <td class="text-center">{{ $salesData->count() }}</td>
                    <td class="fw-bold">₱{{ number_format($totalDiscountedSales,2) }}</td>
                    <td class="text-muted">
                        Transactions: {{ count($groupedSales) }}, 
                        Items: {{ $salesData->count() }}, 
                        Discount: -₱{{ number_format($totalDiscounts,2) }}
                    </td>
                </tr>
                <tr class="table-warning">
                    <td class="fw-bold">Total Profit</td>
                    <td colspan="2" class="text-center">-</td>
                    <td class="fw-bold">₱{{ number_format($totalProfit,2) }}</td>
                    <td class="text-muted">Net profit from all sales</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- SALES REPORT --}}
    <div class="section-title">SALES REPORT - BY TRANSACTION</div>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Date & Time</th>
                    <th>Items</th>
                    <th>Transaction</th>
                    <th>Payment</th>
                    <th>Profit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $saleCount=1; @endphp
                @forelse($groupedSales as $saleTime=>$sale)
                <tr>
                    <td class="fw-bold">{{ $saleCount++ }}</td>
                    <td class="fw-bold">{{ $sale['saleDate']->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                    <td class="text-center">{{ count($sale['items']) }}</td>
                    <td>
                        <div class="small">Orig: ₱{{ number_format($sale['subtotal'],2) }}</div>
                        <div class="small text-success">Disc: -₱{{ number_format($sale['totalDiscount'],2) }}</div>
                        <div class="small fw-bold text-primary">Net: ₱{{ number_format($sale['finalTotal'],2) }}</div>
                    </td>
                    <td>
                        <div class="small text-success">Cash: ₱{{ number_format($sale['cashReceived'],2) }}</div>
                        <div class="small text-info">Change: ₱{{ number_format($sale['changeGiven'],2) }}</div>
                    </td>
                    <td class="text-warning fw-bold">₱{{ number_format($sale['totalProfit'],2) }}</td>
                    <td>
                        @if($sale['isDiscounted'])
                            <span class="badge bg-warning text-dark">Discounted</span>
                        @else
                            <span class="badge bg-success">Regular</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-2">No sales recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- STOCK MOVEMENTS --}}
    <div class="section-title page-break">STOCK MOVEMENTS</div>

    {{-- STOCKED IN --}}
    <h6 class="text-success mt-3 mb-2">STOCKED IN ITEMS</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-success">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total Value</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                @forelse($validReports as $i=>$report)
                <tr>
                    <td class="fw-bold">{{ $i+1 }}</td>
                    <td>
                        <div class="fw-bold small">{{ $report->product->productName ?? 'N/A' }}</div>
                        <div class="text-muted x-small">{{ $report->product->genericName ?? 'N/A' }}</div>
                    </td>
                    <td><span class="badge bg-secondary">{{ $report->batchNo ?? 'N/A' }}</span></td>
                    <td class="text-center">{{ $report->quantity }}</td>
                    <td>
                        <div class="small">Buy: ₱{{ number_format($report->purchase_price,2) }}</div>
                        <div class="small">Sell: ₱{{ number_format($report->selling_price,2) }}</div>
                    </td>
                    <td class="fw-bold">₱{{ number_format($report->quantity*$report->selling_price,2) }}</td>
                    <td>{{ $report->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-2">No stock added.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PULLED OUT --}}
    <h6 class="text-warning mt-3 mb-2">PULLED OUT ITEMS</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-warning">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Date Pulled Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pulledOutReports as $i=>$report)
                <tr>
                    <td class="fw-bold">{{ $i+1 }}</td>
                    <td>
                        <div class="fw-bold small">{{ $report->product->productName ?? 'N/A' }}</div>
                        <div class="text-muted x-small">{{ $report->product->genericName ?? 'N/A' }}</div>
                    </td>
                    <td><span class="badge bg-secondary">{{ $report->batchNo ?? 'N/A' }}</span></td>
                    <td class="text-center">{{ $report->quantity }}</td>
                    <td><span class="badge bg-danger">{{ ucwords(str_replace(['pulled_out_', '_'], ['', ' '], $report->reason)) }}</span></td>
                    <td>{{ $report->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-2">No items pulled out.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- EXPIRED --}}
    <h6 class="text-danger mt-3 mb-2">EXPIRED ITEMS</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-danger">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>Qty</th>
                    <th>Expiry Date</th>
                    <th>Date Recorded</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expiredReports as $i=>$report)
                <tr>
                    <td class="fw-bold">{{ $i+1 }}</td>
                    <td>
                        <div class="fw-bold small">{{ $report->product->productName ?? 'N/A' }}</div>
                        <div class="text-muted x-small">{{ $report->product->genericName ?? 'N/A' }}</div>
                    </td>
                    <td><span class="badge bg-secondary">{{ $report->batchNo ?? 'N/A' }}</span></td>
                    <td class="text-center">{{ $report->quantity }}</td>
                    <td class="text-danger fw-bold">{{ \Carbon\Carbon::parse($report->expiryDate)->format('M d, Y') }}</td>
                    <td>{{ $report->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-2">No expired items.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="mt-5 pt-4 border-top text-center text-muted">
        <small>Report generated by Pharmacy Inventory System • {{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }}</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-print after page renders
    window.addEventListener('load', function(){
        setTimeout(()=>window.print(),500);
    });
</script>
</body>
</html>