<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $user_id;
    public $title;
    public $message;
    public $type;
    public $is_read;
    public $link;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create notification
    public function create() {
        // Generate UUID
        $this->id = $this->generateUUID();
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id=:id, user_id=:user_id, title=:title, 
                      message=:message, type=:type, is_read=:is_read, link=:link";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->link = htmlspecialchars(strip_tags($this->link));
        
        // Set default values if not provided
        $is_read = $this->is_read ? 1 : 0;
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":is_read", $is_read, PDO::PARAM_INT);
        $stmt->bindParam(":link", $this->link);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Create quote notification for all relevant users
    public function createQuoteNotification($quote_id, $customer_name) {
        // Get all users with admin or sales role
        $query = "SELECT id FROM users WHERE role IN ('admin', 'sales')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $success = true;
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $this->generateUUID();
            $this->user_id = $row['id'];
            $this->title = 'New Quote Request';
            $this->message = 'A new quote has been submitted by ' . $customer_name;
            $this->type = 'info';
            $this->is_read = false;
            $this->link = '/admin/quotes/' . $quote_id;
            
            if(!$this->create()) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    // Read notifications for a user
    public function readByUser() {
        // Query to read notifications for a user
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read unread notifications for a user
    public function readUnreadByUser() {
        // Query to read unread notifications for a user
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE user_id = :user_id AND is_read = 0
                  ORDER BY created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Mark notification as read
    public function markAsRead() {
        // Query to update notification
        $query = "UPDATE " . $this->table_name . "
                  SET is_read = 1
                  WHERE id = :id AND user_id = :user_id";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Mark all notifications as read for a user
    public function markAllAsRead() {
        // Query to update notifications
        $query = "UPDATE " . $this->table_name . "
                  SET is_read = 1
                  WHERE user_id = :user_id";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Count unread notifications for a user
    public function countUnread() {
        // Query to count unread notifications
        $query = "SELECT COUNT(*) as count
                  FROM " . $this->table_name . "
                  WHERE user_id = :user_id AND is_read = 0";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get count
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'];
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