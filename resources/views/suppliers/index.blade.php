@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .search-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: none;
    }
    
    .suppliers-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .suppliers-table {
        margin: 0;
    }
    
    .suppliers-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        text-align: center;
    }
    
    .suppliers-table tbody td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
        text-align: center;
    }
    
    .suppliers-table tbody tr {
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }
    
    .suppliers-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .btn-action {
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .pagination {
        justify-content: center;
    }
    
    .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #667eea;
    }
    
    .page-link:hover {
        background-color: #667eea;
        color: white;
    }
    
    .page-item.active .page-link {
        background-color: #667eea;
        border-color: #667eea;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold">Suppliers Management</h1>
                <p class="mb-0 opacity-75">Manage your pharmacy suppliers and their information</p>
            </div>
            <div class="text-end">
                <i class="fas fa-truck-field fa-3x opacity-50"></i>
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

    {{-- Search Section --}}
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
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="fas fa-plus me-2"></i>Add Supplier
            </button>
        </div>
    </div>


    <!-- Suppliers Table -->
    <div class="suppliers-card">
        <div class="table-responsive">
            <table class="table suppliers-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Supplier Name</th>
                        <th>Contact Information</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $i => $supplier)
                    <tr>
                        <td>
                            <span class="badge bg-primary rounded-pill">{{ $i + 1 }}</span>
                        </td>
                        <td class="fw-medium text-start">{{ $supplier->supplierName }}</td>
                        <td class="text-start">
                            <i class="fas fa-phone me-2 text-muted"></i>{{ $supplier->contactInfo }}
                        </td>
                        <td class="text-start">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>{{ $supplier->address }}
                        </td>
                        <td>
                            <span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $supplier->supplierID }}" title="Edit Supplier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if ($supplier->is_active)
                                    <form action="{{ route('suppliers.deactivate', $supplier->supplierID) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-secondary btn-action" title="Deactivate Supplier" type="submit">
                                            <i class="fas fa-times-circle"></i> Deactivate
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('suppliers.activate', $supplier->supplierID) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-action" title="Activate Supplier" type="submit">
                                            <i class="fas fa-check-circle"></i> Activate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>No suppliers found</h5>
                                <p>Start by adding your first supplier using the button above.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $suppliers->links() }}
    </div>
</div>

{{-- Add Supplier Modal --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Add Supplier
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building me-2 text-primary"></i>Supplier Name
                            </label>
                            <input type="text" name="supplierName" class="form-control form-control-lg" placeholder="Enter supplier name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-phone me-2 text-primary"></i>Contact Information
                            </label>
                            <input type="text" name="contactInfo" class="form-control form-control-lg" placeholder="Enter phone number or email">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                            </label>
                            <textarea name="address" class="form-control form-control-lg" rows="3" placeholder="Enter supplier address"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>Add Supplier
                    </button>
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
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Supplier
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building me-2 text-primary"></i>Supplier Name
                            </label>
                            <input type="text" name="supplierName" class="form-control form-control-lg" value="{{ $supplier->supplierName }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-phone me-2 text-primary"></i>Contact Information
                            </label>
                            <input type="text" name="contactInfo" class="form-control form-control-lg" value="{{ $supplier->contactInfo }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                            </label>
                            <textarea name="address" class="form-control form-control-lg" rows="3">{{ $supplier->address }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>Update Supplier
                    </button>
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
