<?php
require '../db/database.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function handleResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get equipment details
function getEquipmentDetails($conn, $equipment_id) {
    $sql = "SELECT * FROM equipment WHERE equipment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch($action) {
        case 'view':
            $equipment_id = intval($_POST['equipment_id']);
            $equipment = getEquipmentDetails($conn, $equipment_id);

            if ($equipment) {
                handleResponse(true, "Equipment details retrieved", $equipment);
            } else {
                handleResponse(false, "Equipment not found");
            }
            break;

        case 'add':
            $name = sanitizeInput($_POST['name']);
            $type = sanitizeInput($_POST['type']);
            $status = sanitizeInput($_POST['status']);
            $last_maintenance_date = sanitizeInput($_POST['last_maintenance_date'] ?? null);
            $next_maintenance_date = sanitizeInput($_POST['next_maintenance_date'] ?? null);

            try {
                $sql = "INSERT INTO equipment (name, type, status, last_maintenance_date, next_maintenance_date) 
                VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $name, $type, $status, $last_maintenance_date, $next_maintenance_date);
                $stmt->execute();

                handleResponse(true, "Equipment added successfully");
            } catch (Exception $e) {
                handleResponse(false, "Error adding equipment: " . $e->getMessage());
            }
            break;

        case 'edit':
            $equipment_id = intval($_POST['equipment_id']);
            $name = sanitizeInput($_POST['name']);
            $type = sanitizeInput($_POST['type']);
            $status = sanitizeInput($_POST['status']);
            $last_maintenance_date = sanitizeInput($_POST['last_maintenance_date'] ?? null);
            $next_maintenance_date = sanitizeInput($_POST['next_maintenance_date'] ?? null);

            try {
                $sql = "UPDATE equipment 
                SET name = ?, type = ?, status = ?, 
                    last_maintenance_date = ?, next_maintenance_date = ?
                WHERE equipment_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $name, $type, $status,
                    $last_maintenance_date, $next_maintenance_date,
                    $equipment_id);
                $stmt->execute();

                handleResponse(true, "Equipment updated successfully");
            } catch (Exception $e) {
                handleResponse(false, "Error updating equipment: " . $e->getMessage());
            }
            break;

        case 'delete':
            $equipment_id = intval($_POST['equipment_id']);

            try {
                $sql = "DELETE FROM equipment WHERE equipment_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $equipment_id);
                $stmt->execute();

                handleResponse(true, "Equipment deleted successfully");
            } catch (Exception $e) {
                handleResponse(false, "Error deleting equipment: " . $e->getMessage());
            }
            break;

        default:
            handleResponse(false, "Invalid action");
    }
}

$conn->close();
?>