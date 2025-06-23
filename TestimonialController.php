<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and model
include_once 'database.php';
include_once 'Testimonial.php';

class TestimonialController {
    // Get testimonials
    public function getTestimonials() {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        // Initialize testimonial object
        $testimonial = new Testimonial($db);

        // Get testimonials
        $stmt = $testimonial->getTestimonials();
        $num = $stmt->rowCount();

        if($num > 0) {
            // Testimonials array
            $testimonials_arr = array();
            $testimonials_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $testimonial_item = array(
                    "id" => $id,
                    "name" => $name,
                    "title" => $title,
                    "avatar" => $avatar,
                    "rating" => $rating,
                    "comment" => $comment
                );

                array_push($testimonials_arr["records"], $testimonial_item);
            }

            // Set response code - 200 OK
            http_response_code(200);

            // Show testimonials in json format
            echo json_encode($testimonials_arr["records"]);
        } else {
            // No testimonials found
            http_response_code(404);
            echo json_encode(array("message" => "No testimonials found."));
        }
    }
}
?>