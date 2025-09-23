<div class="modal fade" id="stockMovementsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Stock Movements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Date</th>
                                    
                                </tr>
                            </thead>
                            <tbody id="movements-body">
                                @forelse ($allStockMovements as $movement)
                                    <tr>
                                        <td>{{ $movement->product->productName ?? 'Unknown' }}</td>
                                        <td>{{ $movement->batchNo}}</td>
                                        <td>{!! $movement->status_badge !!}</td>
                                        <td>{{ $movement->type === 'IN' ? '+' : '-' }}{{ $movement->quantity }}</td>
                                        <td>{{ $movement->movementDate}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No stock movements found</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <div class="text-muted">
                            Showing {{ $allStockMovements->firstItem() }} to 
                            {{ $allStockMovements->lastItem() }} of 
                            {{ $allStockMovements->total() }} entries
                        </div>
                        <div>
                            {{-- Laravel automatically disables prev/next when needed --}}
                            {{ $allStockMovements->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>