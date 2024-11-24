<?php
require '../db/database.php';
require_once '../actions/revenue-analytics_actions.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get current day of month
$currentDay = date('d');

// Get analytics data
$analyticsData = getAnalyticsData($currentDay);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Revenue & Analytics</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        :root {
            --primary-blue: #1e40af;
            --secondary-blue: #3b82f6;
            --accent-silver: #e5e7eb;
            --text-dark: #1f2937;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--primary-blue);
            color: var(--white);
            padding: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo i {
            font-size: 2rem;
        }

        .logo span {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px;
            color: var(--white);
            text-decoration: none;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .main-content {
            flex: 1;
            background: #f0f2f5;
            padding: 20px;
        }

        .revenue-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: var(--text-dark);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-blue);
        }

        .chart-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-filters {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }

        .services-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .table-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .revenue-table {
            width: 100%;
            border-collapse: collapse;
        }

        .revenue-table th,
        .revenue-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
        }

        .revenue-table th {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .trend-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .trend-up {
            color: #10B981;
        }

        .trend-down {
            color: #EF4444;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class='bx bxs-car-wash'></i>
            <span>KELENNE</span>
        </div>
        <nav>
            <a href="admin/dashboard.php" class="menu-item">
                <i class='bx bx-home'></i>
                <span>Dashboard</span>
            </a>
            <a href="appointments.php" class="menu-item">
                <i class='bx bx-calendar'></i>
                <span>Appointments</span>
            </a>
            <a href="employee.php" class="menu-item">
                <i class='bx bx-user'></i>
                <span>Employees</span>
            </a>
            <a href="revenue-analytics.php" class="menu-item">
                <i class='bx bx-line-chart'></i>
                <span>Analytics</span>
            </a>
            <a href="inventory.php" class="menu-item">
                <i class='bx bx-box'></i>
                <span>Inventory</span>
            </a>
            <a href="customers.php" class="menu-item">
                <i class='bx bx-group'></i>
                <span>Customers</span>
            </a>
            <a href="services.php" class="menu-item">
                <i class='bx bx-list-ul'></i>
                <span>Services</span>
            </a>
            <a href="equipment.php" class="menu-item">
                <i class='bx bx-wrench'></i>
                <span>Equipment</span>
            </a>
            <a href="promotions.php" class="menu-item">
                <i class='bx bx-gift'></i>
                <span>Promotions & Rewards</span>
            </a>
            <a href="profile.php" class="menu-item">
                <i class='bx bx-user'></i>
                <span>Profile Settings</span>
            </a>
            <a href="../actions/logout.php" class="menu-item" onclick="event.preventDefault(); logoutUser();">
                <i class='bx bx-exit'></i>Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <!-- Revenue Statistics -->
        <div class="revenue-stats">
            <div class="stat-card">
                <h3>Expected Total Revenue (Monthly)</h3>
                <div class="stat-value">$<?php echo number_format($analyticsData['monthlyRevenue']); ?></div>
                <div class="trend-indicator trend-up">
                    <i class='bx bx-up-arrow-alt'></i>
                    <span>Based on current services</span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Expected Average Daily Revenue</h3>
                <div class="stat-value">$<?php echo number_format($analyticsData['dailyRevenue']); ?></div>
                <div class="trend-indicator trend-up">
                    <i class='bx bx-up-arrow-alt'></i>
                    <span>Based on <?php echo $currentDay; ?> days</span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Expected Services</h3>
                <div class="stat-value"><?php echo $analyticsData['totalServices']; ?></div>
                <div class="trend-indicator">
                    <span>Total Available Services</span>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h2>Revenue Trends</h2>
                <div class="chart-filters">
                    <button class="filter-btn" onclick="updateChart('daily')">Daily</button>
                </div>
            </div>
            <canvas id="revenueChart"></canvas>
        </div>

        <!-- Services Breakdown -->
        <div class="services-breakdown">
            <div class="chart-container">
                <h2>Services Revenue Distribution</h2>
                <canvas id="servicesChart"></canvas>
            </div>
            <div class="table-container">
                <h2>Top Services by Revenue</h2>
                <table class="revenue-table">
                    <thead>
                    <tr>
                        <th>Service</th>
                        <th>Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($analyticsData['topServices'] as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td>$<?php echo number_format($service['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>

    function logoutUser() {
        // Clear sessions (usually handled on server side)
        fetch('../actions/logout.php', {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                // Redirect to login page after logout
                window.location.href = 'login.html';
            }
        });
    }

    // Initialize charts with PHP data
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const servicesCtx = document.getElementById('servicesChart').getContext('2d');

    // Parse PHP data for charts
    const servicesData = <?php echo json_encode($analyticsData['chartData']); ?>;

    // Revenue Chart
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: servicesData.dailyLabels,
            datasets: [{
                label: 'Daily Revenue',
                data: servicesData.dailyRevenue,
                borderColor: '#1e40af',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(30, 64, 175, 0.1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Services Chart
    const servicesChart = new Chart(servicesCtx, {
        type: 'doughnut',
        data: {
            labels: servicesData.serviceLabels,
            datasets: [{
                data: servicesData.serviceValues,
                backgroundColor: [
                    '#1e40af',
                    '#3b82f6',
                    '#60a5fa',
                    '#93c5fd',
                    '#dbeafe'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Update chart based on time filter
    function updateChart(period) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Make AJAX call to get new data based on period
        fetch(`revenue-analytics_actions.php?action=getChartData&period=${period}`)
            .then(response => response.json())
            .then(data => {
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.values;
                revenueChart.update();
            });
    }
</script>
<script type="text/javascript">
    (function() {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    })();
</script>
</body>
</html>