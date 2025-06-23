<?php
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-top: 30px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow: auto; max-height: 300px; }
        button { margin: 5px; padding: 8px 12px; }
    </style>
</head>
<body>
    <h1>API Testing Page</h1>
    
    <h2>Test Endpoints</h2>
    <button onclick="testEndpoint('products-coffee.php')">Test Special Coffees</button>
    <button onclick="testEndpoint('products-desserts.php')">Test Special Desserts</button>
    <button onclick="testEndpoint('products.php?category=coffee')">Test All Coffees</button>
    <button onclick="testEndpoint('products.php?category=dessert')">Test All Desserts</button>
    <button onclick="testEndpoint('testimonials.php')">Test Testimonials</button>
    <button onclick="testEndpoint('product.php?id=1')">Test Product ID 1</button>
    
    <h2>Response</h2>
    <pre id="response">Click a button to test an endpoint...</pre>
    
    <script>
        function testEndpoint(endpoint) {
            document.getElementById('response').textContent = 'Loading...';
            
            fetch(endpoint)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('response').textContent = 
                        JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById('response').textContent = 
                        `Error: ${error.message}`;
                });
        }
    </script>
</body>
</html>