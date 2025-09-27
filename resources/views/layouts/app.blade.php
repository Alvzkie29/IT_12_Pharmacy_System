<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pharmacy')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        .active-link {
            background-color: #14532d; 
            color: #fff !important;
        }
        .active-link:hover {
            background-color: #166534; 
        }
        #sidebar {
            position: sticky; 
            top: 0;
            height: 100vh; 
            overflow-y: auto; 
        }
    </style>
</head>
<body class="bg-light d-flex">

    <!-- Sidebar -->
    <aside id="sidebar" class="bg-success text-white p-3 d-flex flex-column shadow-lg" style="width: 250px;">
        <!-- Logo -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="fw-bold fs-5">LM3</span>
            <button class="btn btn-sm text-white d-md-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="nav flex-column" id="sidebarMenu">
            <a href="{{ route('dashboard.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('dashboard.index') ? 'active-link rounded' : '' }}">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>

            <a href="{{ route('suppliers.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('supplier.index') ? 'active-link rounded' : '' }}">
                <i class="fas fa-shopping-cart me-2"></i> Suppliers
            </a>

            <!-- Inventory Dropdown -->
            @php
                $inventoryActive = request()->routeIs('products.index') || request()->routeIs('inventory.index');
            @endphp
            <a class="nav-link text-white mb-2 d-flex justify-content-between align-items-center {{ $inventoryActive ? 'active-link rounded' : '' }}" 
               data-bs-toggle="collapse" href="#inventoryMenu" role="button" 
               aria-expanded="{{ $inventoryActive ? 'true' : 'false' }}" aria-controls="inventoryMenu">
                <span><i class="fas fa-box me-2"></i> Inventory</span>
                <i class="fas fa-chevron-down small"></i>
            </a>
            <div class="collapse ps-3 {{ $inventoryActive ? 'show' : '' }}" id="inventoryMenu">
                <a href="{{ route('products.index') }}" 
                   class="nav-link text-white mb-1 {{ request()->routeIs('products.index') ? 'active-link rounded' : '' }}">
                    <i class="fas fa-tags me-2"></i> Products
                </a>
                <a href="{{ route('inventory.index') }}" 
                   class="nav-link text-white mb-1 {{ request()->routeIs('inventory.index') ? 'active-link rounded' : '' }}">
                    <i class="fas fa-warehouse me-2"></i> Stocks
                </a>
            </div>

            <a href="{{ route('sales.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('sales.index') ? 'active-link rounded' : '' }}">
                <i class="fas fa-shopping-cart me-2"></i> Sales
            </a>
            <a href="{{ route('reports.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('reports.index') ? 'active-link rounded' : '' }}">
                <i class="fas fa-chart-line me-2"></i> Reports
            </a>
        </nav>

        <!-- Logout -->
        <div class="mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4">
        @yield('content')
    </main>
</body>
</html>
