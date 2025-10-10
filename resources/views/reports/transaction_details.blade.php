@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Transaction Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Transaction Date</th>
                            <th>Product Name</th>
                            <th>Generic Name</th>
                            <th>Batch No</th>
                            <th>Qty</th>
                            <th>Purchase Price</th>
                            <th>Selling Price</th>
                            <th>Original Total</th>
                            <th>Discounted Total</th>
                            <th>Discount</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transactionID }}</td>
                                <td>{{ $transaction->transaction_date }}</td>
                                <td>{{ $transaction->product_name }}</td>
                                <td>{{ $transaction->generic_name }}</td>
                                <td>{{ $transaction->batch_number }}</td>
                                <td>{{ $transaction->qty }}</td>
                                <td>₱{{ number_format($transaction->purchase_price, 2) }}</td>
                                <td>₱{{ number_format($transaction->selling_price, 2) }}</td>
                                <td>₱{{ number_format($transaction->original_total, 2) }}</td>
                                <td>₱{{ number_format($transaction->discounted_total, 2) }}</td>
                                <td>₱{{ number_format($transaction->discount, 2) }}</td>
                                <td>₱{{ number_format($transaction->profit, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No transaction records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-success fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Totals:</td>
                            <td>{{ $totals['qty'] }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>₱{{ number_format($totals['original_total'], 2) }}</td>
                            <td>₱{{ number_format($totals['discounted_total'], 2) }}</td>
                            <td>₱{{ number_format($totals['discount'], 2) }}</td>
                            <td>₱{{ number_format($totals['profit'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection