<?php
class Quote {
    private $conn;
    private $table_name = "quotes";

    public $id;
    public $customer_name;
    public $customer_email;
    public $customer_phone;
    public $company_name;
    public $product_id;
    public $message;
    public $status;
    public $assigned_to;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create quote
    public function create() {
        // Generate UUID
        $this->id = $this->generateUUID();
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id=:id, customer_name=:customer_name, customer_email=:customer_email, 
                      customer_phone=:customer_phone, company_name=:company_name, 
                      product_id=:product_id, message=:message, 
                      status=:status";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->customer_email = htmlspecialchars(strip_tags($this->customer_email));
        $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
        $this->company_name = htmlspecialchars(strip_tags($this->company_name));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Set default status if not provided
        if(empty($this->status)) {
            $this->status = 'new';
        }
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":customer_email", $this->customer_email);
        $stmt->bindParam(":customer_phone", $this->customer_phone);
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Read all quotes
    public function read() {
        // Query to read all quotes
        $query = "SELECT q.*, p.name as product_name,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                  FROM " . $this->table_name . " q
                  LEFT JOIN products p ON q.product_id = p.id
                  LEFT JOIN users u ON q.assigned_to = u.id
                  ORDER BY q.created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read quotes by status
    public function readByStatus() {
        // Query to read quotes by status
        $query = "SELECT q.*, p.name as product_name,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                  FROM " . $this->table_name . " q
                  LEFT JOIN products p ON q.product_id = p.id
                  LEFT JOIN users u ON q.assigned_to = u.id
                  WHERE q.status = :status
                  ORDER BY q.created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind status
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read one quote
    public function readOne() {
        // Query to read single record
        $query = "SELECT q.*, p.name as product_name,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                  FROM " . $this->table_name . " q
                  LEFT JOIN products p ON q.product_id = p.id
                  LEFT JOIN users u ON q.assigned_to = u.id
                  WHERE q.id = :id 
                  LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->customer_name = $row['customer_name'];
            $this->customer_email = $row['customer_email'];
            $this->customer_phone = $row['customer_phone'];
            $this->company_name = $row['company_name'];
            $this->product_id = $row['product_id'];
            $this->message = $row['message'];
            $this->status = $row['status'];
            $this->assigned_to = $row['assigned_to'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Update quote
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET status=:status, assigned_to=:assigned_to
                  WHERE id = :id";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind new values
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get quotes count by status
    public function getCountByStatus() {
        $query = "SELECT status, COUNT(*) as count
                  FROM " . $this->table_name . "
                  GROUP BY status";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Generate UUID v4
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>