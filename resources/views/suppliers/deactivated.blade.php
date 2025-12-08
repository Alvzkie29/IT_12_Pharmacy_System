@extends('layouts.app')

@section('content')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.suppliers-table tbody tr');
            
            tableRows.forEach(row => {
                const supplierName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const contactInfo = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const address = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                if (supplierName.includes(searchTerm) || contactInfo.includes(searchTerm) || address.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});</script>
<style>
    .page-header {
        background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: #856404;
    }
    
    .archive-badge {
        position: absolute;
        top: -10px;
        left: 0;
        background-color: #ffc107;
        color: #856404;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .deletion-date {
        font-style: italic;
        color: #6c757d;
        font-size: 0.8rem;
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
    }
    
    .suppliers-table tbody tr:last-child {
        border-bottom: none;
    }
    
    .badge-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;

        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-status.active {
        background-color: #d1e7dd;
        color: #0f5132;
    }
    
    .badge-status.inactive {
        background-color: #f8d7da;
        color: #842029;
    }
    
    .btn-action {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        margin-right: 0.25rem;
        border-radius: 4px;
    }
    
    .action-buttons {
        white-space: nowrap;
    }
    
    .archive-badge {
        position: absolute;
        top: -10px;
        left: 0;
        background-color: #ffc107;
        color: #856404;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
</style>

<div class="container-fluid">
    <div class="page-header position-relative">
        <span class="archive-badge"><i class="fas fa-archive me-1"></i>Archived</span>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold" style = "color: white;">Deactivated Suppliers</h1>
                <p class="mb-0 opacity-75">View and manage archived supplier records</p>
            </div>
            <div>
                <i class="fas fa-archive fa-3x text-white-50"></i>
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
                    id="liveSearch" 
                    class="form-control border-start-0" 
                    placeholder="Search deactivated suppliers by name..."
                    data-search-url="{{ route('suppliers.deactivated') }}">
            </div>
            <div>
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Active Suppliers
                </a>
            </div>
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
                        <th class="text-center">Deactivation Date</th>
                        
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
                            <span class="badge-status inactive">
                                {{ $supplier->updated_at->format('M d, Y') }}
                            </span>
                        </td>

                        <td>
                            <div class="action-buttons">
                                <form action="{{ route('suppliers.restore', $supplier->supplierID) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-success btn-action" title="Restore Supplier" type="submit" onclick="return confirm('Are you sure you want to restore this supplier?')">
                                        <i class="fas fa-trash-restore me-1"></i> Restore
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-archive fa-3x mb-3"></i>
                                <h5>No deactivated suppliers found</h5>
                                <p class="mb-0">Deactivated suppliers will appear here.</p>
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

{{-- No edit modals needed for archived suppliers --}}

@endsection