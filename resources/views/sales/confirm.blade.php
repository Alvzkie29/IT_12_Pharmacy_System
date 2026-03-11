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
        <form method="POST" action="{{ route('sales.finalize') }}" class="flex-fill" id="finalizeForm">
            @csrf
            <input type="hidden" name="cash" value="{{ $cash }}">
            <input type="hidden" name="isDiscounted" value="{{ $isDiscounted ? 1 : 0 }}">
            <input type="hidden" name="receipt_html" id="receipt_html">
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
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    const finalizeForm = document.getElementById('finalizeForm');
    const receiptInput = document.getElementById('receipt_html');
    
    if (finalizeForm && receiptInput) {
        console.log('Form and receipt input found');
        
        // Get the receipt element
        const receipt = document.getElementById('receipt');
        
        if (receipt) {
            console.log('Receipt element found');
            
            // Create a clean copy of the receipt for saving
            const receiptClone = receipt.cloneNode(true);
            
            // Remove any buttons or interactive elements from the clone
            const elementsToRemove = receiptClone.querySelectorAll('button, .no-print, .btn, .d-flex, .alert, .card-header, .card-body, .btn-group');
            elementsToRemove.forEach(el => {
                if (el) el.remove();
            });
            
            // Remove any Bootstrap classes that might cause styling issues
            const allElements = receiptClone.querySelectorAll('*');
            allElements.forEach(el => {
                if (el.classList) {
                    el.classList.forEach(className => {
                        if (className.startsWith('btn-') || className.startsWith('alert-')) {
                            el.classList.remove(className);
                        }
                    });
                }
            });
            
            // Add a timestamp
            const timestamp = document.createElement('div');
            timestamp.className = 'text-center text-muted mt-2';
            timestamp.style.fontSize = '10px';
            timestamp.style.marginTop = '10px';
            timestamp.style.paddingTop = '5px';
            timestamp.style.borderTop = '1px dashed #ccc';
            const now = new Date();
            const options = { timeZone: 'Asia/Manila', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            timestamp.innerHTML = '<small>Receipt generated: ' + now.toLocaleString('en-PH', options) + '</small>';
            receiptClone.appendChild(timestamp);
            
            // Set the receipt HTML
            const receiptHTML = receiptClone.outerHTML;
            receiptInput.value = receiptHTML;
            
            console.log('Receipt HTML set, length:', receiptHTML.length);
            console.log('First 200 chars:', receiptHTML.substring(0, 200));
        } else {
            console.error('Receipt element not found!');
        }
        
        // Debug: Log form submission
        finalizeForm.addEventListener('submit', function(e) {
            const receiptValue = document.getElementById('receipt_html').value;
            console.log('Form submitting with receipt HTML length:', receiptValue.length);
            
            if (!receiptValue || receiptValue.length < 100) {
                console.warn('Receipt HTML seems too short!');
                // Optionally show warning to user
                if (!confirm('Receipt might be empty. Continue anyway?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    } else {
        console.error('Form or receipt input not found!');
        if (!finalizeForm) console.error('finalizeForm not found');
        if (!receiptInput) console.error('receipt_html input not found');
    }
});

function printReceipt() {
    console.log('Printing receipt...');
    
    const receipt = document.getElementById('receipt');
    if (!receipt) {
        console.error('Receipt not found for printing');
        return;
    }
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Get the receipt content
    const receiptContent = receipt.innerHTML;
    
    // Write the receipt content to the new window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt - LM3 Pharmacy</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    margin: 0;
                    padding: 20px;
                    font-size: 12px;
                    background: white;
                }
                .receipt-container {
                    max-width: 300px;
                    margin: 0 auto;
                    background: white;
                    padding: 15px;
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
                .text-success { color: #28a745; }
                .fw-bold { font-weight: bold; }
                .text-center { text-align: center; }
                .mt-2 { margin-top: 10px; }
                .mt-3 { margin-top: 15px; }
                .mb-1 { margin-bottom: 5px; }
                .border-top { border-top: 1px solid #333; }
                .pt-2 { padding-top: 10px; }
                @media print {
                    body {
                        margin: 0;
                        padding: 10px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="receipt-container">
                ${receiptContent}
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.onafterprint = function() { 
            printWindow.close(); 
        };
    };
}

// Optional: Auto-print when page loads (uncomment if you want this)
// document.addEventListener('DOMContentLoaded', function() {
//     setTimeout(printReceipt, 500);
// });
</script>

{{-- Add a small debug div to help troubleshoot --}}
@if(config('app.debug'))
<div class="no-print mt-3 p-3 bg-light border rounded small">
    <strong>Debug Info:</strong>
    <ul class="mb-0 mt-1">
        <li>Cash: ₱{{ number_format($cash, 2) }}</li>
        <li>Discounted: {{ $isDiscounted ? 'Yes' : 'No' }}</li>
        <li>Items in cart: {{ count($items) }}</li>
        <li>Subtotal: ₱{{ number_format($subtotal, 2) }}</li>
    </ul>
</div>
@endif

@endsection