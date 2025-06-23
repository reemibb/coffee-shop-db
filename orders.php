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

try {
    switch ($method) {
        case 'GET':
            // Get order details or list orders
            if (isset($_GET['id'])) {
                // Get specific order
                getOrderById($db, $_GET['id']);
            } elseif (isset($_GET['user_id'])) {
                // Get orders for a specific user
                getOrdersByUser($db, $_GET['user_id']);
            } else {
                // Invalid request
                http_response_code(400);
                echo json_encode(array("message" => "Missing required parameters"));
            }
            break;
        
        case 'POST':
            // Create new order
            $data = json_decode(file_get_contents("php://input"));
            
            if (!isset($data->user_id) || !isset($data->items) || empty($data->items)) {
                http_response_code(400);
                echo json_encode(array("message" => "Missing required data"));
                exit;
            }
            
            createOrder($db, $data);
            break;
        
        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed"));
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Server error: " . $e->getMessage()));
}

// Function to create a new order
function createOrder($db, $data) {
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Calculate order total
        $total = 0;
        foreach ($data->items as $item) {
            $total += $item->price * $item->quantity;
        }
        
        // Insert order into orders table
        $order_query = "INSERT INTO orders (user_id, total, status, shipping_address, payment_method, created_at) 
                         VALUES (:user_id, :total, :status, :shipping_address, :payment_method, NOW())";
        $order_stmt = $db->prepare($order_query);
        
        // Bind parameters
        $status = isset($data->status) ? $data->status : "pending";
        $shipping_address = isset($data->shipping_address) ? json_encode($data->shipping_address) : null;
        $payment_method = isset($data->payment_method) ? $data->payment_method : "cash";
        
        $order_stmt->bindParam(':user_id', $data->user_id);
        $order_stmt->bindParam(':total', $total);
        $order_stmt->bindParam(':status', $status);
        $order_stmt->bindParam(':shipping_address', $shipping_address);
        $order_stmt->bindParam(':payment_method', $payment_method);
        $order_stmt->execute();
        
        // Get the order ID
        $order_id = $db->lastInsertId();
        
        // Insert order items
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, options) 
                        VALUES (:order_id, :product_id, :quantity, :price, :options)";
        $item_stmt = $db->prepare($item_query);
        
        foreach ($data->items as $item) {
            // Convert options object to JSON string if it exists
            $options_json = isset($item->options) ? json_encode($item->options) : null;
            
            $item_stmt->bindParam(':order_id', $order_id);
            $item_stmt->bindParam(':product_id', $item->id);
            $item_stmt->bindParam(':quantity', $item->quantity);
            $item_stmt->bindParam(':price', $item->price);
            $item_stmt->bindParam(':options', $options_json);
            $item_stmt->execute();
        }
        
        // Commit transaction
        $db->commit();
        
        // Return success response
        http_response_code(201);
        echo json_encode(array(
            "message" => "Order created successfully",
            "order_id" => $order_id,
            "status" => $status,
            "total" => $total
        ));
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
}

// Function to get an order by ID
function getOrderById($db, $order_id) {
    try {
        // Get order details
        $order_query = "SELECT * FROM orders WHERE id = :order_id";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->bindParam(':order_id', $order_id);
        $order_stmt->execute();
        
        if ($order_stmt->rowCount() == 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Order not found"));
            return;
        }
        
        $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Parse shipping_address if it exists
        if ($order['shipping_address']) {
            $order['shipping_address'] = json_decode($order['shipping_address']);
        }
        
        // Get order items - Updated to use image_url
        $items_query = "SELECT oi.id, oi.product_id, oi.quantity, oi.price, oi.options,
                        p.name, p.description, p.image_url AS imageUrl
                        FROM order_items oi 
                        LEFT JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = :order_id";
                        
        $items_stmt = $db->prepare($items_query);
        $items_stmt->bindParam(':order_id', $order_id);
        $items_stmt->execute();
        
        $items = array();
        while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
            // Parse options JSON if it exists
            if ($item['options']) {
                $item['options'] = json_decode($item['options']);
            }
            $items[] = $item;
        }
        
        // Add items to order response
        $order['items'] = $items;
        
        http_response_code(200);
        echo json_encode($order);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

// Function to get orders by user
function getOrdersByUser($db, $user_id) {
    try {
        // Get orders for user
        $order_query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->bindParam(':user_id', $user_id);
        $order_stmt->execute();
        
        $orders = array();
        while ($order = $order_stmt->fetch(PDO::FETCH_ASSOC)) {
            // Parse shipping_address if it exists
            if ($order['shipping_address']) {
                $order['shipping_address'] = json_decode($order['shipping_address']);
            }
            $orders[] = $order;
        }
        
        http_response_code(200);
        echo json_encode($orders);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
}
?>