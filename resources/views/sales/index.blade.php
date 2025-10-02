@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">SALES PAGE</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($hasPrescription)
        <div class="alert alert-warning">
            ⚠️ Some products in the cart need a prescription. Please verify before completing the sale.
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Left: Available Products --}}
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    Products
                </div>
                <div class="card-body">
                    {{-- ✅ Live Search --}}
                    <div class="input-group mb-3">
                        <input type="text" id="search-bar" value="{{ $search ?? '' }}" class="form-control" placeholder="Search products...">
                        <button id="search-btn" class="btn btn-outline-secondary" type="button">Search</button>
                    </div>

                    <div class="row" id="product-list">
                        @forelse($stocks as $stock)
                            <div class="col-md-4 mb-3 product-card">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="product-name">{{ $stock->product->productName }}</h6>
                                        <p class="mb-1">₱{{ number_format($stock->selling_price, 2) }}</p>
                                        <p class="text-muted">Stock: {{ $stock->quantity }}</p>
                                        <form method="POST" action="{{ route('sales.store') }}">
                                            @csrf
                                            <button type="submit" name="add_item" value="{{ $stock->stockID }}" class="btn btn-sm btn-primary w-100">Add</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No products available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Cart --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    Cart
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotal = 0; @endphp
                            @if(!empty($items))
                                @foreach($items as $item)
                                    @php
                                        $stock = \App\Models\Stock::with('product')->find($item['stockID']);
                                        if (!$stock) continue;
                                        $lineTotal = $stock->selling_price * $item['quantity'];
                                        $subtotal += $lineTotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $stock->product->productName }}</td>
                                        <td>
                                            <form class="update-cart-form" data-id="{{ $stock->stockID }}">
                                                <input type="number" class="form-control form-control-sm cart-qty-input"
                                                       value="{{ $item['quantity'] }}" min="1" style="width: 70px;">
                                            </form>
                                        </td>
                                        <td class="item-subtotal">₱{{ number_format($lineTotal, 2) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('sales.store') }}">
                                                @csrf
                                                <button type="submit" name="remove_item" value="{{ $stock->stockID }}" class="btn btn-sm btn-danger">x</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-muted">Cart is empty.</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2">Subtotal</td>
                                <td id="cart-subtotal">₱{{ number_format($subtotal, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Discount display + confirm form area --}}
            @if(!empty($items))
            {{-- Discount display (updated by JS) --}}
            <div class="mb-2 px-2">
                <div id="discount-row" class="text-success" style="display:none;">
                    Discount (20%): -<span id="discount-amount">₱0.00</span>
                </div>
                <div id="grand-total-row" class="fw-bold" style="display:none;">
                    Grand Total: <span id="grand-total">₱0.00</span>
                </div>
            </div>

            <div class="card mt-3 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.confirm') }}">
                        @csrf

                        {{-- Senior / PWD checkbox (visual) --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="discount-checkbox">
                            <label class="form-check-label" for="discount-checkbox">
                                Is the person a <strong>Senior Citizen</strong> or <strong>PWD</strong>? (20% Discount)
                            </label>
                        </div>

                        {{-- Hidden field to send discount flag to controller --}}
                        <input type="hidden" name="isDiscounted" id="isDiscountedInput" value="0">

                        <div class="mb-3">
                            <label for="cash" class="form-label">Cash Received</label>
                            <input type="number" step="0.01" class="form-control" name="cash" id="cash" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Proceed to Confirmation</button>
                    </form>
                </div>
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

                // Recompute discount/grand total if checkbox is checked
                updateDiscountUI();
            }
        })
        .catch(err => console.error(err));
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
