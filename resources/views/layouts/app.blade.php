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
        /* Sidebar Base Styles */
        #sidebar {
            position: sticky; 
            top: 0;
            height: 100vh; 
            overflow-y: auto;
            background: linear-gradient(180deg, #16a34a 0%, #15803d 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* Custom Scrollbar */
        #sidebar::-webkit-scrollbar {
            width: 6px;
        }
        #sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        #sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Logo Section */
        .sidebar-logo {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-logo h4 {
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Navigation Links */
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 12px 16px !important;
            margin-bottom: 8px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 16px;
        }

        /* Active Link */
        .active-link {
            background: linear-gradient(135deg, #14532d 0%, #166534 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(20, 83, 45, 0.4);
            transform: translateX(5px);
        }

        .active-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #fff;
            border-radius: 0 2px 2px 0;
        }

        .active-link:hover {
            background: linear-gradient(135deg, #166534 0%, #14532d 100%) !important;
            transform: translateX(8px);
        }

        /* Dropdown Styles */
        .nav-link[data-bs-toggle="collapse"] {
            position: relative;
        }

        .nav-link[data-bs-toggle="collapse"] .fas.fa-chevron-down {
            transition: transform 0.3s ease;
        }

        .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] .fas.fa-chevron-down {
            transform: rotate(180deg);
        }

        .collapse .nav-link {
            padding-left: 40px !important;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .collapse .nav-link i {
            font-size: 14px;
        }

        /* Logout Button */
        .logout-btn {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        }

        /* Mobile Toggle Button */
        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }


        /* Hover effect for icons */
        .nav-link:hover i {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        /* Focus states for accessibility */
        .nav-link:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
        }

        /* Better mobile responsiveness */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                left: -250px;
                transition: left 0.3s ease;
            }
            
            #sidebar.show {
                left: 0;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
        }

        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-light d-flex">

    <!-- Sidebar -->
    <aside id="sidebar" class="text-white p-4 d-flex flex-column" style="width: 250px; min-width: 250px; background-color: #198754; min-height: 100vh;">
        <!-- Logo -->
        <div class="sidebar-logo">
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="mb-0">LM3 Pharmacy</h4>
                <button class="btn btn-sm text-white sidebar-toggle d-md-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="nav flex-column" id="sidebarMenu">
            <a href="{{ route('dashboard.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('dashboard.index') ? 'active-link rounded' : '' }}">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>

            <a href="{{ route('suppliers.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('suppliers.index') ? 'active-link rounded' : '' }}">
                <i class="fa-solid fa-truck-field"></i> Suppliers
            </a>

            <!-- Inventory Dropdown -->
            @php
                $inventoryActive = request()->routeIs('products.index') || request()->routeIs('inventory.index') || request()->routeIs('inventory.nearExpiry');
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
                <a href="{{ route('inventory.nearExpiry') }}" 
                   class="nav-link text-white mb-1 {{ request()->routeIs('inventory.nearExpiry') ? 'active-link rounded' : '' }}">
                    <i class="fas fa-hourglass-end me-2"></i> Near Expiry
                </a>
            </div>

            <a href="{{ route('sales.index') }}" 
               class="nav-link text-white mb-2 {{ request()->routeIs('sales.index') ? 'active-link rounded' : '' }}">
                <i class="fas fa-shopping-cart me-2"></i> Sales
            </a>
            
            <!-- Reports Dropdown -->
            @php
                $reportsActive = request()->routeIs('reports.index') || request()->routeIs('reports.transaction_details');
            @endphp
            <a class="nav-link text-white mb-2 d-flex justify-content-between align-items-center {{ $reportsActive ? 'active-link rounded' : '' }}" 
               data-bs-toggle="collapse" href="#reportsMenu" role="button" 
               aria-expanded="{{ $reportsActive ? 'true' : 'false' }}" aria-controls="reportsMenu">
                <span><i class="fas fa-chart-line me-2"></i> Reports</span>
                <i class="fas fa-chevron-down small"></i>
            </a>
            <div class="collapse ps-3 {{ $reportsActive ? 'show' : '' }}" id="reportsMenu">
                <a href="{{ route('reports.index') }}" 
                   class="nav-link text-white mb-1 {{ request()->routeIs('reports.index') ? 'active-link rounded' : '' }}">
                    <i class="fas fa-file-alt me-2"></i> Summary Reports
                </a>
                <a href="{{ route('reports.transaction_details') }}" 
                   class="nav-link text-white mb-1 {{ request()->routeIs('reports.transaction_details') ? 'active-link rounded' : '' }}">
                    <i class="fas fa-receipt me-2"></i> Transaction Details
                </a>
            </div>
        </nav>

        <!-- Logout -->
        <div class="mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn logout-btn w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4" style="min-width: 0; overflow-x: hidden;">
        @yield('content')
    </main>

</body>
</html>
