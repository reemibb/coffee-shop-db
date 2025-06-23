<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and model
include_once 'database.php';
include_once 'Product.php';

class ProductController {
    // Get special coffees
    public function getSpecialCoffees() {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        // Initialize product object
        $product = new Product($db);

        // Get products
        $stmt = $product->getSpecialCoffees();
        $num = $stmt->rowCount();

        if($num > 0) {
            // Products array
            $products_arr = array();
            $products_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $product_item = array(
                    "id" => $id,
                    "name" => $name,
                    "description" => $description,
                    "price" => $price,
                    "imageUrl" => $image_url
                );

                array_push($products_arr["records"], $product_item);
            }

            // Set response code - 200 OK
            http_response_code(200);

            // Show products data in json format
            echo json_encode($products_arr["records"]);
        } else {
            // No products found
            http_response_code(404);
            echo json_encode(array("message" => "No coffee products found."));
        }
    }

    // Get special desserts
    public function getSpecialDesserts() {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        // Initialize product object
        $product = new Product($db);

        // Get products
        $stmt = $product->getSpecialDesserts();
        $num = $stmt->rowCount();

        if($num > 0) {
            // Products array
            $products_arr = array();
            $products_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $product_item = array(
                    "id" => $id,
                    "name" => $name,
                    "description" => $description,
                    "price" => $price,
                    "imageUrl" => $image_url
                );

                array_push($products_arr["records"], $product_item);
            }

            // Set response code - 200 OK
            http_response_code(200);

            // Show products data in json format
            echo json_encode($products_arr["records"]);
        } else {
            // No products found
            http_response_code(404);
            echo json_encode(array("message" => "No dessert products found."));
        }
    }

    // Get single product by ID
    public function getProductById($id) {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        // Initialize product object
        $product = new Product($db);
        $product->id = $id;

        // Get product
        if($product->getProductById()) {
            // Create array
            $product_arr = array(
                "id" =>  $product->id,
                "name" => $product->name,
                "description" => $product->description,
                "price" => $product->price,
                "category" => $product->category,
                "imageUrl" => $product->image_url,
                "created" => $product->created
            );

            // Set response code - 200 OK
            http_response_code(200);

            // Make it json format
            echo json_encode($product_arr);
        } else {
            // Product not found
            http_response_code(404);
            echo json_encode(array("message" => "Product does not exist."));
        }
    }
    public function getCoffeeProducts() {
    // This method should return all coffee products
    // You can copy the implementation from getSpecialCoffees() 
    // and add more coffee products if needed
    
    // For now, just return the same data as getSpecialCoffees()
    $this->getSpecialCoffees();
}

public function getDessertProducts() {
    // This method should return all dessert products
    // You can copy the implementation from getSpecialDesserts()
    // and add more dessert products if needed
    
    // For now, just return the same data as getSpecialDesserts()
    $this->getSpecialDesserts();
}
}
?>