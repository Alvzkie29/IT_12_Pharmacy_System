@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .products-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }
    
    .product-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .product-card .card-body {
        padding: 1.5rem;
        text-align: center;
    }
    
    .product-name {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .product-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 0.5rem;
    }
    
    .product-stock {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .cart-section {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .cart-table {
        margin: 0;
    }
    
    .cart-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        text-align: center;
    }
    
    .cart-table tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
        text-align: center;
    }
    
    .cart-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
    }
    
    .cart-table tfoot td {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 700;
        padding: 1rem;
        border: none;
    }
    
    .checkout-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-top: 1rem;
        border: 1px solid #dee2e6;
    }
    
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    
    .btn-add {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    .search-section {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Sales Management</h1>
                <p class="mb-0 opacity-75">Process sales and manage your pharmacy transactions</p>
            </div>
            <div class="text-end">
                <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($hasPrescription)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Some products in the cart need a prescription. Please verify before completing the sale.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Left: Available Products --}}
        <div class="col-md-8">
            <div class="products-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="me-2 text-primary"></i>Available Products
                    </h4>
                </div>
                
                {{-- Search Section --}}
                <div class="search-section">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="search-bar" value="{{ $search ?? '' }}" class="form-control border-start-0" placeholder="Search products by name, category, or supplier...">
                        <button id="search-btn" class="btn btn-primary" type="button">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                </div>

                <div class="row" id="product-list">
                    @forelse($stocks as $stock)
                        <div class="col-md-4 mb-3 product-card">
                            <div class="card product-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="me-2 text-primary"></i>
                                        <h6 class="product-name mb-0">{{ $stock->product->productName }}</h6>
                                    </div>
                                    <p class="product-price mb-1">₱{{ number_format($stock->selling_price, 2) }}</p>
                                    <p class="product-stock mb-3">
                                        <i class="fas fa-boxes me-1"></i>Stock: {{ $stock->available_quantity }}
                                    </p>
                                    <form method="POST" action="{{ route('sales.store') }}">
                                        @csrf
                                        <button type="submit" name="add_item" value="{{ $stock->stockID }}" class="btn btn-add w-100">
                                            <i class="fas fa-plus me-1"></i>Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-pills fa-3x mb-3"></i>
                                <h5>No products available</h5>
                                <p>Check your inventory to add products for sale.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Cart --}}
        <div class="col-md-4">
            <div class="cart-section">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-shopping-cart me-2 text-white"></i>
                    <h4 class="mb-0 fw-bold text-white">Shopping Cart</h4>
                </div>
                
                <div class="table-responsive">
                    <table class="table cart-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item</th>
                                <th style="width: 20%;">Qty</th>
                                <th style="width: 25%;">Subtotal</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $subtotal = 0;
                                $cartItems = $items ?? $cart ?? [];
                            @endphp
                            @if(!empty($cartItems))
                                @foreach($cartItems as $item)
                                    @php
                                        $stock = \App\Models\Stock::with('product')->find($item['stockID']);
                                        if (!$stock) continue;
                                        $lineTotal = $stock->selling_price * $item['quantity'];
                                        $subtotal += $lineTotal;
                                    @endphp
                                    <tr>
                                        <td class="text-start">
                                            <div class="fw-medium">{{ $stock->product->productName }}</div>
                                            <small class="text-muted">₱{{ number_format($stock->selling_price, 2) }} each</small>
                                        </td>
                                        <td>
                                            <form class="update-cart-form" data-id="{{ $stock->stockID }}">
                                                <input type="number" class="form-control form-control-sm cart-qty-input"
                                                       value="{{ $item['quantity'] }}" min="1" max="{{ $stock->available_quantity }}" 
                                                       style="width: 70px;"
                                                       title="Max available: {{ $stock->available_quantity }}">
                                            </form>
                                        </td>
<td class="fw-bold text-primary item-subtotal">₱{{ number_format($lineTotal, 2) }}</td>                                        <td>
                                            <form method="POST" action="{{ route('sales.store') }}" class="d-inline">
                                                @csrf
                                                <button type="submit" name="remove_item" value="{{ $stock->stockID }}" 
                                                        class="btn btn-danger btn-sm" title="Remove from cart">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                            <p class="mb-0">Cart is empty</p>
                                            <small>Add products to get started</small>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-start">
                                    <strong>Subtotal:</strong>
                                </td>
<td class="fw-bold text-primary" id="cart-subtotal">₱{{ number_format($subtotal ?? 0, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Discount display + confirm form area --}}
            @if(!empty($cartItems))
            {{-- Discount display (updated by JS) --}}
            <div class="mb-2 px-2">
                <div id="discount-row" class="text-success" style="display:none;">
                    Discount (20%): -<span id="discount-amount">₱0.00</span>
                </div>
                <div id="grand-total-row" class="fw-bold" style="display:none;">
                    Grand Total: <span id="grand-total">₱0.00</span>
                </div>
            </div>

            <div class="checkout-section">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-credit-card me-2 text-primary"></i>Checkout
                </h5>
                
                <form method="POST" action="{{ route('sales.confirm') }}">
                    @csrf
                    
                    {{-- Senior / PWD checkbox (visual) --}}
                    <div class="form-check mb-3 p-3 bg-light rounded">
                        <input class="form-check-input" type="checkbox" id="discount-checkbox">
                        <label class="form-check-label fw-semibold" for="discount-checkbox">
                            <i class="fas fa-percentage me-1 text-success"></i>
                            Senior Citizen or PWD (20% Discount)
                        </label>
                    </div>

                    {{-- Hidden field to send discount flag to controller --}}
                    <input type="hidden" name="isDiscounted" id="isDiscountedInput" value="0">

                    <div class="mb-3">
                        <label for="cash" class="form-label fw-semibold">
                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>Cash Received
                        </label>
                        <input type="number" step="0.01" class="form-control form-control-lg" name="cash" id="cash" 
                               placeholder="Enter amount received" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check me-2"></i>Proceed to Confirmation
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ✅ Live Update Script --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    // helper: parse money strings like "₱1,234.50" or "1,234.50" into number
    function parseMoney(str) {
        if (!str) return 0;
        // remove currency symbol and commas
        return parseFloat(String(str).replace(/[^0-9.-]+/g, '')) || 0;
    }

    // ---- CART UPDATE ----
    document.querySelectorAll(".cart-qty-input").forEach(input => {
        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                updateCart(this);
            }
        });

        input.addEventListener("change", function () {
            updateCart(this);
        });

        input.addEventListener("input", function () {
            updateCart(this);
        });
    });

    function updateCart(input) {
        let form = input.closest(".update-cart-form");
        let stockID = form.dataset.id;
        let qty = input.value;

        if (qty < 1) {
            input.value = 1;
            qty = 1;
        }

        fetch("{{ route('sales.updateCart') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                stockID: stockID,
                quantity: qty
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let row = form.closest("tr");
                row.querySelector(".item-subtotal").innerText = "₱" + data.itemSubtotal;
                document.getElementById("cart-subtotal").innerText = "₱" + data.total;

                // Update the input value if it was clamped to max quantity
                if (data.quantity !== qty) {
                    input.value = data.quantity;
                    // Show a brief notification that quantity was adjusted
                    showNotification(`Quantity adjusted to ${data.quantity} (max available: ${data.maxQuantity})`, 'warning');
                }

                // Recompute discount/grand total if checkbox is checked
                updateDiscountUI();
            } else {
                showNotification(data.message || 'Failed to update cart', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Failed to update cart', 'error');
        });
    }

    // ---- LIVE SEARCH ----
    const searchInput = document.getElementById("search-bar");
    const searchBtn = document.getElementById("search-btn");
    const productList = document.getElementById("product-list");

    function fetchProducts(query) {
        fetch("{{ route('sales.index') }}?search=" + encodeURIComponent(query), {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(res => res.text())
        .then(html => {
            let parser = new DOMParser();
            let doc = parser.parseFromString(html, "text/html");
            let newProducts = doc.querySelector("#product-list").innerHTML;
            productList.innerHTML = newProducts;
        })
        .catch(err => console.error(err));
    }

    searchInput.addEventListener("input", function () {
        fetchProducts(this.value);
    });

    searchBtn.addEventListener("click", function () {
        fetchProducts(searchInput.value);
    });

    // ---- DISCOUNT UI / logic ----
    const discountCheckbox = document.getElementById("discount-checkbox");
    const isDiscountedInput = document.getElementById("isDiscountedInput");
    const discountRow = document.getElementById("discount-row");
    const discountAmountEl = document.getElementById("discount-amount");
    const grandTotalRow = document.getElementById("grand-total-row");
    const grandTotalEl = document.getElementById("grand-total");
    const cartSubtotalEl = document.getElementById("cart-subtotal");

    // call once on load to set UI
    updateDiscountUI();

    if (discountCheckbox) {
        discountCheckbox.addEventListener("change", function () {
            // set hidden input value so controller receives it on submit
            if (isDiscountedInput) {
                isDiscountedInput.value = this.checked ? 1 : 0;
            }
            updateDiscountUI();
        });
    }

    // ---- NOTIFICATION FUNCTION ----
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    function updateDiscountUI() {
        if (!cartSubtotalEl) return;

        // get numeric subtotal
        let subtotal = parseMoney(cartSubtotalEl.innerText);

        if (discountCheckbox && discountCheckbox.checked) {
            let discount = subtotal * 0.20;
            let grand = subtotal - discount;

            // format with thousands separators
            let fmtDiscount = discount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            let fmtGrand = grand.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            discountAmountEl.innerText = "₱" + fmtDiscount;
            grandTotalEl.innerText = "₱" + fmtGrand;

            discountRow.style.display = "block";
            grandTotalRow.style.display = "block";
        } else {
            // hide discount and grand total rows when not applied
            discountRow.style.display = "none";
            grandTotalRow.style.display = "none";
            if (isDiscountedInput) isDiscountedInput.value = 0;
        }
    }
});
</script>

@endsection
