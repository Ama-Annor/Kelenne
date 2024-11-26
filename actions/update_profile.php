<?php
require '../db/database.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'User is not logged in.';
    header('Location: ../view/profile.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Fetch and sanitize input values
        $firstName = htmlspecialchars(trim($_POST['firstName']));
        $lastName = htmlspecialchars(trim($_POST['lastName']));
        $email = htmlspecialchars(trim($_POST['email']));
        $phone = htmlspecialchars(trim($_POST['phone']));

        // Begin a transaction
        $conn->begin_transaction();

        // Update user details
        $user_sql = "UPDATE users SET fname = ?, lname = ?, email = ?, phone = ? WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $user_id);
        $user_stmt->execute();

        // Check if there are vehicle-related fields in the POST data
        if (isset($_POST['carMake'], $_POST['carModel'], $_POST['carYear'], $_POST['licensePlate'])) {
            $carMake = htmlspecialchars(trim($_POST['carMake']));
            $carModel = htmlspecialchars(trim($_POST['carModel']));
            $carYear = (int)$_POST['carYear'];
            $licensePlate = htmlspecialchars(trim($_POST['licensePlate']));

            // Get the customer ID for the current user
            $customer_sql = "SELECT customer_id FROM customers WHERE user_id = ?";
            $customer_stmt = $conn->prepare($customer_sql);
            $customer_stmt->bind_param("i", $user_id);
            $customer_stmt->execute();
            $customer_result = $customer_stmt->get_result();
            $customer = $customer_result->fetch_assoc();

            if ($customer) {
                $customer_id = $customer['customer_id'];

                // Update vehicle details
                $vehicle_sql = "UPDATE vehicles SET make = ?, model = ?, year = ?, license_plate = ? WHERE customer_id = ?";
                $vehicle_stmt = $conn->prepare($vehicle_sql);
                $vehicle_stmt->bind_param("ssisi", $carMake, $carModel, $carYear, $licensePlate, $customer_id);
                $vehicle_stmt->execute();
            }
        }

        // Commit the transaction
        $conn->commit();

        // Set success message
        $_SESSION['success_message'] = 'Profile updated successfully!';
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();

        // Set error message
        $_SESSION['error_message'] = 'Error updating profile: ' . $e->getMessage();
    } finally {
        // Close the database connection
        $conn->close();

        // Redirect back to profile page
        header('Location: ../view/profile.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: ../view/profile.php');
    exit();
}
?>