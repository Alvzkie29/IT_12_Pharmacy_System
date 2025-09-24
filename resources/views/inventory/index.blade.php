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

    {{-- Add Stock Button --}}
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addStockModal">
        Add Stock
    </button>

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
                            <th>Price</th>
                            <th>Batch No</th>
                            <th>Expiry</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td>{{ $stock->product->productName }}</td>
                                <td>{{ $stock->product->genericName }}</td>
                                <td>{{ $stock->product->productWeight }}</td>
                                <td>{{ $stock->product->dosageForm }}</td>
                                <td>{{ $stock->quantity }}</td>
                                <td>₱{{ number_format($stock->price, 2) }}</td>
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

                            <div class="modal fade" id="stockOutModal{{ $stock->stockID }}" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog">
                                <form action="{{ route('inventory.stockOut', $stock->stockID) }}" method="POST">
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
                                                  <option value="pullout">Pullout (near expiry)</option>
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
                                <td colspan="9" class="text-center text-muted">No stock available.</td>
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
    <form action="{{ route('inventory.stockIn') }}" method="POST">
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
                  <label for="price" class="form-label">Batch Price</label>
                  <input type="number" step="0.01" name="price" id="price" class="form-control" required>
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
@endsection
