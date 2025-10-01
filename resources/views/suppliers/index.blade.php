@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Suppliers</h1>

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

    {{-- Live Search --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="input-group mb-2" style="max-width: 500px;">
        <input 
            type="text" 
            id="supplierSearch" 
            class="form-control" 
            placeholder="Search suppliers...">
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        Add Supplier
    </button>
</div>


    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Supplier Name</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $i => $supplier)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $supplier->supplierName }}</td>
                    <td>{{ $supplier->contactInfo }}</td>
                    <td>{{ $supplier->address }}</td>
                    <td class="d-flex justify-content-center gap-1">
                        <!-- Edit Button triggers modal -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $supplier->supplierID }}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <!-- Delete Form -->
                        <form action="{{ route('suppliers.destroy', $supplier->supplierID) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this supplier?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No suppliers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Supplier Modal --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Supplier Name</label>
                        <input type="text" name="supplierName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contact Info</label>
                        <input type="text" name="contactInfo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-1">
    {{ $suppliers->links() }}
</div>

{{-- Edit Supplier Modals --}}
@foreach($suppliers as $supplier)
<div class="modal fade" id="editSupplierModal{{ $supplier->supplierID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('suppliers.update', $supplier->supplierID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Supplier Name</label>
                        <input type="text" name="supplierName" class="form-control" value="{{ $supplier->supplierName }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Contact Info</label>
                        <input type="text" name="contactInfo" class="form-control" value="{{ $supplier->contactInfo }}">
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" value="{{ $supplier->address }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
<script>
document.getElementById('supplierSearch').addEventListener('keyup', function () {
    let query = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        // Skip "No suppliers found" row
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
