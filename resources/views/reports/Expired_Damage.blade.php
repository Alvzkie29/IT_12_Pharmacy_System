<!-- Expired/Damaged Items -->
                            <div class="col-md-6 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="h4 fw-semibold">Expired & Damaged Items</h3>
                                    <button id="view-all-expired" class="btn btn-sm btn-outline-primary">
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
                                            @forelse ($expiredDamagedItems as $item)
                                                <tr>
                                                    <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
                                                    <td>
                                                        @if($item->status === 'expired')
                                                            <span class="badge bg-danger">Expired</span>
                                                        @elseif($item->status === 'damaged')
                                                            <span class="badge bg-warning">Damaged</span>
                                                        @elseif($item->status === 'pulled_out')
                                                            <span class="badge bg-info">Pulled Out</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $item->created_at->format('M d, Y') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No expired or damaged items found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>
                            </div>