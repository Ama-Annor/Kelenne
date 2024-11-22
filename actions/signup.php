<?php
require '../db/database.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize response array
$response = ["success" => false, "message" => ""];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and sanitize form data
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $secretKey = trim($_POST['secretKey']);

    // Car details
    $carMake = trim($_POST['carMake']);
    $carModel = trim($_POST['carModel']);
    $carYear = trim($_POST['carYear']);
    $licensePlate = trim($_POST['licensePlate']);

    // Server-side validation
    if (empty($fname) || empty($lname) || empty($email) || empty($phoneNumber) ||
        empty($password) || empty($confirmPassword)) {
        $response['message'] = "All fields are required.";
    }
    elseif (!preg_match("/^[a-zA-Z-' ]{2,30}$/", $fname)) {
        $response['message'] = "Please enter a valid first name.";
    }
    elseif (!preg_match("/^[a-zA-Z-' ]{2,30}$/", $lname)) {
        $response['message'] = "Please enter a valid last name.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please enter a valid email address.";
    }
    elseif (!preg_match("/^\d{10}$/", preg_replace("/\D/", "", $phoneNumber))) {
        $response['message'] = "Please enter a valid 10-digit phone number.";
    }
    elseif (strlen($password) < 8) {
        $response['message'] = "Password must be at least 8 characters long.";
    }
    elseif ($password !== $confirmPassword) {
        $response['message'] = "Passwords do not match.";
    }
    else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $response['message'] = "Email is already registered.";
            } else {
                // Determine user type based on secret key
                $userType = ($secretKey === 'Wazaah') ? 'employee' : 'customer';

                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Start transaction
                $conn->begin_transaction();

                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, phone, password, user_type, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssss", $fname, $lname, $email, $phoneNumber, $hashedPassword, $userType);

                if ($stmt->execute()) {
                    $userId = $conn->insert_id;

                    // Insert into appropriate table based on user type
                    if ($userType === 'customer') {
                        // Create customer profile
                        $stmt = $conn->prepare("INSERT INTO customer_profiles (user_id) VALUES (?)");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();

                        // Insert into customers table
                        $stmt = $conn->prepare("INSERT INTO customers (user_id) VALUES (?)");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();

                        // Insert vehicle details
                        $stmt = $conn->prepare("INSERT INTO vehicles (customer_id, make, model, year, license_plate) 
                                              VALUES ((SELECT customer_id FROM customers WHERE user_id = ?), ?, ?, ?, ?)");
                        $stmt->bind_param("issss", $userId, $carMake, $carModel, $carYear, $licensePlate);
                        $stmt->execute();
                    } elseif ($userType === 'employee') {
                        // Insert into employees table
                        $stmt = $conn->prepare("INSERT INTO employees (user_id) VALUES (?)");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                    }

                    // Commit transaction
                    $conn->commit();

                    // Set session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['first_name'] = $fname;
                    $_SESSION['user_type'] = $userType;

                    $response['success'] = true;
                    $response['message'] = "Registration successful!";
                    $response['redirect'] = "../view/login.html";
                } else {
                    throw new Exception("Failed to create user account");
                }
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $response['message'] = "Registration failed. Please try again later.";
            error_log("Registration error: " . $e->getMessage());
        }
    }

    // Return JSON response for AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    // Handle regular form submission
    else {
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
            header("Location: " . $response['redirect']);
        } else {
            $_SESSION['error_message'] = $response['message'];
            header("Location: ../view/signup.html");
        }
        exit();
    }
}
?>