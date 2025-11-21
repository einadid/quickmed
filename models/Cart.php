<?php
/**
 * Cart Model
 */

class Cart {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Get cart items
    public function getItems($userId) {
        $query = "SELECT c.*, m.name, m.power, m.form, m.image, m.requires_prescription,
                  sm.price, sm.stock_quantity,
                  s.name as shop_name, s.city
                  FROM cart c
                  JOIN medicines m ON c.medicine_id = m.id
                  JOIN shop_medicines sm ON c.medicine_id = sm.medicine_id AND c.shop_id = sm.shop_id
                  JOIN shops s ON c.shop_id = s.id
                  WHERE c.user_id = ?
                  ORDER BY s.name, m.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Add to cart
    public function add($userId, $medicineId, $shopId, $quantity) {
        // Check if exists
        $checkQuery = "SELECT id, quantity FROM cart 
                       WHERE user_id = ? AND medicine_id = ? AND shop_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("iii", $userId, $medicineId, $shopId);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("ii", $newQuantity, $existing['id']);
            return $updateStmt->execute();
        } else {
            // Insert new
            $insertQuery = "INSERT INTO cart (user_id, medicine_id, shop_id, quantity) 
                           VALUES (?, ?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("iiii", $userId, $medicineId, $shopId, $quantity);
            return $insertStmt->execute();
        }
    }
    
    // Update quantity
    public function updateQuantity($cartId, $userId, $quantity) {
        $query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $cartId, $userId);
        return $stmt->execute();
    }
    
    // Remove item
    public function remove($cartId, $userId) {
        $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $cartId, $userId);
        return $stmt->execute();
    }
    
    // Clear cart
    public function clear($userId) {
        $query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    // Get cart count
    public function getCount($userId) {
        $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }
}