<?php
// Include the database connection file
require '../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
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
    <title>Kelenne Car Wash - Customer Profile</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .profile-container {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group input {
            padding: 10px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group.error input {
            border-color: #ff4d4d;
        }

        .error-message {
            color: #ff4d4d;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: var(--secondary-blue);
        }

        .submit-btn:disabled {
            background: #a0a0a0;
            cursor: not-allowed;
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
            <?php if ($_SESSION['role'] == 'customer'): ?>
                <a href="bookNow.php" class="menu-item">
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
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
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
            <?php else: ?>
                <a href="employee.php" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>My Shifts</span>
                </a>
                <a href="profile.php" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profile Settings</span>
                </a>
                <a href="../actions/logout.php" class="menu-item" onclick="event.preventDefault(); logoutUser();">
                    <i class='bx bx-exit'></i>Logout
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="main-content">
        <div class="profile-container">
            <h2 style="margin-bottom: 20px; color: var(--primary-blue);">User Profile</h2>
            <form class="profile-form" id="profileForm" action="../actions/update_profile.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName"
                               value="<?php echo htmlspecialchars($user['fname']); ?>"
                               required minlength="2" maxlength="50">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName"
                               value="<?php echo htmlspecialchars($user['lname']); ?>"
                               required minlength="2" maxlength="50">
                        <div class="error-message"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($user['phone']); ?>"
                               required pattern="^[0-9]{10,14}$"
                               placeholder="10-14 digit phone number">
                        <div class="error-message"></div>
                    </div>
                </div>

                <h3 style="margin: 20px 0 10px; color: var(--text-dark);">Car Details</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="carMake">Car Make</label>
                        <input type="text" id="carMake" name="carMake"
                               value="<?php echo isset($vehicle['make']) ? htmlspecialchars($vehicle['make']) : ''; ?>"
                               required minlength="2" maxlength="50"
                               placeholder="e.g., Toyota, Honda, Ford">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="carModel">Car Model</label>
                        <input type="text" id="carModel" name="carModel"
                               value="<?php echo isset($vehicle['model']) ? htmlspecialchars($vehicle['model']) : ''; ?>"
                               required minlength="2" maxlength="50">
                        <div class="error-message"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="carYear">Year</label>
                        <select id="carYear" name="carYear" required>
                            <!-- Dynamically generate years from 1990 to current year -->
                            <?php
                            $currentYear = date('Y');
                            for ($year = $currentYear; $year >= 1990; $year--) {
                                $selected = (isset($vehicle['year']) && $vehicle['year'] == $year) ? 'selected' : '';
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="licensePlate">License Plate Number</label>
                        <input type="text" id="licensePlate" name="licensePlate"
                               value="<?php echo isset($vehicle['license_plate']) ? htmlspecialchars($vehicle['license_plate']) : ''; ?>"
                               required minlength="6" maxlength="10"
                               pattern="^[A-Za-z0-9\s-]+$"
                               placeholder="Enter license plate">
                        <div class="error-message"></div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Update Profile</button>
            </form>
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

    // Reuse the JavaScript validation from the original HTML file
    document.addEventListener('DOMContentLoaded', function() {
        // Get form and all input elements
        const form = document.getElementById('profileForm');
        const inputs = form.querySelectorAll('input, select');
        const submitButton = form.querySelector('.submit-btn');

        // Validation rules and functions
        const validationRules = {
            firstName: {
                validate: (value) => value.length >= 2 && value.length <= 50,
                errorMessage: 'First name must be 2-50 characters long'
            },
            lastName: {
                validate: (value) => value.length >= 2 && value.length <= 50,
                errorMessage: 'Last name must be 2-50 characters long'
            },
            email: {
                validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
                errorMessage: 'Please enter a valid email address'
            },
            phone: {
                validate: (value) => /^[0-9]{10,14}$/.test(value),
                errorMessage: 'Phone number must be 10-14 digits'
            },
            carMake: {
                validate: (value) => value.length >= 2 && value.length <= 50,
                errorMessage: 'Car make must be 2-50 characters long'
            },
            carModel: {
                validate: (value) => value.length >= 2 && value.length <= 50,
                errorMessage: 'Car model must be 2-50 characters long'
            },
            carYear: {
                validate: (value) => value !== '',
                errorMessage: 'Please select a car year'
            },
            licensePlate: {
                validate: (value) => /^[A-Za-z0-9\s-]{6,10}$/.test(value),
                errorMessage: 'License plate must be 6-10 alphanumeric characters'
            }
        };

        // Functions for validation, error handling, etc.
        function validateInput(input) {
            const name = input.name;
            const value = input.value.trim();
            const rule = validationRules[name];

            if (rule) {
                if (!rule.validate(value)) {
                    showError(input, rule.errorMessage);
                    return false;
                } else {
                    clearError(input);
                    return true;
                }
            }
            return true;
        }


        function checkFormValidity() {
            const isValid = Array.from(inputs).every(input => {
                return validateInput(input);
            });

            submitButton.disabled = !isValid;
        }

        // Event listeners for inputs
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                validateInput(this);
                checkFormValidity();
            });
        });

        // Form submission handler
        form.addEventListener('submit', function(event) {
            let isValid = true;
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });
            if (!isValid) {
                event.preventDefault(); // Prevent submission if validation fails
            }
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