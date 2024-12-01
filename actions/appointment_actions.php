<?php
require '../db/database.php';
session_start();

header('Content-Type: application/json');
$response = ["success" => false, "message" => ""];

function validateAppointmentDate($date) {
    $appointmentDate = new DateTime($date);
    $now = new DateTime();
    return $appointmentDate > $now;
}

function getCustomerId($conn, $userId) {
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['customer_id'];
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            // Validate appointment date
            if (!validateAppointmentDate($_POST['appointment_date'])) {
                $response["message"] = "Invalid appointment date";
                break;
            }

            // Get customer_id from current user
            $customer_id = getCustomerId($conn, $_SESSION['user_id']);
            if (!$customer_id) {
                $response["message"] = "Customer not found";
                break;
            }

            // Insert new appointment with default scheduled status
            $stmt = $conn->prepare("INSERT INTO appointments (customer_id, appointment_date, status) 
                                  VALUES (?, ?, 'Scheduled')");
            $stmt->bind_param("is", $customer_id, $_POST['appointment_date']);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Appointment created successfully";
            } else {
                $response["message"] = "Error creating appointment: " . $conn->error;
            }
            break;

        case 'delete':
            if (!isset($_POST['appointment_id'])) {
                $response["message"] = "Appointment ID required";
                break;
            }

            // Different logic for admin and customer
            if ($_SESSION['role'] === 'customer') {
                // For customers: check if appointment belongs to them and is not confirmed
                $check_stmt = $conn->prepare("SELECT a.appointment_id FROM appointments a 
                                      JOIN customers c ON a.customer_id = c.customer_id 
                                      WHERE a.appointment_id = ? 
                                      AND c.user_id = ? 
                                      AND a.status != 'Confirmed'");
                $check_stmt->bind_param("ii", $_POST['appointment_id'], $_SESSION['user_id']);
            } else if ($_SESSION['role'] === 'admin') {
                // For admin: allow deletion of any appointment
                $check_stmt = $conn->prepare("SELECT appointment_id FROM appointments 
                                      WHERE appointment_id = ?");
                $check_stmt->bind_param("i", $_POST['appointment_id']);
            } else {
                $response["message"] = "Unauthorized access";
                break;
            }

            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                $response["message"] = "Cannot delete this appointment";
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

        case 'update_status':
            // Ensure admin is updating the status
            if ($_SESSION['role'] !== 'admin') {
                $response["message"] = "Unauthorized access";
                break;
            }

            if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
                $response["message"] = "Missing appointment ID or status";
                break;
            }

            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->bind_param("si", $_POST['status'], $_POST['appointment_id']);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Appointment status updated successfully";
            } else {
                $response["message"] = "Error updating appointment status: " . $conn->error;
            }
            break;
    }

    $_SESSION['response'] = $response;
    echo json_encode($response);
}

$conn->close();