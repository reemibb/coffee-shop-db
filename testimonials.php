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
    $query = "SELECT id, name, title, avatar, rating, comment FROM testimonials ORDER BY id";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    // Execute query
    $stmt->execute();
    
    // Get row count
    $num = $stmt->rowCount();
    
    // Check if any testimonials found
    if ($num > 0) {
        // Testimonials array
        $testimonials_arr = array();
        
        // Retrieve all rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Format rating as integer
            $row['rating'] = (int)$row['rating'];
            
            // Add to testimonials array
            array_push($testimonials_arr, $row);
        }
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Show testimonials data in json format
        echo json_encode($testimonials_arr);
    } else {
        // Set response code - 200 OK
        http_response_code(200);
        
        // Tell the user no testimonials found
        echo json_encode(array());
    }
} catch(Exception $e) {
    // Set response code - 500 Server Error
    http_response_code(500);
    
    // Tell the user
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>