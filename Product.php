<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and object files
require_once 'database.php';

// Instantiate database
$database = new Database();
$db = $database->getConnection();

try {
    // Check if connection succeeded
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    // Get ID from query string
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        // Set response code - 400 Bad request
        http_response_code(400);
        
        // Tell the user
        echo json_encode(array("message" => "Missing or invalid product ID"));
        exit;
    }
    
    // Prepare query
    $query = "SELECT id, name, description, price, image_url as imageUrl, category 
              FROM products 
              WHERE id = :id";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    // Bind parameter
    $stmt->bindParam(":id", $id);
    
    // Execute query
    $stmt->execute();
    
    // Get row count
    $num = $stmt->rowCount();
    
    // Check if product exists
    if ($num > 0) {
        // Get product data
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Format price as integer
        $row['price'] = (int)$row['price'];
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Show product data in json format
        echo json_encode($row);
    } else {
        // Set response code - 404 Not found
        http_response_code(404);
        
        // Tell the user product does not exist
        echo json_encode(array("message" => "Product not found"));
    }
} catch(Exception $e) {
    // Set response code - 500 Server Error
    http_response_code(500);
    
    // Tell the user
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>