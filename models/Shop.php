<?php
/**
 * Shop Model
 */

class Shop {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Get shop by ID
    public function getById($id) {
        $query = "SELECT * FROM shops WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all active shops
    public function getAll() {
        $query = "SELECT * FROM shops WHERE is_active = 1 ORDER BY name ASC";
        return $this->conn->query($query);
    }
    
    // Get shop inventory
    public function getInventory($shopId, $filters = []) {
        $whereConditions = ["sm.shop_id = ?"];
        $params = [$shopId];
        $types = "i";
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(m.name LIKE ? OR m.generic_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $whereConditions[] = "sm.stock_quantity <= sm.reorder_level";
        }
        
        $whereClause = implode(" AND ", $whereConditions);
        
        $query = "SELECT m.*, sm.price, sm.stock_quantity, sm.reorder_level, sm.expiry_date, sm.batch_number
                  FROM shop_medicines sm
                  JOIN medicines m ON sm.medicine_id = m.id
                  WHERE $whereClause
                  ORDER BY m.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Update stock
    public function updateStock($shopId, $medicineId, $quantity) {
        $query = "UPDATE shop_medicines 
                  SET stock_quantity = stock_quantity + ?,
                      last_restocked = NOW()
                  WHERE shop_id = ? AND medicine_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $shopId, $medicineId);
        return $stmt->execute();
    }
}