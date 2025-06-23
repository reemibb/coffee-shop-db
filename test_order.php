<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database
include_once 'database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Test order ID
$order_id = 2; // Change this to the order ID you're trying to fetch

echo "<h2>Testing Database Connection and Queries</h2>";

try {
    // Test basic connection
    echo "<p>Database connection established.</p>";
    
    // Test order query
    $order_query = "SELECT * FROM orders WHERE id = :order_id";
    $order_stmt = $db->prepare($order_query);
    $order_stmt->bindParam(':order_id', $order_id);
    $order_stmt->execute();
    
    echo "<p>Order query executed.</p>";
    
    if ($order_stmt->rowCount() == 0) {
        echo "<p>No order found with ID: $order_id</p>";
    } else {
        $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Order found:</p>";
        echo "<pre>" . print_r($order, true) . "</pre>";
        
        // Test items query
        $items_query = "SELECT oi.*, p.name, p.description, p.image_url AS imageUrl 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id";
        $items_stmt = $db->prepare($items_query);
        $items_stmt->bindParam(':order_id', $order_id);
        $items_stmt->execute();
        
        echo "<p>Items query executed.</p>";
        
        $items = array();
        while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $item;
        }
        
        echo "<p>Found " . count($items) . " items for this order:</p>";
        echo "<pre>" . print_r($items, true) . "</pre>";
    }
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>General error: " . $e->getMessage() . "</p>";
}
?>