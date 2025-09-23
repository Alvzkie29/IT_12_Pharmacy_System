<!-- Stock Movements -->
                            <div class="col-md-6 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="h4 fw-semibold">Stock Movements</h3>
                                    <button id="view-all-movements" 
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#stockMovementsModal">
                                        View All <i data-feather="chevron-right" class="ms-1" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th>Status</th>
                                                <th>Quantity</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($stocks as $stock)
                                                <tr>
                                                    <td>{{ $stock->product->productName ?? 'Unknown' }}</td>
                                                    <td>{!! $stock->status_badge !!}</td>
                                                    <td>{{ $stock->quantity }}</td>
                                                    <td>{{ $stock->movementDate}}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No stock records found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>