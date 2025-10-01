@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Inventory</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search + Add Stock Button --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="input-group mb-2" style="max-width: 500px;">
            <input 
                type="text" 
                id="inventorySearch" 
                class="form-control" 
                placeholder="Search inventory...">
        </div>

        <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#addStockModal">
            <i class="bi bi-plus-circle me-1"></i> Add Stock
        </button>
    </div>

    {{-- Inventory Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Inventory</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Brand Name</th>
                            <th>Generic Name</th>
                            <th>Weight</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Purchase Price</th>
                            <th>Selling Price</th>
                            <th>Batch No</th>
                            <th>Expiry</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            @php
                                $rowClass = '';
                                if ($stock->expiryDate) {
                                    $expiry = \Carbon\Carbon::parse($stock->expiryDate);
                                    $today = \Carbon\Carbon::today();
                                    $monthsDiff = $today->diffInMonths($expiry, false);

                                    if ($monthsDiff < 0) {
                                        $rowClass = 'table-danger'; // Expired
                                    } elseif ($monthsDiff <= 6) {
                                        $rowClass = 'table-warning'; // Near expiry
                                    }
                                }
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td>{{ $stock->product->productName }}</td>
                                <td>{{ $stock->product->genericName }}</td>
                                <td>{{ $stock->product->productWeight }}</td>
                                <td>{{ $stock->product->dosageForm }}</td>
                                <td>{{ $stock->quantity }}</td>
                                <td>₱{{ number_format($stock->purchase_price, 2) }}</td>
                                <td>₱{{ number_format($stock->selling_price, 2) }}</td>
                                <td>{{ $stock->batchNo ?? 'N/A' }}</td>
                                <td>{{ $stock->expiryDate ?? 'N/A' }}</td>
                                <td>
                                    <!-- Stock Out Button -->
                                    <button 
                                        class="btn btn-warning btn-sm"  
                                        data-bs-toggle="modal"
                                        data-bs-target="#stockOutModal{{ $stock->stockID }}">
                                        <i class="bi bi-box-arrow-down"></i> Stock Out
                                    </button>
                                </td>
                            </tr>

                            {{-- Stock Out Modal --}}
                            <div class="modal fade" id="stockOutModal{{ $stock->stockID }}" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog">
                                <form action="{{ route('inventory.stockOut', $stock->stockID) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Confirm stock out for this product?');">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                      <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title">Stock Out Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                      </div>

                                      <div class="modal-body">
                                          <p><strong>Product:</strong> {{ $stock->product->productName }} ({{ $stock->product->genericName }})</p>
                                          <p><strong>Available:</strong> {{ $stock->quantity }}</p>

                                          <div class="mb-3">
                                              <label class="form-label">Quantity to Stock Out</label>
                                              <input type="number" name="quantity" class="form-control" min="1" max="{{ $stock->quantity }}" required>
                                          </div>

                                          <div class="mb-3">
                                                <label class="form-label">Reason</label>
                                                <select name="reason" class="form-select" required>
                                                    <option value="expired">Expired</option>
                                                    <option value="pulled_out_near_expiry">Pulled Out - Near Expiry</option>
                                                    <option value="pulled_out_damaged">Pulled Out - Damaged</option>
                                                    <option value="pulled_out_contaminated">Pulled Out - Contaminated</option>
                                                    <option value="pulled_out_other">Pulled Out - Other</option>
                                                </select>
                                            </div>
                                      </div>

                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Confirm Stock Out</button>
                                      </div>
                                    </div>
                                </form>
                              </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No stock available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- pagination --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $stocks->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Add Stock Modal --}}
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('inventory.stockIn') }}" 
          method="POST" 
          onsubmit="return confirm('Confirm adding this new stock?');">
        @csrf
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Add Stock</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label for="productID" class="form-label">Product</label>
                  <select name="productID" id="productID" class="form-select" required>
                      <option value="" disabled selected>Select product</option>
                      @foreach($products as $product)
                          <option value="{{ $product->productID }}">
                              {{ $product->productName }} ({{ $product->genericName }})
                          </option>
                      @endforeach
                  </select>
              </div>

              <div class="mb-3">
                  <label for="purchase_price" class="form-label">Purchase Price</label>
                  <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control" required>
              </div>

              <div class="mb-3">
                  <label for="selling_price" class="form-label">Selling Price</label>
                  <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control" required>
              </div>

              <div class="mb-3">
                  <label for="quantity_in" class="form-label">Quantity</label>
                  <input type="number" name="quantity" id="quantity_in" class="form-control" min="1" required>
              </div>

              <div class="mb-3">
                  <label for="batchNo" class="form-label">Batch Number</label>
                  <input type="text" name="batchNo" id="batchNo" class="form-control">
              </div>

              <div class="mb-3">
                  <label for="expiryDate" class="form-label">Expiry Date</label>
                  <input type="date" name="expiryDate" id="expiryDate" class="form-control">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Add Stock</button>
          </div>
        </div>
    </form>
  </div>
</div>
<script>
document.getElementById('inventorySearch').addEventListener('keyup', function () {
    let query = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        // skip "No stock available" row
        if (row.querySelector("td")?.classList.contains("text-muted")) {
            row.style.display = query === "" ? "" : "none";
            return;
        }

        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? "" : "none";
    });
});
</script>


@endsection
