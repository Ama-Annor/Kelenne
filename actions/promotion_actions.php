<?php
require_once '../db/database.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

switch ($action) {
    case 'add_promotion':
        $name = sanitizeInput($_POST['promotionName']);
        $type = sanitizeInput($_POST['promotionType']);
        $discount = floatval($_POST['discountValue']);
        $valid_until = sanitizeInput($_POST['validUntil']);
        $usage_count = intval($_POST['usageCount'] ?? 0);

        $sql = "INSERT INTO promotions (name, type, discount_percentage, valid_until, is_active, usage_count) 
                VALUES (?, ?, ?, ?, 1, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsi", $name, $type, $discount, $valid_until, $usage_count);

        $response = [];
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Promotion added successfully',
                'promotion_id' => $stmt->insert_id
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Error adding promotion: ' . $stmt->error
            ];
        }
        echo json_encode($response);
        break;

    case 'edit_promotion':
        $id = intval($_POST['id']);
        $name = sanitizeInput($_POST['promotionName']);
        $type = sanitizeInput($_POST['promotionType']);
        $discount = floatval($_POST['discountValue']);
        $valid_until = sanitizeInput($_POST['validUntil']);
        $usage_count = intval($_POST['usageCount'] ?? 0);
        $is_active = intval($_POST['isActive'] ?? 1);

        $sql = "UPDATE promotions SET 
                name = ?, 
                type = ?, 
                discount_percentage = ?, 
                valid_until = ?,
                usage_count = ?,
                is_active = ? 
                WHERE promotion_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsiii", $name, $type, $discount, $valid_until, $usage_count, $is_active, $id);

        $response = [];
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Promotion updated successfully'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Error updating promotion: ' . $stmt->error
            ];
        }
        echo json_encode($response);
        break;

    case 'delete_promotion':
        $id = intval($_POST['id']);

        $sql = "DELETE FROM promotions WHERE promotion_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        $response = [];
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Promotion deleted successfully'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Error deleting promotion: ' . $stmt->error
            ];
        }
        echo json_encode($response);
        break;

    case 'view_promotion':
        $id = intval($_POST['id']);

        $sql = "SELECT * FROM promotions WHERE promotion_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $response = [];
        if ($row = $result->fetch_assoc()) {
            $response = [
                'success' => true,
                'promotion' => $row
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Promotion not found'
            ];
        }
        echo json_encode($response);
        break;
}

$conn->close();
?>