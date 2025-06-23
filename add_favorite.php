<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
if (!isset($data->user_id) || !isset($data->product_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required data"));
    exit;
}

try {
    // First check if the favorite already exists
    $check_query = "SELECT * FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':user_id', $data->user_id);
    $check_stmt->bindParam(':product_id', $data->product_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Favorite already exists
        http_response_code(200);
        echo json_encode(array("message" => "Product is already favorited"));
        exit;
    }
    
    // Insert favorite
    $insert_query = "INSERT INTO favorites (user_id, product_id) VALUES (:user_id, :product_id)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':user_id', $data->user_id);
    $insert_stmt->bindParam(':product_id', $data->product_id);
    
    if ($insert_stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Product added to favorites"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to add favorite"));
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>