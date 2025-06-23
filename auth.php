<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Just exit with 200 OK status
    http_response_code(200);
    exit;
}
include_once 'database.php';
include_once 'user.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is set
if(!empty($data)) {
    // Check the action
    switch($data->action) {
        case 'login':
            // Set properties
            $user->email = $data->email;
            $user->password = $data->password;

            // Login
            $result = $user->login();

            if($result["success"]) {
                // Generate JWT token
                $token = bin2hex(random_bytes(32));
                $user->token = $token;
                $user->saveToken();

                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Login successful",
                    "token" => $token,
                    "user" => array(
                        "id" => $result["id"],
                        "name" => $result["name"],
                        "email" => $result["email"]
                    )
                ));
            } else {
                http_response_code(401);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Invalid email or password"
                ));
            }
            break;

        case 'register':
            // Set properties
            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = password_hash($data->password, PASSWORD_DEFAULT);

            // Check if email already exists
            if($user->emailExists()) {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Email already exists"
                ));
                return;
            }

            // Register
            if($user->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "User registered successfully"
                ));
            } else {
                // Log the error
                error_log("User registration failed: " . print_r($db->errorInfo(), true));
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Unable to register user"
                ));
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid action"
            ));
            break;
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Missing required data"
    ));
}
?>