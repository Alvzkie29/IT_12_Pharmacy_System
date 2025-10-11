@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .search-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }
    
    .transactions-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .transactions-table {
        margin: 0;
    }
    
    .transactions-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        text-align: center;
        font-size: 0.875rem;
    }
    
    .transactions-table tbody td {
        padding: 0.75rem;
        border: none;
        vertical-align: middle;
        text-align: center;
        font-size: 0.875rem;
    }
    
    .transactions-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }
    
    .transactions-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .profit-positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .profit-negative {
        color: #dc3545;
        font-weight: 600;
    }
    
    .category-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .totals-row {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        font-weight: 600;
    }
    
    .totals-row td {
        border: none !important;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Transaction Details</h1>
                <p class="mb-0 opacity-75">View and analyze all transaction records</p>
            </div>
            <div class="text-end">
                <i class="fas fa-receipt fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Search and Filter Section --}}
    <div class="search-section">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="form-control border-start-0" 
                        placeholder="Search by product name, generic name, batch no, or transaction ID...">
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input 
                            type="date" 
                            id="fromDate" 
                            class="form-control" 
                            placeholder="From Date">
                    </div>
                    <div class="col-md-5">
                        <input 
                            type="date" 
                            id="toDate" 
                            class="form-control" 
                            placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
<div class="transactions-card">
    <div class="table-responsive">
        <table class="table transactions-table">
            <thead>
                <tr>
                    <th style="width: 100px;">ID</th>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 200px;">Product</th>
                    <th style="width: 100px;">Batch</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 120px;">Price</th>
                    <th style="width: 140px;">Transaction</th>
                    <th style="width: 120px;">Payment</th>
                    <th style="width: 100px;">Profit</th>
                </tr>
            </thead>
            <tbody id="transactionsTableBody">
                @forelse($transactions as $transaction)
                    <tr class="transaction-row">
                        <td>
                            <small class="text-muted fw-bold">{{ $transaction->transactionID }}</small>
                        </td>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('m/d/Y') }}</small>
                        </td>
                        <td class="text-start">
                            <div class="fw-medium small">{{ $transaction->product_name }}</div>
                            <div class="text-muted x-small">{{ $transaction->generic_name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $transaction->batch_number }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $transaction->qty }}</span>
                        </td>
                        <td>
                            <div class="small">Buy: ₱{{ number_format($transaction->purchase_price, 2) }}</div>
                            <div class="small">Sell: ₱{{ number_format($transaction->selling_price, 2) }}</div>
                        </td>
                        <td>
                            <div class="small">Orig: ₱{{ number_format($transaction->original_total, 2) }}</div>
                            <div class="small text-success">Disc: -₱{{ number_format($transaction->discount, 2) }}</div>
                            <div class="small fw-bold text-primary">Net: ₱{{ number_format($transaction->discounted_total, 2) }}</div>
                        </td>
                        <td>
                            <div class="small text-success">Cash: ₱{{ number_format($transaction->cash_received, 2) }}</div>
                            <div class="small text-info">Change: ₱{{ number_format($transaction->change_given, 2) }}</div>
                        </td>
                        <td class="{{ $transaction->profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                            ₱{{ number_format($transaction->profit, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-receipt fa-3x mb-3"></i>
                                <h5>No transactions found</h5>
                                <p>No transaction records available for the selected criteria.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="4" class="text-end fw-bold">TOTALS:</td>
                    <td class="fw-bold">{{ $totals['qty'] }}</td>
                    <td></td>
                    <td>
                        <div class="small">₱{{ number_format($totals['original_total'], 2) }}</div>
                        <div class="small">-₱{{ number_format($totals['discount'], 2) }}</div>
                        <div class="small fw-bold">₱{{ number_format($totals['discounted_total'], 2) }}</div>
                    </td>
                    <td>
                        <div class="small">₱{{ number_format($totals['cash_received'] ?? 0, 2) }}</div>
                        <div class="small">₱{{ number_format($totals['change_given'] ?? 0, 2) }}</div>
                    </td>
                    <td class="fw-bold {{ $totals['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        ₱{{ number_format($totals['profit'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $transactions->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const clearFilters = document.getElementById('clearFilters');
    const transactionsTableBody = document.getElementById('transactionsTableBody');
    const transactionRows = document.querySelectorAll('.transaction-row');

    // Function to filter transactions
    function filterTransactions() {
        const searchTerm = searchInput.value.toLowerCase();
        const fromDateValue = fromDate.value;
        const toDateValue = toDate.value;

        let visibleCount = 0;

        transactionRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const dateCell = row.cells[1].textContent.trim();
            const rowDate = convertToDate(dateCell);
            
            let matchesSearch = true;
            let matchesDate = true;

            // Search filter
            if (searchTerm) {
                matchesSearch = text.includes(searchTerm);
            }

            // Date filter
            if (fromDateValue) {
                const fromDateObj = new Date(fromDateValue);
                matchesDate = matchesDate && rowDate >= fromDateObj;
            }

            if (toDateValue) {
                const toDateObj = new Date(toDateValue);
                toDateObj.setHours(23, 59, 59, 999); // End of day
                matchesDate = matchesDate && rowDate <= toDateObj;
            }

            // Show/hide row based on filters
            if (matchesSearch && matchesDate) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show message if no results
        const emptyRow = transactionsTableBody.querySelector('td[colspan]');
        if (emptyRow && emptyRow.closest('tr')) {
            const emptyMessageRow = emptyRow.closest('tr');
            if (visibleCount === 0 && (searchTerm || fromDateValue || toDateValue)) {
                emptyMessageRow.style.display = '';
                emptyMessageRow.innerHTML = `
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h5>No transactions found</h5>
                            <p>No transactions match your search criteria.</p>
                        </div>
                    </td>
                `;
            } else if (visibleCount === 0) {
                emptyMessageRow.style.display = '';
            } else {
                emptyMessageRow.style.display = 'none';
            }
        }

        // Update totals based on visible rows
        updateTotals();

        // Show/hide clear filters button
        clearFilters.style.display = (searchTerm || fromDateValue || toDateValue) ? 'block' : 'none';
    }

    // Function to convert displayed date to Date object
    function convertToDate(dateString) {
        // Convert from MM/DD/YYYY to Date object
        const [month, day, year] = dateString.split('/');
        return new Date(year, month - 1, day);
    }

    // Function to update totals based on visible rows
    function updateTotals() {
        const visibleRows = Array.from(transactionRows).filter(row => row.style.display !== 'none');
        
        let totalQty = 0;
        let totalOriginal = 0;
        let totalDiscounted = 0;
        let totalDiscount = 0;
        let totalProfit = 0;

        visibleRows.forEach(row => {
            const qty = parseInt(row.cells[4].querySelector('.badge').textContent);
            const originalTotal = parseFloat(row.cells[6].querySelector('div').textContent.replace('₱', '').replace(',', ''));
            const discount = parseFloat(row.cells[6].querySelectorAll('div')[1].textContent.replace('-₱', '').replace(',', ''));
            const discountedTotal = parseFloat(row.cells[7].textContent.replace('₱', '').replace(',', ''));
            const profit = parseFloat(row.cells[8].textContent.replace('₱', '').replace(',', ''));

            totalQty += qty;
            totalOriginal += originalTotal;
            totalDiscounted += discountedTotal;
            totalDiscount += discount;
            totalProfit += profit;
        });

        // Update footer totals
        const footer = document.querySelector('.totals-row');
        if (footer) {
            footer.cells[4].textContent = totalQty;
            footer.cells[6].innerHTML = `
                <div class="small">₱${totalOriginal.toFixed(2)}</div>
                <div class="small">-₱${totalDiscount.toFixed(2)}</div>
            `;
            footer.cells[7].textContent = `₱${totalDiscounted.toFixed(2)}`;
            footer.cells[8].textContent = `₱${totalProfit.toFixed(2)}`;
            footer.cells[8].className = totalProfit >= 0 ? 'profit-positive fw-bold' : 'profit-negative fw-bold';
        }
    }

    // Event listeners for instant filtering
    searchInput.addEventListener('input', filterTransactions);
    fromDate.addEventListener('change', filterTransactions);
    toDate.addEventListener('change', filterTransactions);

    // Clear filters
    clearFilters.addEventListener('click', function() {
        searchInput.value = '';
        fromDate.value = '';
        toDate.value = '';
        filterTransactions();
    });

    // Initialize filters from URL parameters (if any)
    function initializeFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search');
        const from = urlParams.get('from_date');
        const to = urlParams.get('to_date');

        if (search) searchInput.value = search;
        if (from) fromDate.value = from;
        if (to) toDate.value = to;

        if (search || from || to) {
            filterTransactions();
        }
    }

    initializeFiltersFromURL();
});
</script>
@endsection