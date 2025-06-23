<?php
header("Content-Type: text/html; charset=UTF-8");

// Include database file
require_once 'database.php';

// Instantiate database
$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #8c5e3b; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow: auto; max-height: 300px; }
        .table { border-collapse: collapse; width: 100%; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table tr:nth-child(even) { background-color: #f2f2f2; }
        .table th { padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #8c5e3b; color: white; }
    </style>
</head>
<body>
    <h1>Database Connection Test</h1>
    
    <?php if($db): ?>
        <p class="success">Database connection established successfully!</p>
        
        <h2>Products Table</h2>
        <?php
        try {
            $query = "SELECT * FROM products LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                echo "<table class='table'>";
                echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Image</th></tr>";
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['name']."</td>";
                    echo "<td>".$row['price']."</td>";
                    echo "<td>".$row['category']."</td>";
                    echo "<td>".$row['image_url']."</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No products found in database.</p>";
            }
        } catch(PDOException $e) {
            echo "<p class='error'>Error querying products table: ".$e->getMessage()."</p>";
        }
        ?>
        
        <h2>Testimonials Table</h2>
        <?php
        try {
            $query = "SELECT * FROM testimonials LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                echo "<table class='table'>";
                echo "<tr><th>ID</th><th>Name</th><th>Title</th><th>Rating</th><th>Avatar</th></tr>";
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['name']."</td>";
                    echo "<td>".$row['title']."</td>";
                    echo "<td>".$row['rating']."</td>";
                    echo "<td>".$row['avatar']."</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No testimonials found in database.</p>";
            }
        } catch(PDOException $e) {
            echo "<p class='error'>Error querying testimonials table: ".$e->getMessage()."</p>";
        }
        ?>
        
    <?php else: ?>
        <p class="error">Failed to connect to database. Please check your database configuration.</p>
    <?php endif; ?>
    
    <h2>Test API Endpoints</h2>
    <ul>
        <li><a href="products-coffee.php" target="_blank">Test Special Coffees</a></li>
        <li><a href="products-desserts.php" target="_blank">Test Special Desserts</a></li>
        <li><a href="products.php?category=coffee" target="_blank">Test All Coffees</a></li>
        <li><a href="products.php?category=dessert" target="_blank">Test All Desserts</a></li>
        <li><a href="testimonials.php" target="_blank">Test Testimonials</a></li>
        <li><a href="product.php?id=1" target="_blank">Test Product ID 1</a></li>
    </ul>
</body>
</html>