<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pharmacy')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="flex bg-gray-100">

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 h-screen bg-green-900 text-white flex flex-col shadow-lg transition-all duration-300">
        <!-- Logo + Toggle -->
        <div class="flex items-center justify-between p-6 border-b border-green-700">
            <span id="logoText" class="font-bold text-lg">LM3</span>
            <button id="toggleBtn" class="text-white focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-4 text-lg font-semibold tracking-wide">
            <a href="{{ route('dashboard.index') }}" 
               class="flex items-center gap-3 py-2 px-3 rounded-md transition 
                      {{ request()->routeIs('dashboard.index') ? 'bg-green-700 text-white' : 'hover:bg-green-800' }}">
                <i class="fas fa-home"></i>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="{{ route('inventory.index') }}" 
               class="flex items-center gap-3 py-2 px-3 rounded-md transition 
                      {{ request()->routeIs('inventory.index') ? 'bg-green-700 text-white' : 'hover:bg-green-800' }}">
                <i class="fas fa-boxes"></i>
                <span class="nav-text">Inventory</span>
            </a>
            <a href="{{ route('sales.index') }}" 
               class="flex items-center gap-3 py-2 px-3 rounded-md transition 
                      {{ request()->routeIs('sales.index') ? 'bg-green-700 text-white' : 'hover:bg-green-800' }}">
                <i class="fas fa-shopping-cart"></i>
                <span class="nav-text">Sales</span>
            </a>
            <a href="{{ route('reports.index') }}" 
               class="flex items-center gap-3 py-2 px-3 rounded-md transition 
                      {{ request()->routeIs('reports.index') ? 'bg-green-700 text-white' : 'hover:bg-green-800' }}">
                <i class="fas fa-chart-line"></i>
                <span class="nav-text">Reports</span>
            </a>
        </nav>

        <!-- Logout -->
        <div class="p-6 border-t border-green-700">
            <a href="#" 
               class="flex items-center justify-center gap-2 py-2 px-3 rounded-md bg-red-600 hover:bg-red-700 text-white font-bold">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-text">Log Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 transition-all duration-300">
        @yield('content')
    </main>

    <!-- Script -->
    <script>
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        const navTexts = document.querySelectorAll('.nav-text');
        const logoText = document.getElementById('logoText');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-20');

            // Toggle nav text visibility
            navTexts.forEach(text => {
                text.classList.toggle('hidden');
            });

            // Toggle logo text
            logoText.classList.toggle('hidden');
        });
    </script>

</body>
</html>
