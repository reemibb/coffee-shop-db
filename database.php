<?php
class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "coffee_shop_db";
    private $username = "root"; // Replace with your database username
    private $password = ""; // Replace with your database password
    public $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        // Modify database.php to better handle errors
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $exception) {
            // Log the error instead of echoing it
            error_log("Connection error: " . $exception->getMessage());
            throw $exception; // Re-throw to be caught by the caller
        }
    }
}
?>