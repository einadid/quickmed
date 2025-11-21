<?php
/**
 * Authentication Controller
 */

class AuthController {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function login($email, $password) {
        // Implementation in login.php
    }
    
    public function logout() {
        // Implementation in logout.php
    }
    
    public function register($data) {
        // Implementation in signup.php
    }
}