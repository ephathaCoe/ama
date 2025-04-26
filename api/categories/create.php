<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and objects
include_once '../config/Database.php';
include_once '../models/Category.php';
include_once '../config/JwtHandler.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate category object
$category = new Category($db);

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
        "message" => "Access denied. Only admins and sales can create categories."
    ]);
    exit;
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->name) &&
    !empty($data->slug)
) {
    // Set category property values
    $category->name = $data->name;
    $category->slug = $data->slug;
    $category->description = $data->description ?? null;
    $category->image_url = $data->image_url ?? null;
    
    // Create the category
    if($category->create()) {
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode([
            "status" => 1,
            "message" => "Category was created successfully.",
            "id" => $category->id
        ]);
    } else {
        // If unable to create the category, tell the user
        http_response_code(503);
        echo json_encode([
            "status" => 0,
            "message" => "Unable to create category."
        ]);
    }
} else {
    // Tell the user data is incomplete
    http_response_code(400);
    echo json_encode([
        "status" => 0,
        "message" => "Unable to create category. Data is incomplete."
    ]);
}
?>