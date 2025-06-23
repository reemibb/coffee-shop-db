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

    // Prepare query
    $query = "SELECT id, name, description, price, image_url as imageUrl FROM products 
              WHERE category = 'dessert' AND is_featured = 1 
              ORDER BY id LIMIT 4";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    // Execute query
    $stmt->execute();
    
    // Get row count
    $num = $stmt->rowCount();
    
    // Check if any products found
    if ($num > 0) {
        // Products array
        $products_arr = array();
        
        // Retrieve all rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Format price as integer
            $row['price'] = (int)$row['price'];
            
            // Add to products array
            array_push($products_arr, $row);
        }
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Show products data in json format
        echo json_encode($products_arr);
    } else {
        // Set response code - 200 OK
        http_response_code(200);
        
        // Tell the user no products found
        echo json_encode(array());
    }
} catch(Exception $e) {
    // Set response code - 500 Server Error
    http_response_code(500);
    
    // Tell the user
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>