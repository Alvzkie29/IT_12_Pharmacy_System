@php
    // Group sales data by sale for Sales Report - with safe date handling
    $groupedSales = [];
    foreach($salesData as $saleItem) {
        // Handle saleDate safely - it might be a string or Carbon instance
        $saleDate = $saleItem['saleDate'];
        if ($saleDate instanceof \Carbon\Carbon) {
            $saleDateKey = $saleDate->format('Y-m-d H:i:s');
        } else {
            $saleDateKey = \Carbon\Carbon::parse($saleDate)->format('Y-m-d H:i:s');
        }
        
        if (!isset($groupedSales[$saleDateKey])) {
            $groupedSales[$saleDateKey] = [
                'saleDate' => $saleItem['saleDate'],
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
        $groupedSales[$saleDateKey]['items'][] = $saleItem;
        $groupedSales[$saleDateKey]['subtotal'] += $saleItem['total'];
        $groupedSales[$saleDateKey]['totalDiscount'] += ($saleItem['total'] - $saleItem['discountedTotal']);
        $groupedSales[$saleDateKey]['finalTotal'] += $saleItem['discountedTotal'];
        $groupedSales[$saleDateKey]['totalProfit'] += $saleItem['profit'];
        if ($saleItem['isDiscounted']) {
            $groupedSales[$saleDateKey]['isDiscounted'] = true;
        }
    }
    
    // Sort grouped sales by date
    ksort($groupedSales);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports for {{ $date }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        /* Button styles */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            padding: 8px 20px;
            font-size: 14px;
            border-radius: 5px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-success:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .alert a {
            color: #155724;
            font-weight: bold;
            text-decoration: underline;
        }
        .alert a:hover {
            color: #0b5e2e;
        }
        .fa-spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @media print {
            .container { max-width: 100% !important; }
            .table { border: 1px solid #000 !important; }
            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
            .section-title { background-color: #f8f9fa !important; }
            .summary-table th { background-color: #f8f9fa !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container">
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

    <!-- Save to S3 Button - This won't show when printing -->
    <div class="text-end mb-3 no-print">
        <button type="button" class="btn btn-success" id="saveToS3Btn" onclick="saveReportToS3()">
            <i class="fas fa-cloud-upload-alt"></i> Save Report to Cloud
        </button>
        <div id="saveStatus" class="mt-2" style="display: none;"></div>
    </div>

    {{-- Compact Summary Table --}}
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
                {{-- Stocked In --}}
                <tr>
                    <td class="fw-bold text-success">Stocked In</td>
                    <td class="text-center">{{ $validReports->count() }}</td>
                    <td class="text-center">{{ number_format($validReports->sum('quantity')) }}</td>
                    <td class="fw-bold">₱{{ number_format($validReports->sum(function($r) { return $r->quantity * $r->selling_price; }), 2) }}</td>
                    <td class="text-muted">Items added to inventory</td>
                </tr>
                
                {{-- Pulled Out --}}
                <tr>
                    <td class="fw-bold text-warning">Pulled Out</td>
                    <td class="text-center">{{ $pulledOutReports->count() }}</td>
                    <td class="text-center">{{ number_format($pulledOutReports->sum('quantity')) }}</td>
                    <td>-</td>
                    <td class="text-muted">Items removed from inventory</td>
                </tr>
                
                {{-- Expired --}}
                <tr>
                    <td class="fw-bold text-danger">Expired</td>
                    <td class="text-center">{{ $expiredReports->count() }}</td>
                    <td class="text-center">{{ number_format($expiredReports->sum('quantity')) }}</td>
                    <td>-</td>
                    <td class="text-muted">Items expired</td>
                </tr>
                
                {{-- Sales --}}
                <tr>
                    <td class="fw-bold text-primary">Sales</td>
                    <td class="text-center">{{ count($groupedSales) }}</td>
                    <td class="text-center">{{ $salesData->count() }}</td>
                    <td class="fw-bold">₱{{ number_format($totalDiscountedSales, 2) }}</td>
                    <td class="text-muted">
                        Transactions: {{ count($groupedSales) }}, 
                        Items: {{ $salesData->count() }}, 
                        Discount: -₱{{ number_format($totalDiscounts, 2) }}
                    </td>
                </tr>
                
                {{-- Profit --}}
                <tr class="table-warning">
                    <td class="fw-bold">Total Profit</td>
                    <td colspan="2" class="text-center">-</td>
                    <td class="fw-bold">₱{{ number_format($totalProfit, 2) }}</td>
                    <td class="text-muted">Net profit from all sales</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- SALES REPORT (Grouped by Sale) --}}
    <div class="section-title">SALES REPORT - BY TRANSACTION</div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-dark">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 120px;">Date & Time</th>
                    <th style="width: 60px;">Items</th>
                    <th style="width: 80px;">Transaction</th>
                    <th style="width: 80px;">Payment</th>
                    <th style="width: 70px;">Profit</th>
                    <th style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $saleCount = 1; @endphp
                @forelse($groupedSales as $saleTime => $sale)
                    <tr>
                        <td class="fw-bold">{{ $saleCount++ }}</td>
                        <td class="fw-bold">{{ \Carbon\Carbon::parse($sale['saleDate'])->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                        <td class="text-center">{{ count($sale['items']) }}</td>
                        <td>
                            <div class="small">Orig: ₱{{ number_format($sale['subtotal'], 2) }}</div>
                            <div class="small text-success">Disc: -₱{{ number_format($sale['totalDiscount'], 2) }}</div>
                            <div class="small fw-bold text-primary">Net: ₱{{ number_format($sale['finalTotal'], 2) }}</div>
                        </td>
                        <td>
                            <div class="small text-success">Cash: ₱{{ number_format($sale['cashReceived'], 2) }}</div>
                            <div class="small text-info">Change: ₱{{ number_format($sale['changeGiven'], 2) }}</div>
                        </td>
                        <td class="text-warning fw-bold">₱{{ number_format($sale['totalProfit'], 2) }}</td>
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
                        <td colspan="7" class="text-center text-muted py-2">No sales recorded for this period.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($groupedSales) > 0)
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTALS:</td>
                    <td>
                        <div class="small">₱{{ number_format($totalSales, 2) }}</div>
                        <div class="small">-₱{{ number_format($totalDiscounts, 2) }}</div>
                        <div class="small">₱{{ number_format($totalDiscountedSales, 2) }}</div>
                    </td>
                    <td>
                        <div class="small">₱{{ number_format($totalCashReceived, 2) }}</div>
                        <div class="small">₱{{ number_format($totalChangeGiven, 2) }}</div>
                    </td>
                    <td class="text-warning">₱{{ number_format($totalProfit, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- TRANSACTION REPORT (All Line Items) --}}
    <div class="section-title page-break">TRANSACTION REPORT - ALL LINE ITEMS</div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-primary">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 100px;">Date</th>
                    <th style="width: 120px;">Product</th>
                    <th style="width: 80px;">Batch</th>
                    <th style="width: 40px;">Qty</th>
                    <th style="width: 80px;">Price</th>
                    <th style="width: 100px;">Transaction</th>
                    <th style="width: 80px;">Payment</th>
                    <th style="width: 60px;">Profit</th>
                </tr>
            </thead>
            <tbody>
                @php $transactionCount = 1; @endphp
                @forelse($salesData as $sale)
                    @php
                        $lineTotal = $sale['total'];
                        $discountedTotal = $sale['discountedTotal'];
                        $itemDiscount = $lineTotal - $discountedTotal;
                        $actualProfit = $sale['profit'];
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $transactionCount++ }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale['saleDate'])->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                        <td>
                            <div class="fw-bold small">{{ $sale['productName'] }}</div>
                            <div class="text-muted x-small">{{ $sale['genericName'] ?? 'N/A' }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $sale['batchNo'] }}</span></td>
                        <td class="text-center">{{ $sale['quantity'] }}</td>
                        <td>
                            <div class="small">Buy: ₱{{ number_format($sale['purchasePrice'], 2) }}</div>
                            <div class="small">Sell: ₱{{ number_format($sale['sellingPrice'], 2) }}</div>
                        </td>
                        <td>
                            <div class="small">Orig: ₱{{ number_format($lineTotal, 2) }}</div>
                            <div class="small text-success">Disc: -₱{{ number_format($itemDiscount, 2) }}</div>
                            <div class="small fw-bold text-primary">Net: ₱{{ number_format($discountedTotal, 2) }}</div>
                        </td>
                        <td>
                            <div class="small text-success">Cash: ₱{{ number_format($sale['cashReceived'] ?? 0, 2) }}</div>
                            <div class="small text-info">Change: ₱{{ number_format($sale['changeGiven'] ?? 0, 2) }}</div>
                        </td>
                        <td class="{{ $sale['isDiscounted'] ? 'text-warning fw-bold' : 'text-success fw-bold' }}">
                            ₱{{ number_format($actualProfit, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-2">No transactions recorded for this period.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($salesData->count() > 0)
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="6" class="text-end">TOTALS:</td>
                    <td>
                        <div class="small">₱{{ number_format($totalSales, 2) }}</div>
                        <div class="small">-₱{{ number_format($totalDiscounts, 2) }}</div>
                        <div class="small">₱{{ number_format($totalDiscountedSales, 2) }}</div>
                    </td>
                    <td>
                        <div class="small">₱{{ number_format($totalCashReceived, 2) }}</div>
                        <div class="small">₱{{ number_format($totalChangeGiven, 2) }}</div>
                    </td>
                    <td class="text-warning">₱{{ number_format($totalProfit, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Stock Movements Section --}}
    <div class="section-title page-break">STOCK MOVEMENTS</div>
    
    {{-- Stocked In --}}
    <h6 class="text-success mt-3 mb-2">STOCKED IN ITEMS</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-success">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 120px;">Product</th>
                    <th style="width: 80px;">Batch</th>
                    <th style="width: 50px;">Qty</th>
                    <th style="width: 80px;">Price</th>
                    <th style="width: 80px;">Total Value</th>
                    <th style="width: 100px;">Date Added</th>
                </tr>
            </thead>
            <tbody>
                @forelse($validReports as $index => $report)
                    <tr>
                        <td class="fw-bold">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold small">{{ $report->product->productName ?? 'N/A' }}</div>
                            <div class="text-muted x-small">{{ $report->product->genericName ?? 'N/A' }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $report->batchNo ?? 'N/A' }}</span></td>
                        <td class="text-center">{{ $report->quantity }}</td>
                        <td>
                            <div class="small">Buy: ₱{{ number_format($report->purchase_price, 2) }}</div>
                            <div class="small">Sell: ₱{{ number_format($report->selling_price, 2) }}</div>
                        </td>
                        <td class="fw-bold">₱{{ number_format($report->quantity * $report->selling_price, 2) }}</td>
                        <td>{{ $report->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-2">No stock added during this period.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($validReports->count() > 0)
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTAL:</td>
                    <td class="text-center">{{ number_format($validReports->sum('quantity')) }}</td>
                    <td></td>
                    <td>₱{{ number_format($validReports->sum(function($r) { return $r->quantity * $r->selling_price; }), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Pulled Out --}}
    <h6 class="text-warning mt-3 mb-2">PULLED OUT ITEMS</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-warning">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 120px;">Product</th>
                    <th style="width: 80px;">Batch</th>
                    <th style="width: 50px;">Qty</th>
                    <th style="width: 80px;">Reason</th>
                    <th style="width: 100px;">Date Pulled Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pulledOutReports as $index => $report)
                    <tr>
                        <td class="fw-bold">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold small">{{ $report->product->productName ?? 'N/A' }}</div>
                            <div class="text-muted x-small">{{ $report->product->genericName ?? 'N/A' }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $report->batchNo ?? 'N/A' }}</span></td>
                        <td class="text-center">{{ $report->quantity }}</td>
                        <td>
                            <span class="badge bg-danger">
                                {{ ucwords(str_replace(['pulled_out_', '_'], ['', ' '], $report->reason)) }}
                            </span>
                        </td>
                        <td>{{ $report->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-2">No items pulled out during this period.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($pulledOutReports->count() > 0)
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTAL:</td>
                    <td class="text-center">{{ number_format($pulledOutReports->sum('quantity')) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Expired --}}
    <h6 class="text-danger mt-3 mb-2">EXPIRED ITEMS</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-sm compact-table">
            <thead class="table-danger">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 120px;">Product</th>
                    <th style="width: 80px;">Batch</th>
                    <th style="width: 50px;">Qty</th>
                    <th style="width: 80px;">Expiry Date</th>
                    <th style="width: 100px;">Date Recorded</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expiredReports as $index => $report)
                    <tr>
                        <td class="fw-bold">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold small">{{ $report->product->productName ?? 'N/A' }}</div>
                            <div class="text-muted x-small">{{ $report->product->genericName ?? 'N/A' }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $report->batchNo ?? 'N/A' }}</span></td>
                        <td class="text-center">{{ $report->quantity }}</td>
                        <td class="text-danger fw-bold">
                            {{ \Carbon\Carbon::parse($report->expiryDate)->format('M d, Y') }}
                        </td>
                        <td>{{ $report->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-2">No expired items during this period.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($expiredReports->count() > 0)
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTAL:</td>
                    <td class="text-center">{{ number_format($expiredReports->sum('quantity')) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Footer --}}
    <div class="mt-5 pt-4 border-top text-center text-muted">
        <small>Report generated by Pharmacy Inventory System • {{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }}</small>
    </div>
</div>

<script>
function saveReportToS3() {
    const btn = document.getElementById('saveToS3Btn');
    const status = document.getElementById('saveStatus');
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving to Cloud...';
    status.style.display = 'none';
    
    // Prepare the data
    const formData = new FormData();
    formData.append('date', '{{ $date }}');
    formData.append('period', '{{ $period }}');
    formData.append('from_date', '{{ request('from_date') }}');
    formData.append('to_date', '{{ request('to_date') }}');
    formData.append('search', '{{ $search ?? '' }}');
    
    // Send AJAX request
    fetch('{{ route('reports.save-to-s3') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        status.style.display = 'block';
        if (data.success) {
            status.className = 'mt-2 alert alert-success';
            status.innerHTML = `
                <i class="fas fa-check-circle"></i> ${data.message}<br>
                <small><i class="fas fa-file"></i> Filename: ${data.filename}</small><br>
                <small><i class="fas fa-link"></i> <a href="${data.url}" target="_blank">View in S3</a></small>
            `;
        } else {
            status.className = 'mt-2 alert alert-danger';
            status.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
        }
    })
    .catch(error => {
        status.style.display = 'block';
        status.className = 'mt-2 alert alert-danger';
        status.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${error.message}`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Save Report to Cloud';
    });
}

// Add keyboard shortcut (Ctrl+S) to save to S3
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveReportToS3();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>