<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $slug;
    public $short_description;
    public $full_description;
    public $specifications;
    public $features;
    public $price;
    public $stock_status;
    public $main_image_url;
    public $gallery_images;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create product
    public function create() {
        // Generate UUID
        $this->id = $this->generateUUID();
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id=:id, category_id=:category_id, name=:name, 
                      slug=:slug, short_description=:short_description, 
                      full_description=:full_description, specifications=:specifications,
                      features=:features, price=:price, stock_status=:stock_status,
                      main_image_url=:main_image_url, gallery_images=:gallery_images";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->short_description = htmlspecialchars(strip_tags($this->short_description));
        $this->full_description = htmlspecialchars(strip_tags($this->full_description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_status = htmlspecialchars(strip_tags($this->stock_status));
        $this->main_image_url = htmlspecialchars(strip_tags($this->main_image_url));
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":short_description", $this->short_description);
        $stmt->bindParam(":full_description", $this->full_description);
        $stmt->bindParam(":specifications", $this->specifications);
        $stmt->bindParam(":features", $this->features);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_status", $this->stock_status);
        $stmt->bindParam(":main_image_url", $this->main_image_url);
        $stmt->bindParam(":gallery_images", $this->gallery_images);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Read all products
    public function read() {
        // Query to read all products
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read products by category
    public function readByCategory() {
        // Query to read products by category
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id
                  ORDER BY p.name";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind category_id
        $stmt->bindParam(":category_id", $this->category_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read one product
    public function readOne() {
        // Query to read single record
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id 
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
            $this->category_id = $row['category_id'];
            $this->name = $row['name'];
            $this->slug = $row['slug'];
            $this->short_description = $row['short_description'];
            $this->full_description = $row['full_description'];
            $this->specifications = $row['specifications'];
            $this->features = $row['features'];
            $this->price = $row['price'];
            $this->stock_status = $row['stock_status'];
            $this->main_image_url = $row['main_image_url'];
            $this->gallery_images = $row['gallery_images'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Read by slug
    public function readBySlug() {
        // Query to read single record
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.slug = :slug 
                  LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind slug
        $stmt->bindParam(":slug", $this->slug);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->category_id = $row['category_id'];
            $this->name = $row['name'];
            $this->short_description = $row['short_description'];
            $this->full_description = $row['full_description'];
            $this->specifications = $row['specifications'];
            $this->features = $row['features'];
            $this->price = $row['price'];
            $this->stock_status = $row['stock_status'];
            $this->main_image_url = $row['main_image_url'];
            $this->gallery_images = $row['gallery_images'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Update product
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET category_id=:category_id, name=:name, slug=:slug, 
                      short_description=:short_description, full_description=:full_description, 
                      specifications=:specifications, features=:features, price=:price,
                      stock_status=:stock_status, main_image_url=:main_image_url, 
                      gallery_images=:gallery_images
                  WHERE id = :id";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->short_description = htmlspecialchars(strip_tags($this->short_description));
        $this->full_description = htmlspecialchars(strip_tags($this->full_description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_status = htmlspecialchars(strip_tags($this->stock_status));
        $this->main_image_url = htmlspecialchars(strip_tags($this->main_image_url));
        
        // Bind new values
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":short_description", $this->short_description);
        $stmt->bindParam(":full_description", $this->full_description);
        $stmt->bindParam(":specifications", $this->specifications);
        $stmt->bindParam(":features", $this->features);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_status", $this->stock_status);
        $stmt->bindParam(":main_image_url", $this->main_image_url);
        $stmt->bindParam(":gallery_images", $this->gallery_images);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete product
    public function delete() {
        // Query to delete
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind id
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Search products
    public function search($keywords) {
        // Query to search products
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.name LIKE :keywords 
                  OR p.short_description LIKE :keywords 
                  OR p.full_description LIKE :keywords
                  ORDER BY p.name";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        $stmt->bindParam(":keywords", $keywords);
        
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