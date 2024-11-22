<?php
require '../db/database.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Validate input
if (!isset($_POST['serviceName'])) {
    echo json_encode(['success' => false, 'message' => 'Missing service name']);
    exit();
}

// Fetch customer details
$user_id = $_SESSION['user_id'];
$customer_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$customer_stmt->bind_param("i", $user_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit();
}

// Prepare the service insertion query
$stmt = $conn->prepare("INSERT INTO services (name, description, is_active, customer_id) VALUES (?, ?, ?, ?)");

// Define service details
$name = $_POST['serviceName'];
$description = $name . " service booked by customer";
$is_active = 1;
$customer_id = $customer['customer_id'];

// Bind parameters
$stmt->bind_param("ssii", $name, $description, $is_active, $customer_id);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>