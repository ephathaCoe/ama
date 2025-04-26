<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and objects
include_once '../config/Database.php';
include_once '../models/Product.php';
include_once '../config/JwtHandler.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate product object
$product = new Product($db);

// Get the JWT token from the headers
$allHeaders = getallheaders();
$authHeader = isset($allHeaders['Authorization']) ? $allHeaders['Authorization'] : '';

if(empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode([
        "status" => 0,
        "message" => "Access denied. Token not provided or invalid format."
    ]);
    exit;
}

$jwt = new JwtHandler();
$token = $matches[1];
$userData = $jwt->validateToken($token);

if(!$userData) {
    http_response_code(401);
    echo json_encode([
        "status" => 0,
        "message" => "Access denied. Invalid token."
    ]);
    exit;
}

// Check if user has admin or sales role
if(!in_array($userData->role, ['admin', 'sales'])) {
    http_response_code(403);
    echo json_encode([
        "status" => 0,
        "message" => "Access denied. Only admins and sales can create products."
    ]);
    exit;
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->category_id) &&
    !empty($data->name) &&
    !empty($data->slug) &&
    !empty($data->short_description) &&
    !empty($data->full_description) &&
    !empty($data->stock_status)
) {
    // Set product property values
    $product->category_id = $data->category_id;
    $product->name = $data->name;
    $product->slug = $data->slug;
    $product->short_description = $data->short_description;
    $product->full_description = $data->full_description;
    $product->specifications = json_encode($data->specifications ?? []);
    $product->features = json_encode($data->features ?? []);
    $product->price = $data->price ?? null;
    $product->stock_status = $data->stock_status;
    $product->main_image_url = $data->main_image_url ?? null;
    $product->gallery_images = json_encode($data->gallery_images ?? []);
    
    // Create the product
    if($product->create()) {
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode([
            "status" => 1,
            "message" => "Product was created successfully.",
            "id" => $product->id
        ]);
    } else {
        // If unable to create the product, tell the user
        http_response_code(503);
        echo json_encode([
            "status" => 0,
            "message" => "Unable to create product."
        ]);
    }
} else {
    // Tell the user data is incomplete
    http_response_code(400);
    echo json_encode([
        "status" => 0,
        "message" => "Unable to create product. Data is incomplete."
    ]);
}
?>