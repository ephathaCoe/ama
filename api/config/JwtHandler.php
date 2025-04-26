<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler {
    protected $jwt_secret;
    protected $token;
    protected $issuedAt;
    protected $expire;
    
    public function __construct() {
        // Set your secret key here
        $this->jwt_secret = "your_jwt_secret_key";
        $this->issuedAt = time();
        
        // Set expiration time to 24 hours (86400 seconds)
        $this->expire = $this->issuedAt + 86400;
    }
    
    // Generate JWT token
    public function generateToken($data) {
        $payload = array(
            "iss" => "amaris_heavy_machinery",
            "aud" => "amaris_users",
            "iat" => $this->issuedAt,
            "exp" => $this->expire,
            "data" => $data
        );
        
        $this->token = JWT::encode($payload, $this->jwt_secret, 'HS256');
        
        return $this->token;
    }
    
    // Validate JWT token
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            return $decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>