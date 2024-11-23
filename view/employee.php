<?php
// Include the database connection file
require '../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')){
    header("Location: login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$response = ["success" => false, "message" => ""];

// Check for any response messages from actions
if (isset($_SESSION['response'])) {
    $response = $_SESSION['response'];
    unset($_SESSION['response']);
}

$employees = array();
$shifts = array();

if ($_SESSION['role'] == 'admin') {
    // Fetch all employees for admin
    $stmt = $conn->prepare("SELECT employees.employee_id, users.fname, users.lname, users.email, users.phone, employees.role
        FROM employees
        INNER JOIN users ON employees.user_id = users.user_id");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format employee ID
            $emp_id = $row['employee_id'];
            $row['formatted_emp_id'] = sprintf("EMP%03d", $emp_id);

            // Set next pay date (last day of current month)
            $row['next_pay_date'] = date('Y-m-t');

            $employees[] = $row;
        }
    }

    // Fetch all shifts for admin
    $shift_stmt = $conn->prepare("SELECT es.*, u.fname, u.lname, u.email, u.phone
              FROM employee_shifts es
              INNER JOIN employees e ON es.employee_id = e.employee_id
              INNER JOIN users u ON e.user_id = u.user_id
              ORDER BY es.start_date ASC");
    $shift_stmt->execute();
    $shift_result = $shift_stmt->get_result();

    if ($shift_result->num_rows > 0) {
        $shifts = $shift_result->fetch_all(MYSQLI_ASSOC);
    }
}
else if ($_SESSION['role'] == 'employee') {
    // Fetch employee's own information
    $stmt = $conn->prepare("SELECT employees.employee_id, users.fname, users.lname, users.email, users.phone, employees.role
        FROM employees
        INNER JOIN users ON employees.user_id = users.user_id
        WHERE users.user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format employee ID
            $emp_id = $row['employee_id'];
            $row['formatted_emp_id'] = sprintf("EMP%03d", $emp_id);

            // Set next pay date (last day of current month)
            $row['next_pay_date'] = date('Y-m-t');

            $employees[] = $row;
        }
    }

    // Fetch employee's own shifts
    $shift_stmt = $conn->prepare("SELECT es.*, u.fname, u.lname, u.email, u.phone
              FROM employee_shifts es
              INNER JOIN employees e ON es.employee_id = e.employee_id
              INNER JOIN users u ON e.user_id = u.user_id
              WHERE u.user_id = ?
              ORDER BY es.start_date ASC");
    $shift_stmt->bind_param("i", $_SESSION['user_id']);
    $shift_stmt->execute();
    $shift_result = $shift_stmt->get_result();

    if ($shift_result->num_rows > 0) {
        $shifts = $shift_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Employee Management</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/employee.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            transition: all 0.3s ease;
        }

        .main-content {
            flex: 1;
            background: #f0f2f5;
            padding: 20px;
        }

        /* Employee management specific styles */
        .employees-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary-blue);
        }

        .employees-container {
            width: 100%;
        }

        .employee-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .employee-card {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .employee-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .employee-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent-silver);
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary-blue);
        }

        .employee-info h3 {
            margin: 0;
            color: var(--text-dark);
        }

        .employee-details {
            margin-top: 15px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--accent-silver);
        }

        .employee-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        /* Shift schedule styles */
        .shift-schedule {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .schedule-day {
            padding: 10px;
            background: #f8fafc;
            border-radius: 5px;
            text-align: center;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
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

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
        }

        .attendance-table th {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .shift-schedule {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .schedule-table thead {
            background-color: #f8fafc;
        }

        .schedule-table th,
        .schedule-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
            vertical-align: middle;
        }

        .schedule-table th {
            color: var(--text-dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .schedule-table tr:last-child td {
            border-bottom: none;
        }

        .schedule-table tr:hover {
            background-color: #f0f4f8;
            transition: background-color 0.3s ease;
        }

        .schedule-table .btn {
            margin-right: 5px;
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .schedule-table .btn-primary {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .schedule-table .btn-delete {
            background-color: #ef4444;
            color: var(--white);
        }

        /* Responsive Table */
        @media screen and (max-width: 768px) {
            .schedule-table {
                font-size: 0.9rem;
            }
            .schedule-table th,
            .schedule-table td {
                padding: 10px;
            }
        }

        @media screen and (max-width: 576px) {
            .schedule-table {
                font-size: 0.8rem;
            }
            .schedule-table th,
            .schedule-table td {
                padding: 8px;
            }
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
            <?php if ($_SESSION['role'] != 'employee'): ?>
                <a href="appointments.php" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Appointments</span>
                </a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'employee'): ?>
                <a href="employee.php" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>My Shifts</span>
                </a>
            <?php else: ?>
                <a href="employee.php" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>My Shifts</span>
                </a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] != 'employee'): ?>
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
            <?php endif; ?>
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
        <!-- Response Message Display -->
        <?php if ($response['success']): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($response['message']); ?>
            </div>
        <?php elseif (!empty($response['message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($response['message']); ?>
            </div>
        <?php endif; ?>

        <div class="employees-header">
            <h2>Employee Management</h2>
        </div>

        <div class="employees-container">
            <div class="employee-cards">
                <?php foreach ($employees as $employee): ?>
                    <div class="employee-card" data-emp-id="<?php echo $employee['employee_id']; ?>">
                        <div class="employee-header">
                            <div class="employee-avatar">
                                <?php echo htmlspecialchars($employee['fname'][0] . $employee['lname'][0]); ?>
                            </div>
                            <div class="employee-info">
                                <h3><?php echo htmlspecialchars($employee['fname'] . ' ' . $employee['lname']); ?></h3>
                                <span><?php echo htmlspecialchars($employee['role']); ?></span>
                            </div>
                        </div>
                        <div class="employee-details">
                            <div class="detail-item">
                                <span>Employee ID:</span>
                                <span><?php echo htmlspecialchars($employee['formatted_emp_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span>Next Pay Date:</span>
                                <span><?php echo htmlspecialchars($employee['next_pay_date']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span>Current Shift:</span>
                                <span>
                                    <?php
                                    $current_shift = "No shift assigned";
                                    foreach ($shifts as $shift) {
                                        if ($shift['employee_id'] === $employee['employee_id']) {
                                            $current_shift = $shift['shift_type'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($current_shift);
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($_SESSION['role'] != 'employee'): ?>
                            <div class="employee-actions">
                                <button class="btn btn-primary btn-edit">
                                    <i class='bx bx-edit'></i> Edit
                                </button>
                                <button class="btn btn-primary" onclick="openShiftAssignModal('<?php echo $employee['employee_id']; ?>')">
                                    <i class='bx bx-time'></i> Assign Shift
                                </button>
                                <button class="btn btn-delete" data-emp-id="<?php echo $employee['employee_id']; ?>">
                                    <i class='bx bx-trash'></i> Delete
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="shift-schedule">
            <h3>Employee Schedule</h3>
            <table class="schedule-table">
                <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Shift Type</th>
                    <?php if ($_SESSION['role'] != 'employee'): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($shifts as $shift): ?>
                    <tr data-shift-id="<?php echo $shift['shift_id']; ?>">
                        <td><?php echo htmlspecialchars($shift['fname'] . ' ' . $shift['lname']); ?></td>
                        <td><?php echo htmlspecialchars($shift['email']); ?></td>
                        <td><?php echo htmlspecialchars($shift['phone']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($shift['start_date'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($shift['end_date'])); ?></td>
                        <td><?php echo htmlspecialchars($shift['shift_type']); ?></td>
                        <?php if ($_SESSION['role'] != 'employee'): ?>
                            <td>
                                <button class="btn btn-primary btn-edit">Edit</button>
                                <button class="btn btn-delete">Delete</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Employee Edit Modal -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <h3 id="employeeModalTitle">Edit Employee</h3>
        <form id="employeeForm" action="../actions/employee_actions.php" method="POST">
            <input type="hidden" name="employee_id" id="edit-employee-id">
            <input type="hidden" name="action" id="employee-action">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" id="edit-first-name" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" id="edit-last-name" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" id="edit-phone" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" name="role" id="edit-role" required>
            </div>
            <div class="form-group">
                <button type="submit" name="submit_employee" class="btn btn-primary" id="employeeSubmitBtn">Update Employee</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Shift Edit Modal -->
<div id="editShiftModal" class="modal">
    <div class="modal-content">
        <h3>Edit Shift</h3>
        <form id="editShiftForm" action="../actions/employee_actions.php" method="POST">
            <input type="hidden" name="shift_id" id="edit-shift-id">
            <input type="hidden" name="action" value="update_shift">
            <div class="form-group">
                <label>Employee Name</label>
                <input type="text" id="edit-shift-employee-name" disabled>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" id="edit-shift-start-date" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" id="edit-shift-end-date" required>
            </div>
            <div class="form-group">
                <label>Shift Type</label>
                <select name="shift_type" id="edit-shift-type" required>
                    <option value="Morning (8AM - 4PM)">Morning (8AM - 4PM)</option>
                    <option value="Evening (2PM - 10PM)">Evening (2PM - 10PM)</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Shift</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Shift Assignment Modal -->
<div id="shiftAssignModal" class="modal">
    <div class="modal-content">
        <h3>Assign Shift</h3>
        <form id="shiftAssignForm" action="../actions/employee_actions.php" method="POST">
            <input type="hidden" name="employee_id" id="assign-shift-employee-id">
            <input type="hidden" name="action" value="assign_shift">
            <div class="form-group">
                <label>Shift Type</label>
                <select name="shift_type" required>
                    <option value="Morning (8AM - 4PM)">Morning (8AM - 4PM)</option>
                    <option value="Evening (2PM - 10PM)">Evening (2PM - 10PM)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Assign Shift</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
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
        // Modal Functions
        function closeModal() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }

        // Function to handle shift edit in the schedule table
        function handleScheduleEditClick() {
            document.querySelectorAll('.schedule-table .btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const shiftId = row.dataset.shiftId;
                    const employeeName = row.querySelector('td:nth-child(1)').textContent;
                    const startDate = row.querySelector('td:nth-child(4)').textContent;
                    const endDate = row.querySelector('td:nth-child(5)').textContent;
                    const shiftType = row.querySelector('td:nth-child(6)').textContent;

                    document.getElementById('edit-shift-id').value = shiftId;
                    document.getElementById('edit-shift-employee-name').value = employeeName;
                    document.getElementById('edit-shift-start-date').value = startDate;
                    document.getElementById('edit-shift-end-date').value = endDate;
                    document.getElementById('edit-shift-type').value = shiftType;
                    document.getElementById('editShiftModal').style.display = 'flex';
                });
            });
        }

        // Function to handle deletions
        function setupDeleteHandlers() {
            // Employee delete buttons
            document.querySelectorAll('.employee-card .btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const employeeId = this.dataset.empId;
                    if (confirm('Are you sure you want to delete this employee?')) {
                        window.location.href = `../actions/employee_actions.php`;
                    }
                });
            });

            // Shift delete buttons
            document.querySelectorAll('.schedule-table .btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const shiftId = this.closest('tr').dataset.shiftId;
                    if (confirm('Are you sure you want to delete this shift?')) {
                        window.location.href = `../actions/employee_actions.php`;
                    }
                });
            });
        }

        function openEmployeeEditModal(employeeId, firstName, lastName, phone, role) {
            document.getElementById('employeeModalTitle').textContent = 'Edit Employee';
            document.getElementById('edit-employee-id').value = employeeId;
            document.getElementById('edit-first-name').value = firstName;
            document.getElementById('edit-last-name').value = lastName;
            document.getElementById('edit-phone').value = phone;
            document.getElementById('edit-role').value = role;
            document.getElementById('employee-action').value = 'update_employee';
            document.getElementById('employeeSubmitBtn').textContent = 'Update Employee';
            document.getElementById('employeeModal').style.display = 'flex';
        }

        function openShiftEditModal(shiftId, employeeName, startDate, endDate, shiftType) {
            document.getElementById('edit-shift-id').value = shiftId;
            document.getElementById('edit-shift-employee-name').value = employeeName;
            document.getElementById('edit-shift-start-date').value = startDate;
            document.getElementById('edit-shift-end-date').value = endDate;
            document.getElementById('edit-shift-type').value = shiftType;
            document.getElementById('editShiftModal').style.display = 'flex';
        }

        function openShiftAssignModal(employeeId) {
            document.getElementById('assign-shift-employee-id').value = employeeId;
            document.getElementById('shiftAssignModal').style.display = 'flex';
        }

        // Setup Handlers
        handleScheduleEditClick();
        setupDeleteHandlers();

        // Event Listeners for Edit Buttons in Employee Cards
        document.querySelectorAll('.employee-card .btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.employee-card');
                const employeeId = card.dataset.empId;
                const fullName = card.querySelector('.employee-info h3').textContent.trim();
                const [firstName, lastName] = fullName.split(' ');
                const phone = card.querySelector('.detail-item:nth-child(2) span:last-child').textContent;
                const role = card.querySelector('.employee-info span').textContent;

                openEmployeeEditModal(employeeId, firstName, lastName, phone, role);
            });
        });

        // Assign Shift Buttons
        document.querySelectorAll('.btn-assign-shift').forEach(btn => {
            btn.addEventListener('click', function() {
                const employeeId = this.closest('.employee-card').dataset.empId;
                openShiftAssignModal(employeeId);
            });
        });

        // Close modal functionality
        document.querySelectorAll('.modal .btn').forEach(btn => {
            if (btn.textContent.trim() === 'Cancel') {
                btn.addEventListener('click', closeModal);
            }
        });
    });

    // Expose functions globally if needed for inline event handlers
    function closeModal() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }

    function openShiftAssignModal(employeeId) {
        document.getElementById('assign-shift-employee-id').value = employeeId;
        document.getElementById('shiftAssignModal').style.display = 'flex';
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