<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and objects
include_once '../config/Database.php';
include_once '../models/Product.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate product object
$product = new Product($db);

// Query parameters
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// Read products based on filter
if($category_id) {
    $product->category_id = $category_id;
    $stmt = $product->readByCategory();
} else {
    $stmt = $product->read();
}

$num = $stmt->rowCount();

// Check if more than 0 record found
if($num > 0) {
    // Products array
    $products_arr = [];
    $products_arr["records"] = [];
    
    // Retrieve the table contents
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $product_item = [
            "id" => $id,
            "category_id" => $category_id,
            "category_name" => $category_name,
            "name" => $name,
            "slug" => $slug,
            "short_description" => $short_description,
            "full_description" => $full_description,
            "specifications" => json_decode($specifications),
            "features" => json_decode($features),
            "price" => $price,
            "stock_status" => $stock_status,
            "main_image_url" => $main_image_url,
            "gallery_images" => json_decode($gallery_images),
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];
        
        array_push($products_arr["records"], $product_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show products data
    echo json_encode($products_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user no products found
    echo json_encode([
        "status" => 0,
        "message" => "No products found."
    ]);
}
?>