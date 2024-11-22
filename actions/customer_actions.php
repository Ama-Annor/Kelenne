<?php
require '../db/database.php';
session_start();
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function handleResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get customer details
function getCustomerDetails($conn, $customer_id) {
    $sql = "SELECT c.*, u.fname, u.lname, u.email, u.phone,
            (SELECT COUNT(*) FROM services WHERE customer_id = c.customer_id) as total_visits,
            (SELECT MAX(created_at) FROM services WHERE customer_id = c.customer_id) as last_visit
            FROM customers c
            INNER JOIN users u ON c.user_id = u.user_id
            WHERE c.customer_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    // Fetch vehicle details
    $vehicle_sql = "SELECT * FROM vehicles WHERE customer_id = ?";
    $vehicle_stmt = $conn->prepare($vehicle_sql);
    $vehicle_stmt->bind_param("i", $customer_id);
    $vehicle_stmt->execute();
    $vehicle_result = $vehicle_stmt->get_result();
    $customer['vehicle'] = $vehicle_result->fetch_assoc() ?: null;

    return $customer;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch($action) {
        case 'view':
            $customer_id = intval($_POST['customer_id']);
            $customer = getCustomerDetails($conn, $customer_id);

            if ($customer) {
                handleResponse(true, "Customer details retrieved", $customer);
            } else {
                handleResponse(false, "Customer not found");
            }
            break;

        case 'add':
            $fname = sanitizeInput($_POST['fname']);
            $lname = sanitizeInput($_POST['lname']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $status = sanitizeInput($_POST['status']);

            // Start transaction
            $conn->begin_transaction();

            try {
                // First insert into users table
                $sql = "INSERT INTO users (fname, lname, email, phone, user_type) VALUES (?, ?, ?, ?, 'customer')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $fname, $lname, $email, $phone);
                $stmt->execute();
                $user_id = $conn->insert_id;

                // Then insert into customers table
                $sql = "INSERT INTO customers (user_id, status, notes) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $user_id, $status, $notes);
                $stmt->execute();

                $vehicle_sql = "INSERT INTO vehicles (customer_id, make, model, year, license_plate) 
                        VALUES (?, ?, ?, ?, ?)";
                $vehicle_stmt = $conn->prepare($vehicle_sql);
                $vehicle_stmt->bind_param("issss",
                    $conn->insert_id,
                    $_POST['vehicle_make'],
                    $_POST['vehicle_model'],
                    $_POST['vehicle_year'],
                    $_POST['vehicle_license_plate']
                );
                $vehicle_stmt->execute();

                $conn->commit();
                handleResponse(true, "Customer added successfully");
            } catch (Exception $e) {
                $conn->rollback();
                handleResponse(false, "Error adding customer: " . $e->getMessage());
            }
            break;

        case 'edit':
            $customer_id = intval($_POST['customer_id']);
            $fname = sanitizeInput($_POST['fname']);
            $lname = sanitizeInput($_POST['lname']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $status = sanitizeInput($_POST['status']);

            $conn->begin_transaction();

            try {
                // Get user_id from customers table
                $sql = "SELECT user_id FROM customers WHERE customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_id = $result->fetch_assoc()['user_id'];

                // Update users table
                $sql = "UPDATE users SET fname = ?, lname = ?, email = ?, phone = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $fname, $lname, $email, $phone, $user_id);
                $stmt->execute();

                // Update customers table
                $sql = "UPDATE customers SET status = ? WHERE customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $status, $customer_id);
                $stmt->execute();

                // Check if vehicle already exists
                $check_vehicle_sql = "SELECT * FROM vehicles WHERE customer_id = ?";
                $check_stmt = $conn->prepare($check_vehicle_sql);
                $check_stmt->bind_param("i", $customer_id);
                $check_stmt->execute();
                $existing_vehicle = $check_stmt->get_result()->fetch_assoc();

                if ($existing_vehicle) {
                    // Update existing vehicle
                    $vehicle_sql = "UPDATE vehicles SET make = ?, model = ?, year = ?, license_plate = ? 
                            WHERE customer_id = ?";
                    $vehicle_stmt = $conn->prepare($vehicle_sql);
                    $vehicle_stmt->bind_param("ssssi",
                        $_POST['vehicle_make'],
                        $_POST['vehicle_model'],
                        $_POST['vehicle_year'],
                        $_POST['vehicle_license_plate'],
                        $customer_id
                    );
                } else {
                    // Insert new vehicle
                    $vehicle_sql = "INSERT INTO vehicles (customer_id, make, model, year, license_plate) 
                            VALUES (?, ?, ?, ?, ?)";
                    $vehicle_stmt = $conn->prepare($vehicle_sql);
                    $vehicle_stmt->bind_param("issss",
                        $customer_id,
                        $_POST['vehicle_make'],
                        $_POST['vehicle_model'],
                        $_POST['vehicle_year'],
                        $_POST['vehicle_license_plate']
                    );
                }
                $vehicle_stmt->execute();

                $conn->commit();
                handleResponse(true, "Customer updated successfully");
            } catch (Exception $e) {
                $conn->rollback();
                handleResponse(false, "Error updating customer: " . $e->getMessage());
            }
            break;

        case 'delete':
            $customer_id = intval($_POST['customer_id']);

            $conn->begin_transaction();

            try {
                // Get user_id first
                $sql = "SELECT user_id FROM customers WHERE customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_id = $result->fetch_assoc()['user_id'];

                // Delete from customers table
                $sql = "DELETE FROM customers WHERE customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();

                // Delete from users table
                $sql = "DELETE FROM users WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                $conn->commit();
                handleResponse(true, "Customer deleted successfully");
            } catch (Exception $e) {
                $conn->rollback();
                handleResponse(false, "Error deleting customer: " . $e->getMessage());
            }
            break;

        default:
            handleResponse(false, "Invalid action");
    }
}

function logError($message) {
    error_log($message, 3, 'customer_actions_error.log');
}

$conn->close();
?>