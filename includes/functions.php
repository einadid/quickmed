<?php
/**
 * QuickMed - Helper Functions
 * Additional utility functions
 */

/**
 * Get medicine categories
 */
function getMedicineCategories() {
    global $conn;
    $query = "SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL ORDER BY category";
    $result = $conn->query($query);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    return $categories;
}

/**
 * Get recent orders
 */
function getRecentOrders($limit = 5) {
    global $conn;
    $query = "SELECT o.*, u.full_name 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC 
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get top selling medicines
 */
function getTopSellingMedicines($limit = 10) {
    global $conn;
    $query = "SELECT m.*, COUNT(oi.id) as order_count, SUM(oi.quantity) as total_sold
              FROM medicines m
              JOIN order_items oi ON m.id = oi.medicine_id
              GROUP BY m.id
              ORDER BY total_sold DESC
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get shop by ID
 */
function getShopById($shopId) {
    global $conn;
    $query = "SELECT * FROM shops WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $shopId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Check if medicine requires prescription
 */
function requiresPrescription($medicineId) {
    global $conn;
    $query = "SELECT requires_prescription FROM medicines WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $medicineId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['requires_prescription'] ?? false;
}

/**
 * Get user's total orders
 */
function getUserOrderCount($userId) {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'];
}

/**
 * Format phone number
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 11) {
        return substr($phone, 0, 4) . '-' . substr($phone, 4);
    }
    return $phone;
}

/**
 * Get order status badge
 */
function getStatusBadge($status) {
    $badges = [
        'processing' => '<span class="badge badge-info">Processing</span>',
        'packed' => '<span class="badge badge-warning">Packed</span>',
        'ready' => '<span class="badge badge-warning">Ready</span>',
        'out_for_delivery' => '<span class="badge badge-info">Out for Delivery</span>',
        'delivered' => '<span class="badge badge-success">Delivered</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
    ];
    return $badges[$status] ?? $status;
}

/**
 * Calculate discount percentage
 */
function calculateDiscount($originalPrice, $salePrice) {
    if ($originalPrice <= 0) return 0;
    return round((($originalPrice - $salePrice) / $originalPrice) * 100, 1);
}

/**
 * Is shop open now
 */
function isShopOpen() {
    $currentHour = (int)date('H');
    return ($currentHour >= 8 && $currentHour < 22); // 8 AM to 10 PM
}

/**
 * Get delivery estimate
 */
function getDeliveryEstimate($city = null) {
    $estimates = [
        'Dhaka' => '24-48 hours',
        'Chittagong' => '48-72 hours',
        'Sylhet' => '48-72 hours',
        'Rajshahi' => '48-72 hours',
        'Barishal' => '48-72 hours',
        'default' => '2-5 business days'
    ];
    return $estimates[$city] ?? $estimates['default'];
}

// Auto deactivate expired flash sales
function checkExpiredSales() {
    global $conn;
    $conn->query("UPDATE flash_sales SET is_active = 0 WHERE expires_at < NOW() AND is_active = 1");
}
// Run check
checkExpiredSales();