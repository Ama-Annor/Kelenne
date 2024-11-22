<?php
// Include the database connection file
require '../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle different actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create or Update Employee
    if (isset($_POST['action']) && ($_POST['action'] == 'create_employee' || $_POST['action'] == 'update_employee')) {
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $phone = sanitizeInput($_POST['phone']);
        $role = sanitizeInput($_POST['role']);

        if ($_POST['action'] == 'create_employee') {
            // Insert new user
            $user_sql = "INSERT INTO users (fname, lname, phone) VALUES (?, ?, ?)";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("sss", $first_name, $last_name, $phone);

            if ($user_stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert new employee
                $emp_sql = "INSERT INTO employees (user_id, role) VALUES (?, ?)";
                $emp_stmt = $conn->prepare($emp_sql);
                $emp_stmt->bind_param("is", $user_id, $role);

                if ($emp_stmt->execute()) {
                    $_SESSION['response'] = ["success" => true, "message" => "Employee created successfully"];
                } else {
                    $_SESSION['response'] = ["success" => false, "message" => "Error creating employee"];
                }

                $emp_stmt->close();
            } else {
                $_SESSION['response'] = ["success" => false, "message" => "Error creating user"];
            }

            $user_stmt->close();
        } else {
            // Update existing employee
            $employee_id = intval($_POST['employee_id']);

            // Update user details
            $user_sql = "UPDATE users u
                         JOIN employees e ON e.user_id = u.user_id
                         SET u.fname = ?, u.lname = ?, u.phone = ?, e.role = ?
                         WHERE e.employee_id = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("ssssi", $first_name, $last_name, $phone, $role, $employee_id);

            if ($user_stmt->execute()) {
                $_SESSION['response'] = ["success" => true, "message" => "Employee updated successfully"];
            } else {
                $_SESSION['response'] = ["success" => false, "message" => "Error updating employee"];
            }

            $user_stmt->close();
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == 'update_shift') {
        $shift_id = intval($_POST['shift_id']);
        $start_date = sanitizeInput($_POST['start_date']);
        $end_date = sanitizeInput($_POST['end_date']);
        $shift_type = sanitizeInput($_POST['shift_type']);

        // Validate dates
        if (strtotime($start_date) > strtotime($end_date)) {
            $_SESSION['response'] = ["success" => false, "message" => "Start date cannot be after end date"];
        } else {
            // Update shift in the database
            $shift_sql = "UPDATE employee_shifts 
                          SET start_date = ?, end_date = ?, shift_type = ? 
                          WHERE shift_id = ?";
            $shift_stmt = $conn->prepare($shift_sql);
            $shift_stmt->bind_param("sssi", $start_date, $end_date, $shift_type, $shift_id);

            if ($shift_stmt->execute()) {
                $_SESSION['response'] = ["success" => true, "message" => "Shift updated successfully"];
            } else {
                $_SESSION['response'] = ["success" => false, "message" => "Error updating shift"];
            }
            $shift_stmt->close();
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == 'assign_shift') {
        $employee_id = intval($_POST['employee_id']);
        $shift_type = sanitizeInput($_POST['shift_type']);
        $start_date = sanitizeInput($_POST['start_date']);
        $end_date = sanitizeInput($_POST['end_date']);

        // Validate dates
        if (strtotime($start_date) > strtotime($end_date)) {
            $_SESSION['response'] = ["success" => false, "message" => "Start date cannot be after end date"];
        } else {
            // Check if the employee already has an active shift
            $check_existing_shift_sql = "SELECT * FROM employee_shifts 
                                         WHERE employee_id = ? 
                                         AND ((? BETWEEN start_date AND end_date) 
                                         OR (? BETWEEN start_date AND end_date))";
            $check_stmt = $conn->prepare($check_existing_shift_sql);
            $check_stmt->bind_param("iss", $employee_id, $start_date, $end_date);
            $check_stmt->execute();
            $existing_shift_result = $check_stmt->get_result();

            if ($existing_shift_result->num_rows > 0) {
                // Shift conflict exists
                $_SESSION['response'] = ["success" => false, "message" => "An active shift already exists for this employee during the specified dates"];
            } else {
                // Insert new shift
                $shift_sql = "INSERT INTO employee_shifts 
                             (employee_id, start_date, end_date, shift_type) 
                             VALUES (?, ?, ?, ?)";
                $shift_stmt = $conn->prepare($shift_sql);
                $shift_stmt->bind_param("isss", $employee_id, $start_date, $end_date, $shift_type);

                if ($shift_stmt->execute()) {
                    $_SESSION['response'] = ["success" => true, "message" => "Shift assigned successfully"];
                } else {
                    $_SESSION['response'] = ["success" => false, "message" => "Error assigning shift"];
                }
                $shift_stmt->close();
            }
            $check_stmt->close();
        }
    }

    // Redirect back to the employee management page
    $conn->close();
    header("Location: ../view/employee.php");
    exit();
}



// Handle GET requests for deletions
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['action']) && isset($_GET['employee_id'])) {
        $action = sanitizeInput($_GET['action']);
        $employee_id = intval($_GET['employee_id']);

        if ($action == 'delete_employee') {
            // First, find the user_id associated with this employee
            $find_user_sql = "SELECT user_id FROM employees WHERE employee_id = ?";
            $find_user_stmt = $conn->prepare($find_user_sql);
            $find_user_stmt->bind_param("i", $employee_id);
            $find_user_stmt->execute();
            $result = $find_user_stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $user_id = $row['user_id'];

                // Delete from shifts first
                $delete_shifts_sql = "DELETE FROM employee_shifts WHERE employee_id = ?";
                $delete_shifts_stmt = $conn->prepare($delete_shifts_sql);
                $delete_shifts_stmt->bind_param("i", $employee_id);
                $delete_shifts_stmt->execute();

                // Delete from employees
                $delete_emp_sql = "DELETE FROM employees WHERE employee_id = ?";
                $delete_emp_stmt = $conn->prepare($delete_emp_sql);
                $delete_emp_stmt->bind_param("i", $employee_id);
                $delete_emp_stmt->execute();

                // Delete from users
                $delete_user_sql = "DELETE FROM users WHERE user_id = ?";
                $delete_user_stmt = $conn->prepare($delete_user_sql);
                $delete_user_stmt->bind_param("i", $user_id);
                $delete_user_stmt->execute();

                $_SESSION['response'] = ["success" => true, "message" => "Employee deleted successfully"];
            } else {
                $_SESSION['response'] = ["success" => false, "message" => "Employee not found"];
            }
        }
    }

    if (isset($_GET['action']) && isset($_GET['shift_id'])) {
        $action = sanitizeInput($_GET['action']);
        $shift_id = intval($_GET['shift_id']);

        if ($action == 'delete_shift') {
            $delete_shift_sql = "DELETE FROM employee_shifts WHERE shift_id = ?";
            $delete_shift_stmt = $conn->prepare($delete_shift_sql);
            $delete_shift_stmt->bind_param("i", $shift_id);

            if ($delete_shift_stmt->execute()) {
                $_SESSION['response'] = ["success" => true, "message" => "Shift deleted successfully"];
            } else {
                $_SESSION['response'] = ["success" => false, "message" => "Error deleting shift"];
            }
        }
    }

    $conn->close();
    header("Location: ../employee.php");
    exit();
}
?>