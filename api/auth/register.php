<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and user object
include_once '../config/Database.php';
include_once '../models/User.php';
include_once '../config/JwtHandler.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->first_name) &&
    !empty($data->last_name)
) {
    // Set user property values
    $user->email = $data->email;
    $user->password = $data->password;
    $user->first_name = $data->first_name;
    $user->last_name = $data->last_name;
    
    // Check if this is the first user (will be admin)
    $isFirstUser = $user->isFirstUser();
    $user->role = $isFirstUser ? "admin" : "sales"; // First user is admin, otherwise default to sales
    
    // Create the user
    if($user->create()) {
        // Generate JWT token
        $jwt = new JwtHandler();
        $token = $jwt->generateToken([
            "id" => $user->id,
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "role" => $user->role
        ]);
        
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode([
            "status" => 1,
            "message" => "User was created successfully as " . $user->role,
            "token" => $token,
            "user" => [
                "id" => $user->id,
                "email" => $user->email,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "role" => $user->role
            ]
        ]);
    } else {
        // If unable to create the user, tell the user
        http_response_code(503);
        echo json_encode([
            "status" => 0,
            "message" => "Unable to create user."
        ]);
    }
} else {
    // Tell the user data is incomplete
    http_response_code(400);
    echo json_encode([
        "status" => 0,
        "message" => "Unable to create user. Data is incomplete."
    ]);
}
?>