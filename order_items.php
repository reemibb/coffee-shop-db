<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing order_id parameter"));
    exit;
}

$order_id = intval($_GET['order_id']);

try {
    // Get order items with product details
    $query = "SELECT oi.id, oi.product_id, oi.quantity, oi.price, oi.options,
                 p.name, p.description, p.image_url AS imageUrl
          FROM order_items oi
          LEFT JOIN products p ON oi.product_id = p.id
          WHERE oi.order_id = :order_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    $items = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Parse options JSON if it exists
        if ($row['options']) {
            $row['options'] = json_decode($row['options']);
        }
        $items[] = $row;
    }
    
    // Return order items
    http_response_code(200);
    echo json_encode($items);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>