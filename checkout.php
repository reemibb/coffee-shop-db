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
if (!isset($data->user_id) || !isset($data->items) || empty($data->items)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required data"));
    exit;
}

try {
    // Start transaction
    $db->beginTransaction();
    
    // Check if all products are in stock
    $insufficient_stock = array();
    
    foreach ($data->items as $item) {
        // Check if product exists and has enough stock
        $stock_query = "SELECT id, name, in_stock, stock_quantity FROM products WHERE id = :product_id";
        $stock_stmt = $db->prepare($stock_query);
        $stock_stmt->bindParam(':product_id', $item->id);
        $stock_stmt->execute();
        
        if ($stock_stmt->rowCount() > 0) {
            $product = $stock_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if product is in stock
            if (!$product['in_stock']) {
                $insufficient_stock[] = array(
                    "id" => $product['id'],
                    "name" => $product['name'],
                    "message" => "Product is out of stock"
                );
                continue;
            }
            
            // Check if enough quantity is available
            if ($product['stock_quantity'] !== null && $product['stock_quantity'] < $item->quantity) {
                $insufficient_stock[] = array(
                    "id" => $product['id'],
                    "name" => $product['name'],
                    "message" => "Not enough stock",
                    "available" => $product['stock_quantity']
                );
            }
        } else {
            $insufficient_stock[] = array(
                "id" => $item->id,
                "message" => "Product not found"
            );
        }
    }
    
    // If any product is out of stock, return error
    if (!empty($insufficient_stock)) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(array(
            "message" => "Some products are unavailable",
            "unavailable_items" => $insufficient_stock
        ));
        exit;
    }
    
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
    
    // Insert order items and update stock
    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, options) 
                    VALUES (:order_id, :product_id, :quantity, :price, :options)";
    $item_stmt = $db->prepare($item_query);
    
    // Prepare stock update query
    $stock_update_query = "UPDATE products SET stock_quantity = stock_quantity - :quantity 
                           WHERE id = :product_id AND stock_quantity IS NOT NULL";
    $stock_update_stmt = $db->prepare($stock_update_query);
    
    foreach ($data->items as $item) {
        // Convert options object to JSON string if it exists
        $options_json = isset($item->options) ? json_encode($item->options) : null;
        
        $item_stmt->bindParam(':order_id', $order_id);
        $item_stmt->bindParam(':product_id', $item->id);
        $item_stmt->bindParam(':quantity', $item->quantity);
        $item_stmt->bindParam(':price', $item->price);
        $item_stmt->bindParam(':options', $options_json);
        $item_stmt->execute();
        
        // Update stock quantity if not null
        $stock_update_stmt->bindParam(':quantity', $item->quantity);
        $stock_update_stmt->bindParam(':product_id', $item->id);
        $stock_update_stmt->execute();
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
?>