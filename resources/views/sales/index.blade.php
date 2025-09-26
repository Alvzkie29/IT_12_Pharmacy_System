@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Point of Sale</h1>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Create Sale
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('sales.store') }}">
                @csrf

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Available Qty</th>
                            <th>Quantity to Sell</th>
                            <th>Unit Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 5; $i++)
                            <tr>
                                <td>
                                    <select name="items[{{ $i }}][stockID]" class="form-select">
                                        <option value="">-- Select Product --</option>
                                        @foreach($stocks as $stock)
                                            <option value="{{ $stock->stockID }}">
                                                {{ $stock->product->productName }} (Batch {{ $stock->batchNo }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control" placeholder="Auto-fill" readonly>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][quantity]" 
                                           class="form-control" min="1">
                                </td>
                                <td>
                                    <input type="text" class="form-control" placeholder="Auto-fill" readonly>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Checkout</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
