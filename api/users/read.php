<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and objects
include_once '../config/Database.php';
include_once '../models/User.php';
include_once '../config/JwtHandler.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate user object
$user = new User($db);

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

// Check if user has admin role
if($userData->role !== 'admin') {
    http_response_code(403);
    echo json_encode([
        "status" => 0,
        "message" => "Access denied. Only admins can view all users."
    ]);
    exit;
}

// Read users
$stmt = $user->read();
$num = $stmt->rowCount();

// Check if more than 0 record found
if($num > 0) {
    // Users array
    $users_arr = [];
    $users_arr["records"] = [];
    
    // Retrieve the table contents
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $user_item = [
            "id" => $id,
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "role" => $role,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];
        
        array_push($users_arr["records"], $user_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show users data
    echo json_encode($users_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user no users found
    echo json_encode([
        "status" => 0,
        "message" => "No users found."
    ]);
}
?>