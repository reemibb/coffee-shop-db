<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Check if user ID is provided
        if (isset($_GET['user_id'])) {
            getUserRatings($db, $_GET['user_id']);
        } else {
            // Get all ratings
            getAllRatings($db);
        }
        break;
    
    case 'POST':
        // Create new rating
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->user_id) || !isset($data->rating)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required data"));
            exit;
        }
        
        createRating($db, $data);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

// Function to create a new rating
function createRating($db, $data) {
    try {
        // Check if user has already rated
        $check_query = "SELECT id FROM ratings WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':user_id', $data->user_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing rating
            $rating = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            $update_query = "UPDATE ratings 
                             SET rating = :rating, comment = :comment 
                             WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            
            // Sanitize and bind parameters
            $comment = isset($data->comment) ? $data->comment : null;
            
            $update_stmt->bindParam(':rating', $data->rating);
            $update_stmt->bindParam(':comment', $comment);
            $update_stmt->bindParam(':id', $rating['id']);
            
            if ($update_stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Rating updated successfully"));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Unable to update rating"));
            }
        } else {
            // Create new rating
            $insert_query = "INSERT INTO ratings (user_id, rating, comment) 
                             VALUES (:user_id, :rating, :comment)";
            $insert_stmt = $db->prepare($insert_query);
            
            // Sanitize and bind parameters
            $comment = isset($data->comment) ? $data->comment : null;
            
            $insert_stmt->bindParam(':user_id', $data->user_id);
            $insert_stmt->bindParam(':rating', $data->rating);
            $insert_stmt->bindParam(':comment', $comment);
            
            if ($insert_stmt->execute()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Rating created successfully",
                    "id" => $db->lastInsertId()
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Unable to create rating"));
            }
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
}

// Function to get ratings for a specific user
function getUserRatings($db, $user_id) {
    try {
        $query = "SELECT * FROM ratings WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $rating = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($rating);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No rating found for this user"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
}

// Function to get all ratings
function getAllRatings($db) {
    try {
        $query = "SELECT r.*, u.name as user_name 
                  FROM ratings r
                  JOIN users u ON r.user_id = u.id
                  ORDER BY r.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $ratings = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ratings[] = $row;
        }
        
        http_response_code(200);
        echo json_encode($ratings);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
}
?>