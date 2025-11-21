<?php
/**
 * User Model
 */

class User {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Get user by ID
    public function getById($id) {
        $query = "SELECT u.*, r.name as role_name, r.display_name as role_display
                  FROM users u
                  JOIN roles r ON u.role_id = r.id
                  WHERE u.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get user by email
    public function getByEmail($email) {
        $query = "SELECT u.*, r.name as role_name
                  FROM users u
                  JOIN roles r ON u.role_id = r.id
                  WHERE u.email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Create new user
    public function create($data) {
        $query = "INSERT INTO users (role_id, username, email, password_hash, full_name, phone, address, shop_id, points)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issssssii", 
            $data['role_id'],
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['full_name'],
            $data['phone'],
            $data['address'],
            $data['shop_id'],
            $data['points']
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }
    
    // Update user points
    public function updatePoints($userId, $points) {
        $query = "UPDATE users SET points = points + ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $points, $userId);
        return $stmt->execute();
    }
    
    // Get all users with filters
    public function getAll($filters = []) {
        $whereConditions = ["1=1"];
        $params = [];
        $types = "";
        
        if (isset($filters['role_id'])) {
            $whereConditions[] = "u.role_id = ?";
            $params[] = $filters['role_id'];
            $types .= "i";
        }
        
        if (isset($filters['search'])) {
            $whereConditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        $whereClause = implode(" AND ", $whereConditions);
        
        $query = "SELECT u.*, r.display_name as role_name, s.name as shop_name
                  FROM users u
                  JOIN roles r ON u.role_id = r.id
                  LEFT JOIN shops s ON u.shop_id = s.id
                  WHERE $whereClause
                  ORDER BY u.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Ban/Unban user
    public function toggleBan($userId) {
        $query = "UPDATE users SET is_banned = NOT is_banned WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
}