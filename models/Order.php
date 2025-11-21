<?php
/**
 * Order Model
 */

class Order {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Get order by ID
    public function getById($id) {
        $query = "SELECT o.*, u.full_name as customer_full_name
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE o.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get user orders
    public function getUserOrders($userId) {
        $query = "SELECT o.*, 
                  COUNT(DISTINCT p.id) as parcel_count,
                  COUNT(DISTINCT oi.id) as item_count
                  FROM orders o
                  LEFT JOIN parcels p ON o.id = p.order_id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.user_id = ?
                  GROUP BY o.id
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get order items
    public function getItems($orderId) {
        $query = "SELECT oi.*, m.image
                  FROM order_items oi
                  LEFT JOIN medicines m ON oi.medicine_id = m.id
                  WHERE oi.order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get order parcels
    public function getParcels($orderId) {
        $query = "SELECT p.*, s.name as shop_name, s.city
                  FROM parcels p
                  JOIN shops s ON p.shop_id = s.id
                  WHERE p.order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        return $stmt->get_result();
    }
}