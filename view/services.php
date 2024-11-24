<?php
// Include the database connection file
require '../db/database.php';

// Start the session
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

$response = ["success" => false, "message" => ""];

// Total Services
$result = $conn->query("SELECT COUNT(*) AS total_services FROM services");
$total_services = $result->fetch_assoc()['total_services'];

// Active services
$result = $conn->query("SELECT COUNT(*) AS active_services FROM services WHERE is_active = 1");
$active_services = $result->fetch_assoc()['active_services'];

// Fetch services
$sql = "SELECT service_id, name, description, duration, price, is_active
        FROM services";
$result = $conn->query($sql);

// Store services in an array
$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Services Management</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
            color: var(--white);
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .logo i {
            margin-right: 10px;
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

        .service-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 500px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--secondary-blue);
            color: var(--white);
        }

        .table-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
        }

        .services-table th,
        .services-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
        }

        .services-table th {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .service-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .status-active {
            background: #DEF7EC;
            color: #03543F;
        }

        .status-inactive {
            background: #FDE8E8;
            color: #9B1C1C;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .btn-view {
            background: #10B981;
            color: white;
        }

        .btn-edit {
            background: #F59E0B;
            color: white;
        }

        .btn-delete {
            background: #EF4444;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            position: relative;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
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

        <!-- Service Statistics -->
        <div class="service-stats">
            <div class="stat-card">
                <h3>Total Services</h3>
                <div class="stat-value"><?php echo htmlspecialchars($total_services); ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Services</h3>
                <div class="stat-value"><?php echo htmlspecialchars($active_services); ?></div>
            </div>
        </div>

        <div class="actions-bar">
            <div class="search-bar">
                <input type="text" placeholder="Search services..." id="searchInput">
                <button class="btn btn-secondary" onclick="searchServices()">
                    <i class='bx bx-search'></i> Search
                </button>
            </div>
            <button class="btn btn-primary" onclick="openAddServiceModal()">
                <i class='bx bx-plus'></i> Add New Service
            </button>
        </div>

        <div class="table-container">
            <table class="services-table">
                <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Duration</th>
                    <th>Price ($)</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="servicesTableBody">
                <?php foreach ($services as $service): ?>
                    <tr data-service-id="<?php echo $service['service_id']; ?>">
                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                        <td><?php echo htmlspecialchars($service['duration'] ?? 'Not Set'); ?></td>
                        <td><?php echo htmlspecialchars($service['price'] ?? 'Not Set'); ?></td>
                        <td><?php echo htmlspecialchars($service['description']); ?></td>
                        <td>
                            <span class="service-status <?php echo $service['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewService(<?php echo $service['service_id']; ?>)">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editService(<?php echo $service['service_id']; ?>)">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteService(<?php echo $service['service_id']; ?>)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div id="addServiceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddServiceModal()">&times;</span>
        <h2>Add New Service</h2>
        <form id="addServiceForm">
            <div class="form-group">
                <label for="addServiceCustomer">Customer</label>
                <select id="addServiceCustomer" required>
                    <option value="">Select Customer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="addServiceName">Service Name</label>
                <select id="addServiceName" required>
                    <option value="Waxing">Waxing</option>
                    <option value="Polishing">Polishing</option>
                    <option value="Interior Detailing">Interior Detailing</option>
                    <option value="Window Treatment">Window Treatment</option>
                    <option value="Tire and Wheel Cleaning">Tire and Wheel Cleaning</option>
                    <option value="Undercarriage Wash">Polishing</option>
                    <option value="Steam Cleaning">Steam Cleaning</option>
                    <option value="Air Freshening">Air Freshening</option>
                    <option value="Headlight Restoration">Headlight Restoration</option>
                    <option value="Paint Correction">Paint Correction</option>
                </select>
            </div>
            <div class="form-group">
                <label for="addServiceDuration">Duration (minutes)</label>
                <input type="number" id="addServiceDuration" required>
            </div>
            <div class="form-group">
                <label for="addServicePrice">Price ($)</label>
                <input type="number" id="addServicePrice" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="addServiceDescription">Description</label>
                <textarea id="addServiceDescription" required></textarea>
            </div>
            <div class="form-group">
                <label for="addServiceStatus">Status</label>
                <select id="addServiceStatus" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Service</button>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div id="editServiceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditServiceModal()">&times;</span>
        <h2>Edit Service</h2>
        <form id="editServiceForm">
            <input type="hidden" id="editServiceId">
            <div class="form-group">
                <label for="editServiceName">Service Name</label>
                <input type="text" id="editServiceName" required>
            </div>
            <div class="form-group">
                <label for="editServiceDuration">Duration (minutes)</label>
                <input type="number" id="editServiceDuration" required>
            </div>
            <div class="form-group">
                <label for="editServicePrice">Price ($)</label>
                <input type="number" id="editServicePrice" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="editServiceDescription">Description</label>
                <textarea id="editServiceDescription" required></textarea>
            </div>
            <div class="form-group">
                <label for="editServiceStatus">Status</label>
                <select id="editServiceStatus" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Service</button>
        </form>
    </div>
</div>

<!-- View Service Modal -->
<div id="viewServiceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewServiceModal()">&times;</span>
        <h2>Service Details</h2>
        <div id="serviceDetails"></div>
    </div>
</div>

<script src="../assets/js/services.js"></script>
<script type="text/javascript">

    function logoutUser() {
        // Clear sessions 
        fetch('../actions/logout.php', {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                // Redirect to login page after logout
                window.location.href = 'login.html';
            }
        });
    }

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