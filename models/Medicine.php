<?php
/**
 * Medicine Model
 */

class Medicine {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Get medicine by ID
    public function getById($id) {
        $query = "SELECT * FROM medicines WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get medicine with shop details
    public function getWithShops($medicineId) {
        $query = "SELECT m.*, sm.price, sm.stock_quantity, sm.shop_id,
                  s.name as shop_name, s.city
                  FROM medicines m
                  JOIN shop_medicines sm ON m.id = sm.medicine_id
                  JOIN shops s ON sm.shop_id = s.id
                  WHERE m.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $medicineId);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Search medicines
    public function search($query) {
        $searchTerm = "%$query%";
        $sql = "SELECT DISTINCT m.id, m.name, m.generic_name, m.power, m.form, m.image,
                sm.price, sm.stock_quantity, sm.shop_id,
                s.name as shop_name, s.city
                FROM medicines m
                JOIN shop_medicines sm ON m.id = sm.medicine_id
                JOIN shops s ON sm.shop_id = s.id
                WHERE (m.name LIKE ? OR m.generic_name LIKE ? OR m.brand LIKE ?)
                AND sm.stock_quantity > 0
                AND s.is_active = 1
                ORDER BY m.name ASC
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get all medicines with pagination
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $whereConditions = ["sm.stock_quantity > 0", "s.is_active = 1"];
        $params = [];
        $types = "";
        
        if (!empty($filters['category'])) {
            $whereConditions[] = "m.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(m.name LIKE ? OR m.generic_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if (!empty($filters['shop_id'])) {
            $whereConditions[] = "sm.shop_id = ?";
            $params[] = $filters['shop_id'];
            $types .= "i";
        }
        
        $whereClause = implode(" AND ", $whereConditions);
        
        $query = "SELECT m.*, sm.price, sm.stock_quantity, sm.shop_id,
                  s.name as shop_name, s.city
                  FROM medicines m
                  JOIN shop_medicines sm ON m.id = sm.medicine_id
                  JOIN shops s ON sm.shop_id = s.id
                  WHERE $whereClause
                  GROUP BY m.id
                  ORDER BY m.name ASC
                  LIMIT ? OFFSET ?";
        
        $allParams = array_merge($params, [$perPage, $offset]);
        $allTypes = $types . "ii";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($allTypes)) {
            $stmt->bind_param($allTypes, ...$allParams);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Count total medicines
    public function count($filters = []) {
        $whereConditions = ["sm.stock_quantity > 0", "s.is_active = 1"];
        $params = [];
        $types = "";
        
        if (!empty($filters['category'])) {
            $whereConditions[] = "m.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(m.name LIKE ? OR m.generic_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        $whereClause = implode(" AND ", $whereConditions);
        
        $query = "SELECT COUNT(DISTINCT m.id) as total
                  FROM medicines m
                  JOIN shop_medicines sm ON m.id = sm.medicine_id
                  JOIN shops s ON sm.shop_id = s.id
                  WHERE $whereClause";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
}