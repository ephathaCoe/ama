<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and objects
include_once '../config/Database.php';
include_once '../models/Quote.php';
include_once '../models/Notification.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate objects
$quote = new Quote($db);
$notification = new Notification($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->customer_name) &&
    !empty($data->customer_email) &&
    !empty($data->message)
) {
    // Set quote property values
    $quote->customer_name = $data->customer_name;
    $quote->customer_email = $data->customer_email;
    $quote->customer_phone = $data->customer_phone ?? null;
    $quote->company_name = $data->company_name ?? null;
    $quote->product_id = $data->product_id ?? null;
    $quote->message = $data->message;
    $quote->status = 'new';
    
    // Create the quote
    if($quote->create()) {
        // Create notifications for admins and sales users
        $notification->createQuoteNotification($quote->id, $quote->customer_name);
        
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode([
            "status" => 1,
            "message" => "Quote request was submitted successfully."
        ]);
    } else {
        // If unable to create the quote, tell the user
        http_response_code(503);
        echo json_encode([
            "status" => 0,
            "message" => "Unable to submit quote request."
        ]);
    }
} else {
    // Tell the user data is incomplete
    http_response_code(400);
    echo json_encode([
        "status" => 0,
        "message" => "Unable to submit quote request. Data is incomplete."
    ]);
}
?>