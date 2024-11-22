<?php
// Include the database connection file
require '../db/database.php'; // Adjust the path based on your project structure

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Fetch and sanitize input values
        $firstName = htmlspecialchars(trim($_POST['firstName']));
        $lastName = htmlspecialchars(trim($_POST['lastName']));
        $email = htmlspecialchars(trim($_POST['email']));
        $phone = htmlspecialchars(trim($_POST['phone']));
        $carMake = htmlspecialchars(trim($_POST['carMake']));
        $carModel = htmlspecialchars(trim($_POST['carModel']));
        $carYear = (int)$_POST['carYear'];
        $licensePlate = htmlspecialchars(trim($_POST['licensePlate']));

        // Begin a transaction
        $conn->begin_transaction();

        // Update user details
        $user_sql = "UPDATE users SET fname = ?, lname = ?, email = ?, phone = ? WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $user_id);
        $user_stmt->execute();

        // Get the customer ID for the current user
        $customer_sql = "SELECT customer_id FROM customers WHERE user_id = ?";
        $customer_stmt = $conn->prepare($customer_sql);
        $customer_stmt->bind_param("i", $user_id);
        $customer_stmt->execute();
        $customer_result = $customer_stmt->get_result();
        $customer = $customer_result->fetch_assoc();

        if (!$customer) {
            throw new Exception("Customer details not found.");
        }

        $customer_id = $customer['customer_id'];

        // Update vehicle details
        $vehicle_sql = "UPDATE vehicles SET make = ?, model = ?, year = ?, license_plate = ? WHERE customer_id = ?";
        $vehicle_stmt = $conn->prepare($vehicle_sql);
        $vehicle_stmt->bind_param("ssisi", $carMake, $carModel, $carYear, $licensePlate, $customer_id);
        $vehicle_stmt->execute();

        // Commit the transaction
        $conn->commit();


    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        // Close the database connection
        $conn->close();
        header("Location: ../view/profile.php");
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
