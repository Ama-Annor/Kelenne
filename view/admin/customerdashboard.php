<?php
require_once '../../db/database.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || $_SESSION['role'] == 'employee' || $_SESSION['role'] == 'admin') {
    header("Location: ../login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fetch next appointment
$next_appointment_sql = "SELECT 
    a.appointment_id,
    a.appointment_date,
    a.status,
    u.fname,
    u.lname
FROM appointments a
INNER JOIN customers c ON a.customer_id = c.customer_id
INNER JOIN users u ON c.user_id = u.user_id
WHERE c.user_id = ?
AND a.appointment_date >= CURRENT_DATE()
ORDER BY a.appointment_date DESC
LIMIT 1";

$next_appointment_stmt = $conn->prepare($next_appointment_sql);
$next_appointment_stmt->bind_param("i", $_SESSION['user_id']);
$next_appointment_stmt->execute();
$next_appointment = $next_appointment_stmt->get_result()->fetch_assoc();
$next_appointment_stmt->close();

// Fetch loyalty points
$loyalty_points_sql = "SELECT loyalty_points FROM customer_profiles WHERE user_id = ?";
$loyalty_stmt = $conn->prepare($loyalty_points_sql);
$loyalty_stmt->bind_param("i", $_SESSION['user_id']);
$loyalty_stmt->execute();
$loyalty_points = $loyalty_stmt->get_result()->fetch_assoc()['loyalty_points'];
$loyalty_stmt->close();

// Fetch total visits
$visits_sql = "SELECT COUNT(*) AS total_visits
               FROM services s
               INNER JOIN customers c ON s.customer_id = c.customer_id
               INNER JOIN users u ON c.user_id = u.user_id
               WHERE c.user_id = ?";

$visits_stmt = $conn->prepare($visits_sql);
$visits_stmt->bind_param("i", $_SESSION['user_id']);
$visits_stmt->execute();
$total_visits = $visits_stmt->get_result()->fetch_assoc()['total_visits'];
$visits_stmt->close();

// Fetch available promotions
$rewards_sql = "SELECT COUNT(*) as total_promotions 
                FROM promotions 
                WHERE is_active = 1 
                AND valid_until >= CURRENT_DATE()";
$rewards_stmt = $conn->prepare($rewards_sql);
$rewards_stmt->execute();
$available_rewards = $rewards_stmt->get_result()->fetch_assoc()['total_promotions'];
$rewards_stmt->close();

// Fetch service history
$history_sql = "SELECT s.service_id, s.name, s.description, s.duration, s.price, s.is_active 
        FROM services s
        INNER JOIN customers c ON s.customer_id = c.customer_id
        WHERE c.user_id = ?
        LIMIT 5";

$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $_SESSION['user_id']);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Customer Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/customerdashboard.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class='bx bxs-car-wash'></i>
            <span>KELENNE</span>
        </div>
        <nav>
            <a href="#" class="menu-item">
                <i class='bx bx-home'></i>
                <span>Dashboard</span>
            </a>
            <a href="../bookNow.php" class="menu-item">
                <i class='bx bx-calendar-plus'></i>
                <span>Book a Service</span>
            </a>
            <a href="../appointments.php" class="menu-item">
                <i class='bx bx-calendar'></i>
                <span>My Appointments</span>
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
        <!-- Next Appointment Card -->
        <?php if ($next_appointment): ?>
            <div class="next-appointment">
                <div class="appointment-header">
                    <h2>Next Appointment</h2>
                </div>
                <div class="appointment-details">
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($next_appointment['appointment_date'])); ?></p>
                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($next_appointment['appointment_date'])); ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($next_appointment['fname'] . ' ' . $next_appointment['lname']); ?></p>
                    <div class="vehicle-status">Status: <?php echo htmlspecialchars($next_appointment['status']); ?></div>
                </div>
            </div>
        <?php else: ?>
            <div class="next-appointment">
                <div class="appointment-header">
                    <h2>No Upcoming Appointments</h2>
                    <div class="appointment-actions">
                        <a href="../bookNow.php" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Loyalty Points</h3>
                <div class="stat-value"><?php echo number_format($loyalty_points); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Visits</h3>
                <div class="stat-value"><?php echo number_format($total_visits); ?></div>
            </div>
            <div class="stat-card">
                <h3>Available Promotions</h3>
                <div class="stat-value"><?php echo number_format($available_rewards); ?></div>
            </div>
        </div>

        <!-- Service History -->
        <div class="history-section">
            <h2>Recent Services</h2>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $history_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['duration']); ?> mins</td>
                        <td>₦<?php echo number_format($row['price']); ?></td>
                        <td><?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($history_result->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">No services available</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function rescheduleAppointment(appointmentId) {
        window.location.href = `reschedule.php?id=${appointmentId}`;
    }

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
</script>
</body>
</html>