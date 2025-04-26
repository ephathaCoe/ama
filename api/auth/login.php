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
if(!empty($data->email) && !empty($data->password)) {
    // Set user property values
    $user->email = $data->email;
    $user->password = $data->password;
    
    // Attempt to login
    if($user->login()) {
        // Generate JWT token
        $jwt = new JwtHandler();
        $token = $jwt->generateToken([
            "id" => $user->id,
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "role" => $user->role
        ]);
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Tell the user login was successful
        echo json_encode([
            "status" => 1,
            "message" => "Login successful.",
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
        // Set response code - 401 Unauthorized
        http_response_code(401);
        
        // Tell the user login failed
        echo json_encode([
            "status" => 0,
            "message" => "Invalid email or password."
        ]);
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user data is incomplete
    echo json_encode([
        "status" => 0,
        "message" => "Unable to login. Data is incomplete."
    ]);
}
?>