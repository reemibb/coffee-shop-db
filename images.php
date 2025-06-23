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
    $query = "SELECT id, img_name, img_url FROM images";
    
    // If image name is provided, filter by it
    if (isset($_GET['name'])) {
        $query .= " WHERE img_name = :name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $_GET['name']);
    } else {
        $stmt = $db->prepare($query);
    }
    
    // Execute query
    $stmt->execute();
    
    // Get row count
    $num = $stmt->rowCount();
    
    // Check if any images found
    if ($num > 0) {
        // Images array
        $images_arr = array();
        
        // Retrieve all rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Add to images array
            array_push($images_arr, $row);
        }
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Show images data in json format
        echo json_encode($images_arr);
    } else {
        // Set response code - 200 OK
        http_response_code(200);
        
        // Tell the user no images found
        echo json_encode(array());
    }
} catch(Exception $e) {
    // Set response code - 500 Server Error
    http_response_code(500);
    
    // Tell the user
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>