<?php
/**
 * Salesman - Process Return Logic
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$user = getCurrentUser();
$shopId = $user['shop_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parcelId = intval($_POST['parcel_id']);
    $reason = clean($_POST['return_reason']);
    
    if (!$parcelId || empty($reason)) {
        $_SESSION['error'] = 'Invalid return request';
        redirect('dashboard.php');
    }
    
    // Get order details to check shop permission
    $check = $conn->query("SELECT id, order_id FROM parcels WHERE id = $parcelId AND shop_id = $shopId AND status = 'delivered'");
    if ($check->num_rows === 0) {
        $_SESSION['error'] = 'Order not found or already returned';
        redirect('dashboard.php');
    }
    
    $parcel = $check->fetch_assoc();
    
    $conn->begin_transaction();
    try {
        // 1. Update Parcel Status
        $conn->query("UPDATE parcels SET status = 'returned' WHERE id = $parcelId");
        
        // 2. Log Activity
        $stmt = $conn->prepare("INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by) VALUES (?, 'returned', ?, ?)");
        $stmt->bind_param("isi", $parcelId, $reason, $user['id']);
        $stmt->execute();
        
        // 3. Restore Stock
        $items = $conn->query("SELECT medicine_id, quantity FROM order_items WHERE parcel_id = $parcelId");
        while ($item = $items->fetch_assoc()) {
            $updateStock = $conn->prepare("UPDATE shop_medicines SET stock_quantity = stock_quantity + ? WHERE shop_id = ? AND medicine_id = ?");
            $updateStock->bind_param("iii", $item['quantity'], $shopId, $item['medicine_id']);
            $updateStock->execute();
        }
        
        // 4. Reverse Points (If any)
        $orderInfo = $conn->query("SELECT user_id, points_earned FROM orders WHERE id = " . $parcel['order_id'])->fetch_assoc();
        if ($orderInfo['points_earned'] > 0) {
            $conn->query("UPDATE users SET points = points - {$orderInfo['points_earned']} WHERE id = {$orderInfo['user_id']}");
            
            // Log Points Reversal
            $logPoints = $conn->prepare("INSERT INTO points_log (user_id, order_id, points, type, description) VALUES (?, ?, ?, 'admin_adjust', 'Order Returned')");
            $negPoints = -$orderInfo['points_earned'];
            $logPoints->bind_param("iii", $orderInfo['user_id'], $parcel['order_id'], $negPoints);
            $logPoints->execute();
        }
        
        $conn->commit();
        $_SESSION['success'] = 'Return processed successfully. Stock restored.';
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Return failed: ' . $e->getMessage();
    }
    
    redirect('dashboard.php');
}
?>