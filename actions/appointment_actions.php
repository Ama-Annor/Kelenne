<?php
require '../db/database.php';
session_start();

header('Content-Type: application/json');
$response = ["success" => false, "message" => ""];

// Function to validate appointment date
function validateAppointmentDate($date) {
    $appointmentDate = new DateTime($date);
    $now = new DateTime();
    return $appointmentDate > $now;
}

// Function to check if email exists and get customer_id
function getCustomerId($conn, $email) {
    $stmt = $conn->prepare("SELECT c.customer_id FROM customers c 
                           INNER JOIN users u ON c.user_id = u.user_id 
                           WHERE u.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['customer_id'];
    }
    return null;
}

// Handle GET requests (fetching appointment details)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT a.*, u.email, s.service_id 
                               FROM appointments a
                               INNER JOIN customers c ON a.customer_id = c.customer_id
                               INNER JOIN users u ON c.user_id = u.user_id
                               INNER JOIN services s ON a.service_id = s.service_id
                               WHERE a.appointment_id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
            $response["success"] = true;
            $response["appointment"] = $appointment;
        } else {
            $response["message"] = "Appointment not found";
        }
    }
    echo json_encode($response);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            // Validate input
            if (!isset($_POST['customer_email']) || !isset($_POST['service_id']) || !isset($_POST['appointment_date'])) {
                $response["message"] = "Missing required fields";
                break;
            }

            // Validate appointment date
            if (!validateAppointmentDate($_POST['appointment_date'])) {
                $response["message"] = "Invalid appointment date";
                break;
            }

            // Get customer_id from email
            $customer_id = getCustomerId($conn, $_POST['customer_email']);
            if (!$customer_id) {
                $response["message"] = "Customer not found";
                break;
            }

            // Insert new appointment
            $stmt = $conn->prepare("INSERT INTO appointments (customer_id, service_id, appointment_date, status) 
                                  VALUES (?, ?, ?, 'scheduled')");
            $stmt->bind_param("iis", $customer_id, $_POST['service_id'], $_POST['appointment_date']);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Appointment created successfully";
            } else {
                $response["message"] = "Error creating appointment: " . $conn->error;
            }
            break;

        case 'edit':
            if (!isset($_POST['appointment_id'])) {
                $response["message"] = "Appointment ID required";
                break;
            }

            // Validate appointment date
            if (!validateAppointmentDate($_POST['appointment_date'])) {
                $response["message"] = "Invalid appointment date";
                break;
            }

            // Update appointment
            $stmt = $conn->prepare("UPDATE appointments 
                                  SET service_id = ?, appointment_date = ?, status = ? 
                                  WHERE appointment_id = ?");
            $stmt->bind_param("issi",
                $_POST['service_id'],
                $_POST['appointment_date'],
                $_POST['status'],
                $_POST['appointment_id']
            );

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Appointment updated successfully";
            } else {
                $response["message"] = "Error updating appointment: " . $conn->error;
            }
            break;

        case 'delete':
            if (!isset($_POST['appointment_id'])) {
                $response["message"] = "Appointment ID required";
                break;
            }

            // Check if appointment exists before deleting
            $check_stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ?");
            $check_stmt->bind_param("i", $_POST['appointment_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                $response["message"] = "Appointment not found";
                break;
            }

            // Delete appointment
            $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
            $stmt->bind_param("i", $_POST['appointment_id']);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Appointment deleted successfully";
            } else {
                $response["message"] = "Error deleting appointment: " . $conn->error;
            }
            break;

        default:
            $response["message"] = "Invalid action";
            break;
    }

    // Store response in session for displaying messages after redirect
    $_SESSION['response'] = $response;

    // Send JSON response
    echo json_encode($response);
}

// Close database connection
$conn->close();