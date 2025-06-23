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

// Check if product_id is provided
if (!isset($_GET['product_id'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing product_id parameter"));
    exit;
}

$product_id = intval($_GET['product_id']);

try {
    // Check product stock status
    $query = "SELECT id, name, in_stock, stock_quantity FROM products WHERE id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Product not found"));
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>