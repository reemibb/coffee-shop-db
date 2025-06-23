<?php
class Testimonial {
    // Database connection and table name
    private $conn;
    private $table_name = "testimonials";

    // Object properties
    public $id;
    public $name;
    public $title;
    public $avatar;
    public $rating;
    public $comment;
    public $created;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all testimonials
    public function getTestimonials() {
        $query = "SELECT t.id, t.name, t.title, t.avatar, t.rating, t.comment, t.created 
                  FROM " . $this->table_name . " t 
                  ORDER BY t.rating DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>