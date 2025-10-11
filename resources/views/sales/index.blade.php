@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .products-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .product-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
        background: white;
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    
    .product-card .card-body {
        padding: 1.5rem;
    }
    
    .product-name {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .product-price {
        font-size: 1.125rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 0.5rem;
    }
    
    .product-stock {
        color: #6c757d;
        font-size: 0.8rem;
        margin-bottom: 1rem;
    }
    
    .cart-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }
    
    .cart-table {
        margin: 0;
    }
    
    .cart-table thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .cart-table tbody td {
        padding: 0.875rem 1rem;
        border: none;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .cart-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
    }
    
    .cart-table tfoot td {
        background: #f8f9fa;
        font-weight: 700;
        padding: 1rem;
        border: none;
    }
    
    .checkout-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
        border: 1px solid #e9ecef;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .btn-add {
        background: #28a745;
        border: none;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
        font-size: 0.875rem;
    }
    
    .btn-add:hover {
        background: #218838;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    
    .search-section {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }
    
    .discount-badge {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Sales Management</h1>
                <p class="mb-0 opacity-75">Process sales and manage pharmacy transactions</p>
            </div>
            <div class="text-end">
                <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
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
            <i class="fas fa-exclamation-triangle me-2"></i>Some products in the cart require prescription verification.
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
        <div class="col-lg-8">
            <div class="products-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Available Products</h5>
                </div>
                
                {{-- Search Section --}}
                <div class="search-section">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="search-bar" value="{{ $search ?? '' }}" class="form-control border-start-0" placeholder="Search products by name, category, or supplier...">
                        <button id="search-btn" class="btn btn-success" type="button">
                            Search
                        </button>
                    </div>
                </div>

                <div class="row" id="product-list">
                    @forelse($stocks as $stock)
                        <div class="col-xl-4 col-lg-6 mb-3">
                            <div class="card product-card">
                                <div class="card-body">
                                    <div class="product-name">
                                        {{ $stock->product->productName }}
                                        @if($stock->product->genericName)
                                            <div class="text-muted small">{{ $stock->product->genericName }}</div>
                                        @endif
                                    </div>
                                    <div class="product-stock">
                                        <i class="fas fa-boxes me-1"></i>Stock: {{ $stock->available_quantity }}
                                    </div>
                                    <div class="product-stock">
                                        <i class="fas fa-calendar-alt me-1"></i>Expires: {{ date('M d, Y', strtotime($stock->expiryDate)) }}
                                    </div>
                                    <div class="product-price">₱{{ number_format($stock->selling_price, 2) }}</div>
                                    <form method="POST" action="{{ route('sales.store') }}">
                                        @csrf
                                        <button type="submit" name="add_item" value="{{ $stock->stockID }}" class="btn btn-add w-100">
                                            Add to Cart
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
                                <p class="mb-0">Check your inventory to add products for sale.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Cart --}}
        <div class="col-lg-4">
            <div class="cart-section">
                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Shopping Cart</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table cart-table">
                        <thead>
                            <tr>
                                <th class="text-start">Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center" style="width: 60px;">Action</th>
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
                                            <div class="fw-medium small">{{ $stock->product->productName }}</div>
                                            <div class="text-muted x-small">₱{{ number_format($stock->selling_price, 2) }} each</div>
                                        </td>
                                        <td class="text-center">
                                            <form class="update-cart-form" data-id="{{ $stock->stockID }}">
                                                <input type="number" class="form-control form-control-sm cart-qty-input"
                                                       value="{{ $item['quantity'] }}" min="1" max="{{ $stock->available_quantity }}" 
                                                       style="width: 70px;" onkeydown="return event.key !== '0' || this.value.length > 0"
                                                       oninput="if(this.value == '0') this.value = '1';"
                                                       title="Max available: {{ $stock->available_quantity }}">
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold text-primary item-subtotal">₱{{ number_format($lineTotal, 2) }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('sales.store') }}" class="d-inline">
                                                @csrf
                                                <button type="submit" name="remove_item" value="{{ $stock->stockID }}" 
                                                        class="btn btn-outline-danger btn-sm" title="Remove from cart">
                                                    Remove
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
                                <td id="cart-subtotal" class="text-end fw-bold text-primary">₱{{ number_format($subtotal ?? 0, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Discount display + confirm form area --}}
            @if(!empty($cartItems))
            <div class="checkout-section">
                <h6 class="fw-bold mb-3">Checkout</h6>
                
                {{-- Discount display (updated by JS) --}}
                <div class="mb-3">
                    <div id="discount-row" class="d-flex justify-content-between text-success" style="display:none !important;">
                        <span>Discount (20%):</span>
                        <span>-<span id="discount-amount">₱0.00</span></span>
                    </div>
                    <div id="grand-total-row" class="d-flex justify-content-between fw-bold border-top pt-2" style="display:none !important;">
                        <span>Grand Total:</span>
                        <span id="grand-total">₱0.00</span>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('sales.confirm') }}">
                    @csrf
                    
                    {{-- Senior / PWD checkbox --}}
                    <div class="form-check mb-3 p-3 bg-light rounded">
                        <input class="form-check-input" type="checkbox" id="discount-checkbox" 
                               {{ old('isDiscounted') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="discount-checkbox">
                            Senior Citizen or PWD (20% Discount)
                        </label>
                    </div>

                    {{-- Hidden field to send discount flag to controller --}}
                    <input type="hidden" name="isDiscounted" id="isDiscountedInput" value="{{ old('isDiscounted', '0') }}">

                    <div class="mb-3">
                        <label for="cash" class="form-label fw-semibold">Cash Received</label>
                        <input type="number" step="0.01" class="form-control" name="cash" id="cash" 
                               placeholder="Enter amount received" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        Proceed to Confirmation
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

        // Only use change event for better performance
        input.addEventListener("change", function () {
            updateCart(this);
        });
    });

    function updateCart(input) {
        let form = input.closest(".update-cart-form");
        let stockID = form.dataset.id;
        let qty = parseInt(input.value) || 1;

        // Prevent zero or negative quantities
        if (qty < 1) {
            input.value = 1;
            qty = 1;
        }

        // Calculate subtotal immediately for better UX
        let row = form.closest("tr");
        let subtotalElement = row.querySelector(".item-subtotal");
        let priceText = row.querySelector(".text-muted").innerText;
        let price = parseMoney(priceText);
        
        // Update subtotal immediately (client-side calculation)
        if (subtotalElement) {
            let newSubtotal = price * qty;
            subtotalElement.innerText = "₱" + newSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Update cart total (client-side calculation)
        let total = 0;
        document.querySelectorAll(".update-cart-form").forEach(form => {
            let formRow = form.closest("tr");
            let formQty = parseInt(formRow.querySelector(".cart-qty-input").value) || 1;
            let formPriceText = formRow.querySelector(".text-muted").innerText;
            let formPrice = parseMoney(formPriceText);
            total += formPrice * formQty;
        });
        
        let cartSubtotal = document.getElementById("cart-subtotal");
        if (cartSubtotal) {
            cartSubtotal.innerText = "₱" + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Also update server-side (in background)
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
                // Update the input value if it was clamped to max quantity
                if (data.quantity !== qty) {
                    input.value = data.quantity;
                    
                    // Update subtotal with server value (in case of quantity adjustment)
                    if (subtotalElement) {
                        subtotalElement.innerText = "₱" + data.itemSubtotal;
                    }
                    
                    // Update cart total with server value
                    if (cartSubtotal) {
                        cartSubtotal.innerText = "₱" + data.total;
                    }
                    
                    // Show a brief notification that quantity was adjusted
                    showNotification(`Quantity adjusted to ${data.quantity} (max available: ${data.maxQuantity})`, 'warning');
                }

                // Recompute discount/grand total if checkbox is checked
                if (typeof updateDiscountUI === 'function') {
                    updateDiscountUI();
                }
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

    // Load saved checkbox state on page load
    if (discountCheckbox) {
        // Check if there's a saved state in localStorage
        const savedState = localStorage.getItem('discountCheckboxState');
        if (savedState === 'true') {
            discountCheckbox.checked = true;
            if (isDiscountedInput) {
                isDiscountedInput.value = 1;
            }
        }
    }

    // call once on load to set UI
    updateDiscountUI();

    if (discountCheckbox) {
        discountCheckbox.addEventListener("change", function () {
            // Save state to localStorage
            localStorage.setItem('discountCheckboxState', this.checked);
            
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

            discountRow.style.display = "flex";
            grandTotalRow.style.display = "flex";
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