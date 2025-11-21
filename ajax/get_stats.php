<?php
/**
 * Get Live Statistics - AJAX Endpoint
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$type = clean($_GET['type'] ?? 'general');
$user = getCurrentUser();

$response = [];

try {
    switch ($type) {
        case 'general':
            // General statistics for homepage
            $statsQuery = "SELECT 
                (SELECT COUNT(*) FROM medicines) as total_medicines,
                (SELECT COUNT(*) FROM shops WHERE is_active = 1) as active_shops,
                (SELECT COUNT(*) FROM orders) as total_orders,
                (SELECT COUNT(*) FROM users WHERE role_id = 1) as total_customers,
                (SELECT COUNT(*) FROM parcels WHERE status = 'delivered') as delivered_parcels,
                (SELECT COUNT(*) FROM parcels) as total_parcels";
            
            $result = $conn->query($statsQuery);
            $stats = $result->fetch_assoc();
            
            $deliveryRate = $stats['total_parcels'] > 0 
                ? round(($stats['delivered_parcels'] / $stats['total_parcels']) * 100, 1) 
                : 0;
            
            $response = [
                'success' => true,
                'data' => [
                    'medicines' => (int)$stats['total_medicines'],
                    'shops' => (int)$stats['active_shops'],
                    'orders' => (int)$stats['total_orders'],
                    'customers' => (int)$stats['total_customers'],
                    'delivery_rate' => $deliveryRate
                ]
            ];
            break;
            
        case 'cart':
            // Cart count for logged-in customer
            if ($user['role_name'] === 'customer') {
                $cartQuery = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
                $stmt = $conn->prepare($cartQuery);
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $cartResult = $stmt->get_result()->fetch_assoc();
                
                $response = [
                    'success' => true,
                    'cart_count' => (int)($cartResult['total'] ?? 0)
                ];
            } else {
                $response = ['success' => true, 'cart_count' => 0];
            }
            break;
            
        case 'admin_dashboard':
            // Admin dashboard statistics
            if ($user['role_name'] !== 'admin') {
                throw new Exception('Unauthorized access');
            }
            
            $today = date('Y-m-d');
            
            $adminStatsQuery = "SELECT 
                (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?) as today_orders,
                (SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = ?) as today_revenue,
                (SELECT COUNT(*) FROM prescriptions WHERE status = 'pending') as pending_prescriptions,
                (SELECT COUNT(*) FROM shop_medicines WHERE stock_quantity <= reorder_level) as low_stock_items";
            
            $stmt = $conn->prepare($adminStatsQuery);
            $stmt->bind_param("ss", $today, $today);
            $stmt->execute();
            $adminStats = $stmt->get_result()->fetch_assoc();
            
            $response = [
                'success' => true,
                'data' => [
                    'today_orders' => (int)$adminStats['today_orders'],
                    'today_revenue' => (float)($adminStats['today_revenue'] ?? 0),
                    'pending_prescriptions' => (int)$adminStats['pending_prescriptions'],
                    'low_stock_items' => (int)$adminStats['low_stock_items']
                ]
            ];
            break;
            
        case 'shop_manager':
            // Shop manager statistics
            if ($user['role_name'] !== 'shop_manager' || !$user['shop_id']) {
                throw new Exception('Unauthorized access');
            }
            
            $shopId = $user['shop_id'];
            $today = date('Y-m-d');
            
            $shopStatsQuery = "SELECT 
                (SELECT COUNT(*) FROM parcels WHERE shop_id = ? AND DATE(created_at) = ?) as today_orders,
                (SELECT SUM(subtotal) FROM parcels WHERE shop_id = ? AND DATE(created_at) = ?) as today_sales,
                (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = ? AND stock_quantity <= reorder_level) as low_stock";
            
            $stmt = $conn->prepare($shopStatsQuery);
            $stmt->bind_param("isisii", $shopId, $today, $shopId, $today, $shopId);
            $stmt->execute();
            $shopStats = $stmt->get_result()->fetch_assoc();
            
            $response = [
                'success' => true,
                'data' => [
                    'today_orders' => (int)$shopStats['today_orders'],
                    'today_sales' => (float)($shopStats['today_sales'] ?? 0),
                    'low_stock' => (int)$shopStats['low_stock']
                ]
            ];
            break;
            
        default:
            throw new Exception('Invalid stats type');
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);