<?php
/**
 * Salesman - Process Return Logic (FIXED & SECURE)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$user = getCurrentUser();
$shopId = $user['shop_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input Validation
    $parcelId = isset($_POST['parcel_id']) ? intval($_POST['parcel_id']) : 0;
    $reason = isset($_POST['return_reason']) ? clean($_POST['return_reason']) : '';
    
    if ($parcelId <= 0 || empty($reason)) {
        $_SESSION['error'] = 'Invalid return request parameters.';
        redirect('dashboard.php');
    }
    
    // Check if order exists, belongs to this shop, and is delivered
    $checkStmt = $conn->prepare("SELECT id, order_id FROM parcels WHERE id = ? AND shop_id = ? AND status = 'delivered'");
    $checkStmt->bind_param("ii", $parcelId, $shopId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Order not found, not delivered yet, or already returned.';
        redirect('dashboard.php');
    }
    
    $parcel = $result->fetch_assoc();
    
    // Start Transaction
    $conn->begin_transaction();
    try {
        // 1. Update Parcel Status
        $updateStmt = $conn->prepare("UPDATE parcels SET status = 'returned' WHERE id = ?");
        $updateStmt->bind_param("i", $parcelId);
        $updateStmt->execute();
        
        // 2. Log Activity
        $logStmt = $conn->prepare("INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by) VALUES (?, 'returned', ?, ?)");
        $logStmt->bind_param("isi", $parcelId, $reason, $user['id']);
        $logStmt->execute();
        
        // 3. Restore Stock (Loop through items)
        $itemsStmt = $conn->prepare("SELECT medicine_id, quantity FROM order_items WHERE parcel_id = ?");
        $itemsStmt->bind_param("i", $parcelId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();

        while ($item = $itemsResult->fetch_assoc()) {
            // Add quantity back to shop_medicines
            $restoreStmt = $conn->prepare("UPDATE shop_medicines SET stock_quantity = stock_quantity + ? WHERE shop_id = ? AND medicine_id = ?");
            $restoreStmt->bind_param("iii", $item['quantity'], $shopId, $item['medicine_id']);
            $restoreStmt->execute();
        }
        
        // 4. Reverse Points (Only if user earned points)
        // Check if points column exists first (Safety check)
        $orderQuery = $conn->query("SELECT user_id, points_earned FROM orders WHERE id = " . intval($parcel['order_id']));
        if ($orderQuery && $orderInfo = $orderQuery->fetch_assoc()) {
            if ($orderInfo['points_earned'] > 0) {
                // Deduct points
                $deductStmt = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
                $deductStmt->bind_param("ii", $orderInfo['points_earned'], $orderInfo['user_id']);
                $deductStmt->execute();
                
                // Log the deduction if you have a points_log table
                // (Checking if table exists to avoid fatal error)
                $checkTable = $conn->query("SHOW TABLES LIKE 'points_log'");
                if ($checkTable->num_rows > 0) {
                    $logPoints = $conn->prepare("INSERT INTO points_log (user_id, order_id, points, type, description) VALUES (?, ?, ?, 'admin_adjust', 'Order Returned')");
                    $negPoints = -$orderInfo['points_earned'];
                    $logPoints->bind_param("iii", $orderInfo['user_id'], $parcel['order_id'], $negPoints);
                    $logPoints->execute();
                }
            }
        }
        
        $conn->commit();
        $_SESSION['success'] = 'Return processed successfully. Stock restored.';
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'System Error: Return failed. ' . $e->getMessage();
    }
    
    redirect('dashboard.php');
} else {
    redirect('dashboard.php');
}
?>