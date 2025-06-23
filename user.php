<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $token;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user (register)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET name=:name, email=:email, password=:password";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind parameters
        $stmt->bindParam(":email", $this->email);

        // Execute query
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    // Login user
    public function login() {
        $query = "SELECT id, name, email, password FROM " . $this->table_name . " WHERE email = :email LIMIT 1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind parameters
        $stmt->bindParam(":email", $this->email);

        // Execute query
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password, $row['password'])) {
            return array(
                "success" => true,
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $row['email']
            );
        }

        return array("success" => false);
    }

    // Save user token
    public function saveToken() {
        $query = "UPDATE " . $this->table_name . " SET token = :token WHERE email = :email";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->token = htmlspecialchars(strip_tags($this->token));

        // Bind parameters
        $stmt->bindParam(":token", $this->token);
        $stmt->bindParam(":email", $this->email);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>