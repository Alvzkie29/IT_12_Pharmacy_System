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
    
    .search-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .suppliers-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        background: white;
    }
    
    .suppliers-table {
        margin: 0;
    }
    
    .suppliers-table thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .suppliers-table tbody td {
        padding: 0.875rem 1rem;
        border: none;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .suppliers-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }
    
    .suppliers-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .btn-action {
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 12px 12px 0 0;
        padding: 1.25rem 1.5rem;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .badge-status {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-status.active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .badge-status.inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f1b0b7;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Suppliers Management</h1>
                <p class="mb-0 opacity-75">Manage your pharmacy suppliers and vendor information</p>
            </div>
            <div class="text-end">
                <i class="fas fa-truck fa-2x opacity-50"></i>
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search and Actions Section --}}
    <div class="search-section">
        <div class="d-flex justify-content-between align-items-center">
            <div class="input-group" style="max-width: 500px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input 
                    type="text" 
                    id="supplierSearch" 
                    class="form-control border-start-0" 
                    placeholder="Search suppliers by name, contact, or address...">
            </div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                Add Supplier
            </button>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="suppliers-card">
        <div class="table-responsive">
            <table class="table suppliers-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th class="text-start">Supplier Name</th>
                        <th class="text-start">Contact Information</th>
                        <th class="text-start">Address</th>
                        <th class="text-center">Status</th>
                        <th style="width: 200px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $i => $supplier)
                    <tr>
                        <td class="text-muted fw-medium">
                            {{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $i + 1 }}
                        </td>
                        <td class="fw-medium text-start">{{ $supplier->supplierName }}</td>
                        <td class="text-start">
                            <div class="small">{{ $supplier->contactInfo }}</div>
                        </td>
                        <td class="text-start">
                            <div class="small text-muted">{{ $supplier->address }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge-status {{ $supplier->is_active ? 'active' : 'inactive' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $supplier->supplierID }}" title="Edit Supplier">
                                    Edit
                                </button>
                                @if ($supplier->is_active)
                                    <form action="{{ route('suppliers.deactivate', $supplier->supplierID) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-warning btn-action" title="Deactivate Supplier" type="submit" onclick="return confirm('Are you sure you want to deactivate this supplier?')">
                                            Deactivate
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('suppliers.activate', $supplier->supplierID) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-success btn-action" title="Activate Supplier" type="submit">
                                            Activate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-truck fa-3x mb-3"></i>
                                <h5>No suppliers found</h5>
                                <p class="mb-0">Start by adding your first supplier using the button above.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($suppliers->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>

{{-- Add Supplier Modal --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Supplier Name</label>
                            <input type="text" name="supplierName" class="form-control" placeholder="Enter supplier name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Contact Information</label>
                            <input type="text" name="contactInfo" class="form-control" placeholder="Enter phone number or email">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter supplier address"></textarea>
                        </div>
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

{{-- Edit Supplier Modals --}}
@foreach($suppliers as $supplier)
<div class="modal fade" id="editSupplierModal{{ $supplier->supplierID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('suppliers.update', $supplier->supplierID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Supplier Name</label>
                            <input type="text" name="supplierName" class="form-control" value="{{ $supplier->supplierName }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Contact Information</label>
                            <input type="text" name="contactInfo" class="form-control" value="{{ $supplier->contactInfo }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="3">{{ $supplier->address }}</textarea>
                        </div>
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
        // skip "No suppliers found" row
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