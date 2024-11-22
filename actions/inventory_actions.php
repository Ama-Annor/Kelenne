<?php
// Include database connection
require_once '../db/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header to return JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Function to validate input
function validateInput($data) {
    $errors = [];

    if (empty($data['name'])) {
        $errors[] = "Item name is required";
    }

    if (empty($data['category'])) {
        $errors[] = "Category is required";
    }

    if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0) {
        $errors[] = "Valid quantity is required";
    }

    if (!isset($data['unit_price']) || !is_numeric($data['unit_price']) || $data['unit_price'] < 0) {
        $errors[] = "Valid unit price is required";
    }

    if (empty($data['supplier'])) {
        $errors[] = "Supplier is required";
    }

    if (empty($data['status'])) {
        $errors[] = "Status is required";
    }

    return $errors;
}

try {
    // Check if action is set
    if (!isset($_POST['action'])) {
        throw new Exception('Action is required');
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            // Validate input
            $errors = validateInput($_POST);
            if (!empty($errors)) {
                throw new Exception(implode(", ", $errors));
            }

            // Prepare insert statement
            $stmt = $conn->prepare("INSERT INTO inventory_items (name, category, quantity, unit_price, supplier, status) VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("ssidss",
                $_POST['name'],
                $_POST['category'],
                $_POST['quantity'],
                $_POST['unit_price'],
                $_POST['supplier'],
                $_POST['status']
            );

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Item added successfully';
                $response['data'] = ['item_id' => $conn->insert_id];
            } else {
                throw new Exception('Failed to add item');
            }
            break;

        case 'edit':
            // Check if item_id is provided
            if (!isset($_POST['item_id'])) {
                throw new Exception('Item ID is required');
            }

            // Validate input
            $errors = validateInput($_POST);
            if (!empty($errors)) {
                throw new Exception(implode(", ", $errors));
            }

            // Prepare update statement
            $stmt = $conn->prepare("UPDATE inventory_items SET name=?, category=?, quantity=?, unit_price=?, supplier=?, status=? WHERE item_id=?");

            $stmt->bind_param("ssidssi",
                $_POST['name'],
                $_POST['category'],
                $_POST['quantity'],
                $_POST['unit_price'],
                $_POST['supplier'],
                $_POST['status'],
                $_POST['item_id']
            );

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Item updated successfully';
            } else {
                throw new Exception('Failed to update item');
            }
            break;

        case 'delete':
            // Check if item_id is provided
            if (!isset($_POST['item_id'])) {
                throw new Exception('Item ID is required');
            }

            // Prepare delete statement
            $stmt = $conn->prepare("DELETE FROM inventory_items WHERE item_id = ?");
            $stmt->bind_param("i", $_POST['item_id']);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Item deleted successfully';
            } else {
                throw new Exception('Failed to delete item');
            }
            break;

        case 'view':
            // Check if item_id is provided
            if (!isset($_POST['item_id'])) {
                throw new Exception('Item ID is required');
            }

            // Prepare select statement
            $stmt = $conn->prepare("SELECT * FROM inventory_items WHERE item_id = ?");
            $stmt->bind_param("i", $_POST['item_id']);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();

                if ($item) {
                    $response['success'] = true;
                    $response['data'] = $item;
                } else {
                    throw new Exception('Item not found');
                }
            } else {
                throw new Exception('Failed to fetch item details');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }

    // Return JSON response
    echo json_encode($response);
    exit;
}
?>