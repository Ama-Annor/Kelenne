<?php
// Include the database connection file
require '../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input fields
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../view/login.html");
        exit;
    } else {
        // Check if the user exists in the database
        $stmt = $conn->prepare("SELECT user_id, fname, lname, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $firstName, $lastName, $hashedPassword, $user_type);

        if ($stmt->fetch()) {
            // Verify password
            if (password_verify($password, $hashedPassword)) {
                // Set session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['role'] = $user_type;

                // Redirect based on user role
                if ($user_type === "customer") {
                    header("Location: ../view/revenue-analytics.php");
                } else if ($user_type === "admin") {
                    header("Location: ../view/revenue-analytics.php");
                } else if ($user_type === "employee") {
                    header("Location: ../view/revenue-analytics.php");
                }
                exit;
            } else {
                $_SESSION['error_message'] = "Incorrect password.";
            }
        } else {
            $_SESSION['error_message'] = "User not found.";
        }

        $stmt->close();
    }

    // Redirect back to the login page
    header("Location: ../view/login.html");
    exit;
}
?>



