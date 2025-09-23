@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <style>
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .print-area {
            background-color: white;
            color: black;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-light">

                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reports</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button id="print-btn" class="btn btn-sm btn-outline-secondary">
                                <i data-feather="printer"></i>
                                Print Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Printable Area -->
                <div id="print-area" class="print-area card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <div>
                                <h2 class="h3 fw-bold">Sales Report</h2>
                                <p class="text-muted mb-0" id="report-date-range"></p>
                            </div>
                            <div class="text-end">
                                <p class="text-muted mb-0">Generated on: <span id="current-date"></span></p>
                                <p class="text-muted mb-0">Prepared by: <span class="fw-semibold">Admin</span></p>
                            </div>
                        </div>

                        <!-- Sales Chart with Filters -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h4 fw-semibold">Sales Performance</h3>
                                <div class="input-group" style="width: 150px;">
                                    <select id="chart-period" class="form-select form-select-sm">
                                        <option value="daily">Daily</option>
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="chart-container" style="height: 320px;">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>

                        
                        <div class="row mb-4">
                            <!-- Stock Movements -->
                            
                            @include('reports.stocks_widget')
                            <!-- Expired/Damaged Items -->

                            @include('reports.Expired_Damage')
                        </div>

                        <!-- Sales Summary -->
                        <div>
                            <h3 class="h4 fw-semibold mb-3">Sales Summary</h3>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Transactions</th>
                                            <th>Items Sold</th>
                                            <th>Total Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Jan 1, 2023</td>
                                            <td>24</td>
                                            <td>87</td>
                                            <td>₱8,745</td>
                                        </tr>
                                        
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td>Total</td>
                                            <td>160</td>
                                            <td>577</td>
                                            <td>₱56,305</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </div>

    <!-- Stock Movements Modal -->
    @include('reports.stocks_movement')

    <!-- Expired/Damaged Items Modal -->
    <div class="modal fade" id="expiredItemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Expired & Damaged Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Status</th>
                                    <th>Quantity</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody id="expired-body">
                                <!-- Sample data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <div class="text-muted">
                            Showing <span id="expired-current-page">1</span> to <span id="expired-total-pages">10</span> of <span id="expired-total-items">100</span> entries
                        </div>
                        <div>
                            <button id="expired-prev-page" class="btn btn-sm btn-outline-secondary me-1" disabled>
                                <i data-feather="chevron-left" style="width: 16px; height: 16px;"></i>
                            </button>
                            <button id="expired-next-page" class="btn btn-sm btn-outline-secondary">
                                <i data-feather="chevron-right" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var stockMovementsModal = document.getElementById('stockMovementsModal');
            stockMovementsModal.addEventListener('hidden.bs.modal', function () {
                // Remove ?page= from the URL without reloading
                if (window.location.search.includes('page=')) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        });
    </script>

    @if(request()->has('page'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var stockModal = new bootstrap.Modal(document.getElementById('stockMovementsModal'));
                stockModal.show();
            });
        </script>
    @endif
    <script>
        // Initialize AOS
        AOS.init();

        // Initialize Feather Icons
        feather.replace();

        // Set current date
        const now = new Date();
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });

        // Print functionality
        document.getElementById('print-btn').addEventListener('click', function() {
            window.print();
        });

        
        viewAllBtn.addEventListener('click', function() {
            currentPage = 1;
            generateSampleData(currentPage);
            stockModal.show();
        });

        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                generateSampleData(currentPage);
            }
        });

        nextPageBtn.addEventListener('click', function() {
            if (currentPage < Math.ceil(totalItems / itemsPerPage)) {
                currentPage++;
                generateSampleData(currentPage);
            }
        });

        // Expired/Damaged Items Modal functionality
        const expiredModal = new bootstrap.Modal(document.getElementById('expiredItemsModal'));
        const viewAllExpiredBtn = document.getElementById('view-all-expired');
        const expiredBody = document.getElementById('expired-body');
        const expiredPrevPageBtn = document.getElementById('expired-prev-page');
        const expiredNextPageBtn = document.getElementById('expired-next-page');
        const expiredCurrentPageSpan = document.getElementById('expired-current-page');
        const expiredTotalPagesSpan = document.getElementById('expired-total-pages');
        const expiredTotalItemsSpan = document.getElementById('expired-total-items');

        let expiredCurrentPage = 1;
        const expiredTotalItems = 85; // This would normally come from your API

        // Generate sample data for expired/damaged items
        function generateExpiredData(page) {
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            let html = '';
            
            for (let i = startIndex; i < endIndex; i++) {
                const statuses = ['Expired', 'Damaged', 'Pulled Out'];
                const statusColors = ['danger', 'warning', 'info'];
                const randomStatus = Math.floor(Math.random() * statuses.length);
                
                html += `
                    <tr>
                        <td>Product ${i+1}</td>
                        <td>BATCH-${Math.floor(Math.random() * 9000) + 1000}</td>
                        <td>
                            <span class="badge bg-${statusColors[randomStatus]}">
                                ${statuses[randomStatus]}
                            </span>
                        </td>
                        <td>${Math.floor(Math.random() * 20) + 1}</td>
                        <td>${new Date(Date.now() - Math.random() * 60 * 24 * 60 * 60 * 1000).toLocaleDateString()}</td>
                        <td>Reason for ${statuses[randomStatus].toLowerCase()} item ${i+1}</td>
                    </tr>
                `;
            }
            
            expiredBody.innerHTML = html;
            feather.replace();
            
            // Update pagination info
            expiredCurrentPageSpan.textContent = page;
            expiredTotalPagesSpan.textContent = Math.ceil(expiredTotalItems / itemsPerPage);
            expiredTotalItemsSpan.textContent = expiredTotalItems;
            
            // Update button states
            expiredPrevPageBtn.disabled = page === 1;
            expiredNextPageBtn.disabled = page === Math.ceil(expiredTotalItems / itemsPerPage);
        }

        viewAllExpiredBtn.addEventListener('click', function() {
            expiredCurrentPage = 1;
            generateExpiredData(expiredCurrentPage);
            expiredModal.show();
        });

        expiredPrevPageBtn.addEventListener('click', function() {
            if (expiredCurrentPage > 1) {
                expiredCurrentPage--;
                generateExpiredData(expiredCurrentPage);
            }
        });

        expiredNextPageBtn.addEventListener('click', function() {
            if (expiredCurrentPage < Math.ceil(expiredTotalItems / itemsPerPage)) {
                expiredCurrentPage++;
                generateExpiredData(expiredCurrentPage);
            }
        });

        // Pulled out modal functionality
        const pulledOutModal = new bootstrap.Modal(document.getElementById('pulledOutModal'));
        
        // This would normally be attached to a "View Details" button in the table
        // For demo purposes, we'll just show it when clicking any "Pulled Out" status
        document.querySelectorAll('.badge.bg-info').forEach(badge => {
            badge.closest('tr').addEventListener('click', function() {
                pulledOutModal.show();
            });
        });

        // Sales Chart Data
        const chartData = {
            daily: {
                labels: ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM', '8PM'],
                data: [3200, 4500, 6800, 5200, 7800, 9200, 6500]
            },
            monthly: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                data: [28745, 30230, 29560, 32450]
            },
            yearly: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                data: [125000, 118000, 132000, 127500, 140000, 145000, 138000, 152000, 148000, 160000, 155000, 168000]
            }
        };

        // Initialize Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        let salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.monthly.labels,
                datasets: [{
                    label: 'Sales (₱)',
                    data: chartData.monthly.data,
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 3,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Chart Filter with Dropdown
        document.getElementById('chart-period').addEventListener('change', function() {
            const period = this.value;
            updateChart(period);
        });

        function updateChart(period) {
            salesChart.data.labels = chartData[period].labels;
            salesChart.data.datasets[0].data = chartData[period].data;
            salesChart.update();
        }

        // Period filter functionality
        document.getElementById('report-period').addEventListener('change', function() {
            const period = this.value;
            let dateRange = '';
            
            switch(period) {
                case 'daily':
                    dateRange = now.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    break;
                case 'weekly':
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    const endOfWeek = new Date(now);
                    endOfWeek.setDate(now.getDate() + (6 - now.getDay()));
                    
                    dateRange = startOfWeek.toLocaleDateString('en-US', { 
                        month: 'long', 
                        day: 'numeric' 
                    }) + ' - ' + endOfWeek.toLocaleDateString('en-US', { 
                        month: 'long', 
                        day: 'numeric', 
                        year: 'numeric' 
                    });
                    break;
                case 'monthly':
                    dateRange = now.toLocaleDateString('en-US', { 
                        month: 'long', 
                        year: 'numeric' 
                    });
                    break;
                case 'yearly':
                    dateRange = now.toLocaleDateString('en-US', { 
                        year: 'numeric' 
                    });
                    break;
            }
            
            document.getElementById('report-date-range').textContent = dateRange;
            
            // Here you would normally fetch new data based on the selected period
            // For demo purposes, we'll just update the chart with random data
            const newData = salesChart.data.datasets[0].data.map(() => Math.floor(Math.random() * 20000) + 5000);
            salesChart.data.datasets[0].data = newData;
            salesChart.update();
        });
    </script>
</body>
</html>

</div>
@endsection
