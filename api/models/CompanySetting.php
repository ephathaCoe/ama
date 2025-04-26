<?php
class CompanySetting {
    private $conn;
    private $table_name = "company_settings";

    public $id;
    public $name;
    public $logo_url;
    public $contact_email;
    public $contact_phone;
    public $address;
    public $social_media;
    public $homepage_content;
    public $about_content;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read company settings
    public function read() {
        // Query to read company settings
        $query = "SELECT * FROM " . $this->table_name . " LIMIT 0,1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->logo_url = $row['logo_url'];
            $this->contact_email = $row['contact_email'];
            $this->contact_phone = $row['contact_phone'];
            $this->address = $row['address'];
            $this->social_media = $row['social_media'];
            $this->homepage_content = $row['homepage_content'];
            $this->about_content = $row['about_content'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Update company settings
    public function update() {
        // Query to update company settings
        $query = "UPDATE " . $this->table_name . "
                  SET name=:name, logo_url=:logo_url, 
                      contact_email=:contact_email, contact_phone=:contact_phone,
                      address=:address, social_media=:social_media,
                      homepage_content=:homepage_content, about_content=:about_content
                  WHERE id = :id";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->logo_url = htmlspecialchars(strip_tags($this->logo_url));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":logo_url", $this->logo_url);
        $stmt->bindParam(":contact_email", $this->contact_email);
        $stmt->bindParam(":contact_phone", $this->contact_phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":social_media", $this->social_media);
        $stmt->bindParam(":homepage_content", $this->homepage_content);
        $stmt->bindParam(":about_content", $this->about_content);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>