<?php
// Include the database connection file
require '../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: ../view/login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch customer details
$customer_sql = "SELECT * FROM customers WHERE user_id = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $user_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();

// Fetch vehicle details
$vehicle_sql = "SELECT * FROM vehicles WHERE customer_id = ?";
$vehicle_stmt = $conn->prepare($vehicle_sql);
$vehicle_stmt->bind_param("i", $customer['customer_id']);
$vehicle_stmt->execute();
$vehicle_result = $vehicle_stmt->get_result();
$vehicle = $vehicle_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Book a Service</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/inventory.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        .form-section {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: var(--secondary-blue);
        }
        .error {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
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
        <a href="#" class="menu-item">
            <i class='bx bx-calendar'></i>
            <span>Book a Service</span>
        </a>
        <a href="appointments.php" class="menu-item">
            <i class='bx bx-calendar'></i>
            <span>Appointments</span>
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
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-section">
            <h2 style="text-align: center; margin-bottom: 20px; color: var(--primary-blue);">Book a Car Wash Service</h2>
            <form id="bookingForm" action="#" class="contact-form">
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
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" disabled value="<?php echo htmlspecialchars($user['fname']. ' ' .$user['lname']); ?>">
                    <div class="error" id="nameError"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"  disabled value="<?php echo htmlspecialchars($user['email']); ?>">
                    <div class="error" id="emailError"></div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" disabled value="<?php echo htmlspecialchars($user['phone']); ?>">
                    <div class="error" id="phoneError"></div>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <select id="location" name="location" required>
                        <option value="">Select a location</option>
                        <option value="location1">26 Nkorman ansah estate, GRA Abia state</option>
                        <option value="location2">12 Muri Okunola Street, Ajah Lagos</option>
                        <option value="location3">156 Ojo Alaba, Ibadan Oyo state</option>
                        <option value="location4">11b Toyin Street, Ikeja Lagos</option>
                        <option value="location5">45 Balarabe Musa Crescent, Victoria Island Lagos</option>
                        <option value="location6">17 Allen Avenue, Ikeja Lagos</option>
                    </select>
                    <div class="error" id="locationError"></div>
                </div>
                <div class="form-group">
                    <label for="make">Car Make</label>
                    <input type="text" id="make" name="make" disabled value="<?php echo isset($vehicle['make']) ? htmlspecialchars($vehicle['make']) : ''; ?>">
                    <div class="error" id="makeError"></div>
                </div>
                <div class="form-group">
                    <label for="model">Car Model</label>
                    <input type="text" id="model" name="model" disabled value="<?php echo isset($vehicle['model']) ? htmlspecialchars($vehicle['model']) : ''; ?>">
                    <div class="error" id="modelError"></div>
                </div>
                <div class="form-group">
                    <label for="year">Car Year</label>
                    <input type="number" id="year" name="year" disabled value="<?php echo isset($vehicle['year']) ? htmlspecialchars($vehicle['year']) : ''; ?>">
                    <div class="error" id="yearError"></div>
                </div>
                <div class="form-group">
                    <label for="licensePlate">License Plate</label>
                    <input type="text" id="licensePlate" disabled name="licensePlate" value="<?php echo isset($vehicle['license_plate']) ? htmlspecialchars($vehicle['license_plate']) : ''; ?>">
                    <div class="error" id="licensePlateError"></div>
                </div>
                <button type="submit" class="submit-btn">Book Service</button>
            </form>
        </div>
    </div>
</div>
<script>
    function logoutUser() {
        // Clear sessions (usually handled on server side)
        fetch('../../actions/logout.php', {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                // Redirect to login page after logout
                window.location.href = 'login.html';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const bookingForm = document.getElementById('bookingForm');

        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const serviceName = document.getElementById('addServiceName').value;
            const location = document.getElementById('location').value;

            // Check if location is selected
            if (!location) {
                alert('Please select a location');
                return;
            }

            fetch('../actions/process_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `serviceName=${encodeURIComponent(serviceName)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Service booked successfully!');
                        window.location.href = 'appointments.php';
                    } else {
                        alert('Error booking service: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while booking the service.');
                });
        });
    });
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
