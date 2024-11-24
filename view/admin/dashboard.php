<?php
// Include the database connection file
require '../../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$response = ["success" => false, "message" => ""];

// Total Services Today
$result = $conn->query("SELECT COUNT(*) AS total_services FROM services WHERE DATE(created_at) = CURDATE()");
$cars_washed_today = $result->fetch_assoc()['total_services'];

// Total Revenue Today
$result = $conn->query("SELECT SUM(price) AS today_revenue FROM services");
$today_revenue = $result->fetch_assoc()['today_revenue'] ?? 0;

// Total Appointments for Today
$result = $conn->query("SELECT COUNT(*) AS total_appointments FROM appointments WHERE DATE(appointment_date) = CURDATE()");
$total_appointments_today = $result->fetch_assoc()['total_appointments'];

// Total Active Employees
$result = $conn->query("SELECT COUNT(*) AS total_active FROM employees");
$total_active_employees = $result->fetch_assoc()['total_active'];

// Get Monthly Performance Data (Last 4 weeks)
$monthlyQuery = "SELECT 
    WEEK(created_at) as week,
    COUNT(*) as services_count
    FROM services 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
    GROUP BY WEEK(created_at)
    ORDER BY week DESC
    LIMIT 4";
$monthlyResult = mysqli_query($conn, $monthlyQuery);

$weekLabels = [];
$weeklyData = [];
while ($row = mysqli_fetch_assoc($monthlyResult)) {
    $weekLabels[] = 'Week ' . $row['week'];
    $weeklyData[] = $row['services_count'];
}

// Get Service Distribution
$servicesQuery = "SELECT 
                    name,
                    COUNT(*) as service_count
                FROM services 
                GROUP BY name
                ORDER BY service_count DESC";
$servicesResult = mysqli_query($conn, $servicesQuery);

$serviceLabels = [];
$serviceValues = [];

while ($row = mysqli_fetch_assoc($servicesResult)) {
    $serviceLabels[] = $row['name'];
    $serviceValues[] = $row['service_count'];
}

// Fetch customers
$sql = "SELECT 
            customers.customer_id, 
            users.fname, 
            users.lname, 
            users.email, 
            users.phone, 
            customers.status,
            (SELECT COUNT(*) FROM services WHERE services.customer_id = customers.customer_id) as total_visits,
            (SELECT MAX(created_at) FROM services WHERE services.customer_id = customers.customer_id) as last_visit_date,
            (SELECT SUM(price) FROM services WHERE services.customer_id = customers.customer_id) as total_spent
        FROM customers
        INNER JOIN users ON customers.user_id = users.user_id
        ORDER BY total_spent DESC";

$result = $conn->query($sql);

$customers = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
            <a href="#" class="menu-item">
                <i class='bx bx-home'></i>
                <span>Dashboard</span>
            </a>
            <a href="../appointments.php" class="menu-item">
                <i class='bx bx-calendar'></i>
                <span>Appointments</span>
            </a>
            <a href="../employee.php" class="menu-item">
                <i class='bx bx-user'></i>
                <span>Employees</span>
            </a>
            <a href="../revenue-analytics.php" class="menu-item">
                <i class='bx bx-line-chart'></i>
                <span>Analytics</span>
            </a>
            <a href="../inventory.php" class="menu-item">
                <i class='bx bx-box'></i>
                <span>Inventory</span>
            </a>
            <a href="../customers.php" class="menu-item">
                <i class='bx bx-group'></i>
                <span>Customers</span>
            </a>
            <a href="../services.php" class="menu-item">
                <i class='bx bx-list-ul'></i>
                <span>Services</span>
            </a>
            <a href="../equipment.php" class="menu-item">
                <i class='bx bx-wrench'></i>
                <span>Equipment</span>
            </a>
            <a href="../promotions.php" class="menu-item">
                <i class='bx bx-gift'></i>
                <span>Promotions & Rewards</span>
            </a>
            <a href="../profile.php" class="menu-item">
                <i class='bx bx-user'></i>
                <span>Profile Settings</span>
            </a>
            <a href="../../actions/logout.php" class="menu-item" onclick="event.preventDefault(); logoutUser();">
                <i class='bx bx-exit'></i>Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Cars Serviced Today</h3>
                <div class="stat-value"><?php echo $cars_washed_today; ?></div>
            </div>
            <div class="stat-card">
                <h3>Today's Revenue</h3>
                <div class="stat-value">$<?php echo number_format($today_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Appointments Today</h3>
                <div class="stat-value"><?php echo $total_appointments_today; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Employees</h3>
                <div class="stat-value"><?php echo $total_active_employees; ?></div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-card">
                <h3>Monthly Performance</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Service Distribution</h3>
                <canvas id="serviceChart"></canvas>
            </div>
        </div>
        <div class="table-section">
            <h3>Top Customers</h3>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Total Visits</th>
                    <th>Total Spent</th>
                    <th>Last Visit</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['fname'] . ' ' . $customer['lname']); ?></td>
                        <td><?php echo htmlspecialchars($customer['total_visits']); ?></td>
                        <td>$<?php echo number_format($customer['total_spent']); ?></td>
                        <td><?php
                            $last_visit = new DateTime($customer['last_visit_date']);
                            $now = new DateTime();
                            $diff = $now->diff($last_visit);

                            if ($diff->days == 0) {
                                echo 'Today';
                            } elseif ($diff->days == 1) {
                                echo 'Yesterday';
                            } else {
                                echo $last_visit->format('M d, Y');
                            }
                            ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>

    function logoutUser() {
        // Clear sessions (usually handled on server side)
        fetch('../../actions/logout.php', {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                // Redirect to login page after logout
                window.location.href = '../login.html';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Performance Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_reverse($weekLabels)); ?>,
                datasets: [{
                    label: 'Cars Serviced',
                    data: <?php echo json_encode(array_reverse($weeklyData)); ?>,
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Service Distribution Chart
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($serviceLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($serviceValues); ?>,
                    backgroundColor: [
                        '#1e40af',
                        '#3b82f6',
                        '#60a5fa',
                        '#93c5fd'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    });
</script>
</body>
</html>