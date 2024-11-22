<?php
require '../db/database.php';
session_start();

header('Content-Type: application/json');

$response = ["success" => false, "message" => ""];

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $duration = floatval($_POST['duration'] ?? 0);
            $price = floatval($_POST['price'] ?? 0);
            $is_active = intval($_POST['is_active'] ?? 1);
            $customer_id = $_POST['customer_id'] ? intval($_POST['customer_id']) : null;

            // Input validation
            if (empty($name)) {
                throw new Exception("Service name is required");
            }
            if ($duration <= 0) {
                throw new Exception("Duration must be greater than zero");
            }
            if ($price < 0) {
                throw new Exception("Price cannot be negative");
            }

            $stmt = $conn->prepare("INSERT INTO services (name, description, duration, price, is_active, customer_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddii", $name, $description, $duration, $price, $is_active, $customer_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['service_id'] = $stmt->insert_id;
            } else {
                throw new Exception("Failed to add service: " . $stmt->error);
            }
            break;

        case 'view':
            $service_id = intval($_POST['service_id'] ?? 0);

            $stmt = $conn->prepare("
                SELECT s.*, 
                       c.customer_id,
                       u.fname, 
                       u.lname, 
                       u.email, 
                       u.phone,
                       c.status AS customer_status
                FROM services s
                LEFT JOIN customers c ON s.customer_id = c.customer_id 
                LEFT JOIN users u ON c.user_id = u.user_id
                WHERE s.service_id = ?
            ");
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $response['success'] = true;
                $response['data'] = $row;
            } else {
                throw new Exception("Service not found");
            }
            break;

        case 'get_customers':
            $stmt = $conn->prepare("
                SELECT customers.customer_id, 
                       users.fname, 
                       users.lname, 
                       users.email, 
                       users.phone, 
                       customers.status
                FROM customers
                INNER JOIN users ON customers.user_id = users.user_id
                ORDER BY users.lname, users.fname
            ");
            $stmt->execute();
            $result = $stmt->get_result();

            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }

            $response['success'] = true;
            $response['customers'] = $customers;
            break;

        case 'edit':
            $service_id = intval($_POST['service_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $duration = floatval($_POST['duration'] ?? 0);
            $price = floatval($_POST['price'] ?? 0);
            $is_active = intval($_POST['is_active'] ?? 1);

            // Input validation
            if (empty($name)) {
                throw new Exception("Service name is required");
            }
            if ($duration <= 0) {
                throw new Exception("Duration must be greater than zero");
            }
            if ($price < 0) {
                throw new Exception("Price cannot be negative");
            }

            $stmt = $conn->prepare("UPDATE services SET name=?, description=?, duration=?, price=?, is_active=? WHERE service_id=?");
            $stmt->bind_param("ssddii", $name, $description, $duration, $price, $is_active, $service_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Service updated successfully";
            } else {
                throw new Exception("Failed to update service: " . $stmt->error);
            }
            break;

        case 'delete':
            $service_id = intval($_POST['service_id'] ?? 0);

            $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
            $stmt->bind_param("i", $service_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Service deleted successfully";
            } else {
                throw new Exception("Failed to delete service: " . $stmt->error);
            }
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

$conn->close();
?>