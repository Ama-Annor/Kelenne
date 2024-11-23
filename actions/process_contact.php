<?php
include '../db/database.php';

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check connection
    if ($conn->connect_error) {
        $response = array(
            "status" => "error",
            "message" => "Sorry, there was an error connecting to the database."
        );
        echo json_encode($response);
        error_log("Connection failed: " . $conn->connect_error);
        exit;
    }

    try {
        // Sanitize form inputs
        $full_name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $subject = sanitize_input($_POST['subject']);
        $message = sanitize_input($_POST['message']);

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO contact_us (full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param("sssss", $full_name, $email, $phone, $subject, $message);

        // Execute the statement
        if ($stmt->execute()) {
            // Send success response
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

    } catch(Exception $e) {
        // Send error response
        $response = array(
            "status" => "error",
            "message" => "Sorry, there was an error submitting your message. Please try again later."
        );

        // Log the error
        error_log("Error: " . $e->getMessage());
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
    exit;
}
?>