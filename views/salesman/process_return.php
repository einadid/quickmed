<?php
/**
 * Salesman - Process Return Logic (Partial + Penalty Points)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$user = getCurrentUser();
$shopId = $user['shop_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parcelId = intval($_POST['parcel_id']);
    $reason = clean($_POST['return_reason']);
    $returnQty = $_POST['return_qty']; // Array [medicine_id => quantity]
    
    if (!$parcelId || empty($returnQty)) {
        $_SESSION['error'] = 'Invalid return request';
        redirect('dashboard.php');
    }
    
    // Get order details
    $check = $conn->query("SELECT p.id, p.order_id, o.user_id, o.points_earned, o.total_amount 
                           FROM parcels p 
                           JOIN orders o ON p.order_id = o.id 
                           WHERE p.id = $parcelId AND p.shop_id = $shopId AND p.status = 'delivered'");
    
    if ($check->num_rows === 0) {
        $_SESSION['error'] = 'Order not found or already returned';
        redirect('dashboard.php');
    }
    
    $parcel = $check->fetch_assoc();
    
    $conn->begin_transaction();
    try {
        $totalRefundAmount = 0;
        $itemsReturned = 0;

        foreach ($returnQty as $medId => $qty) {
            $qty = intval($qty);
            if ($qty <= 0) continue;

            // Verify item belongs to order
            $itemQuery = $conn->query("SELECT price, quantity FROM order_items WHERE parcel_id = $parcelId AND medicine_id = $medId");
            $itemData = $itemQuery->fetch_assoc();

            if ($itemData && $qty <= $itemData['quantity']) {
                // Restore Stock
                $conn->query("UPDATE shop_medicines SET stock_quantity = stock_quantity + $qty WHERE shop_id = $shopId AND medicine_id = $medId");
                
                // Calculate Refund Amount
                $totalRefundAmount += ($itemData['price'] * $qty);
                $itemsReturned++;
                
                // Log specific item return (Optional: create a return_items table for better tracking)
            }
        }

        if ($itemsReturned > 0) {
            // Update Parcel Status to 'Returned' if fully returned, or create a log for partial
            // For simplicity, let's mark as 'returned' (or you can add 'partial_return' enum)
            $conn->query("UPDATE parcels SET status = 'returned' WHERE id = $parcelId");

            // Log Activity
            $stmt = $conn->prepare("INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by) VALUES (?, 'returned', ?, ?)");
            $logMsg = "Returned Items. Reason: $reason";
            $stmt->bind_param("isi", $parcelId, $logMsg, $user['id']);
            $stmt->execute();

            // PENALTY POINTS CALCULATION
            // Rule: Deduct 120 points for every 1000 BDT returned
            // Formula: (RefundAmount / 1000) * 120
            $pointsToDeduct = floor(($totalRefundAmount / 1000) * 120);

            if ($pointsToDeduct > 0 && $parcel['user_id']) {
                // Deduct points from user
                $conn->query("UPDATE users SET points = GREATEST(0, points - $pointsToDeduct) WHERE id = {$parcel['user_id']}");
                
                // Log Points Deduction
                $logPoints = $conn->prepare("INSERT INTO points_log (user_id, order_id, points, type, description) VALUES (?, ?, ?, 'admin_adjust', 'Return Penalty')");
                $negPoints = -$pointsToDeduct;
                $logPoints->bind_param("iii", $parcel['user_id'], $parcel['order_id'], $negPoints);
                $logPoints->execute();
            }

            $conn->commit();
            $_SESSION['success'] = "Return processed. Refund: ৳$totalRefundAmount. Points Deducted: $pointsToDeduct";
        } else {
            throw new Exception("No valid items to return");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Return failed: ' . $e->getMessage();
    }
    
    redirect('views/salesman/dashboard.php');
}
?>