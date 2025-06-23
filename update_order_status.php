<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include database
include_once 'database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is set
if (!isset($data->order_id) || !isset($data->status)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required data"));
    exit;
}

try {
    // Update order status
    $query = "UPDATE orders SET status = :status WHERE id = :order_id";
    $stmt = $db->prepare($query);
    
    // Valid status values
    $valid_statuses = array('pending', 'processing', 'shipped', 'delivered', 'cancelled');
    
    if (!in_array($data->status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid status value"));
        exit;
    }
    
    $stmt->bindParam(':status', $data->status);
    $stmt->bindParam(':order_id', $data->order_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Order status updated successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to update order status"));
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>