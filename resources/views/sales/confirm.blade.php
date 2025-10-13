@extends('layouts.app')

@section('content')
<style>
    .receipt-container {
        max-width: 400px;
        margin: 0 auto;
        background: white;
        border: 1px solid #ddd;
        padding: 20px;
        font-family: 'Courier New', monospace;
    }
    .receipt-header {
        text-align: center;
        border-bottom: 2px dashed #333;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .receipt-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    .receipt-totals {
        border-top: 2px dashed #333;
        padding-top: 10px;
        margin-top: 15px;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
        }
        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="container">
    <h1 class="mb-4 no-print">Confirm Sale</h1>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Receipt Preview --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <span>Order Summary</span>
            <button onclick="printReceipt()" class="btn btn-light btn-sm no-print">
                <i class="fas fa-print me-1"></i>Print Receipt
            </button>
        </div>
        <div class="card-body p-0">
            {{-- Printable Receipt --}}
            <div class="receipt-container" id="receipt">
                <div class="receipt-header">
                    <h3 class="mb-1">LM3 PHARMACY</h3>
                    <p class="mb-1">Urban, Matina Pangi</p>
                    <p class="mb-1">Davao City</p>
                    <p class="mb-1">Tel: (123) 456-7890</p>
                    <p class="mb-0">{{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
                </div>

                <div class="receipt-items">
                    <div class="receipt-item fw-bold border-bottom pb-1 mb-2">
                        <span>ITEM</span>
                        <span>QTY x PRICE</span>
                        <span>AMOUNT</span>
                    </div>
                    
                    @php 
                        $subtotal = 0;
                        $itemsData = [];
                    @endphp
                    
                    @foreach($items as $item)
                        @php
                            $stock = \App\Models\Stock::with('product')->find($item['stockID']);
                            if (!$stock) continue;
                            $lineTotal = $stock->selling_price * $item['quantity'];
                            $subtotal += $lineTotal;
                            $itemsData[] = [
                                'name' => $stock->product->productName,
                                'qty' => $item['quantity'],
                                'price' => $stock->selling_price,
                                'total' => $lineTotal
                            ];
                        @endphp
                        <div class="receipt-item">
                            <span>{{ $stock->product->productName }}</span>
                            <span>{{ $item['quantity'] }} x ₱{{ number_format($stock->selling_price, 2) }}</span>
                            <span>₱{{ number_format($lineTotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="receipt-totals">
                    @php
                        $discount = 0;
                        if($isDiscounted){
                            $discount = $subtotal * 0.20;
                        }
                        $grandTotal = $subtotal - $discount;
                        $change = $cash - $grandTotal;
                    @endphp

                    <div class="receipt-item">
                        <span>Subtotal:</span>
                        <span></span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($isDiscounted)
                    <div class="receipt-item text-success">
                        <span>Discount (20%):</span>
                        <span></span>
                        <span>-₱{{ number_format($discount, 2) }}</span>
                    </div>
                    @endif

                    <div class="receipt-item fw-bold border-top pt-2">
                        <span>Grand Total:</span>
                        <span></span>
                        <span>₱{{ number_format($grandTotal, 2) }}</span>
                    </div>

                    <div class="receipt-item">
                        <span>Cash Received:</span>
                        <span></span>
                        <span>₱{{ number_format($cash, 2) }}</span>
                    </div>

                    <div class="receipt-item fw-bold">
                        <span>Change:</span>
                        <span></span>
                        <span>₱{{ number_format($change, 2) }}</span>
                    </div>

                    @if($isDiscounted)
                    <div class="receipt-item text-center text-success mt-2">
                        <small>SENIOR CITIZEN/PWD DISCOUNT APPLIED</small>
                    </div>
                    @endif

                    <div class="text-center mt-3">
                        <p class="mb-1">Thank you for your purchase!</p>
                        <small>Please keep this receipt for your records</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex gap-2 no-print">
        <form method="POST" action="{{ route('sales.finalize') }}" class="flex-fill">
            @csrf
            <input type="hidden" name="cash" value="{{ $cash }}">
            <input type="hidden" name="isDiscounted" value="{{ $isDiscounted }}">
            <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-check me-2"></i>Confirm & Complete Sale
            </button>
        </form>
        
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<script>
function printReceipt() {
    // Get the receipt element
    const receipt = document.getElementById('receipt');
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Write the receipt content to the new window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    margin: 0;
                    padding: 20px;
                    font-size: 12px;
                }
                .receipt-container {
                    max-width: 300px;
                    margin: 0 auto;
                }
                .receipt-header {
                    text-align: center;
                    border-bottom: 2px dashed #333;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                }
                .receipt-item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 4px;
                }
                .receipt-totals {
                    border-top: 2px dashed #333;
                    padding-top: 10px;
                    margin-top: 15px;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 10px;
                    }
                }
            </style>
        </head>
        <body>
            ${receipt.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.afterprint = function() { printWindow.close(); }
    };
}

// Auto-print receipt when page loads (optional)
document.addEventListener('DOMContentLoaded', function() {
    printReceipt();
});
</script>
@endsection