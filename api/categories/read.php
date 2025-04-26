<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and objects
include_once '../config/Database.php';
include_once '../models/Category.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate category object
$category = new Category($db);

// Read categories
$stmt = $category->read();
$num = $stmt->rowCount();

// Check if more than 0 record found
if($num > 0) {
    // Categories array
    $categories_arr = [];
    $categories_arr["records"] = [];
    
    // Retrieve the table contents
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $category_item = [
            "id" => $id,
            "name" => $name,
            "slug" => $slug,
            "description" => $description,
            "image_url" => $image_url,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];
        
        array_push($categories_arr["records"], $category_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show categories data
    echo json_encode($categories_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user no categories found
    echo json_encode([
        "status" => 0,
        "message" => "No categories found."
    ]);
}
?>