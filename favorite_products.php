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

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing user_id parameter"));
    exit;
}

$user_id = intval($_GET['user_id']);

try {
    // Query to get user's favorite products with product details
    $query = "SELECT p.* FROM products p 
              INNER JOIN favorites f ON p.id = f.product_id 
              WHERE f.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $products = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[] = $row;
    }
    
    // Return favorite products with details
    http_response_code(200);
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>