<?php
/**
 * Update Parcel Status - AJAX Handler (FIXED PERMISSIONS)
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// 1. Check Login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();

// 2. Check Permissions (Explicitly Allow Salesman)
$allowedRoles = ['salesman', 'shop_manager', 'admin'];

if (!isset($user['role_name']) || !in_array($user['role_name'], $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Insufficient permissions.']);
    exit;
}

// 3. Validate Input
$parcelId = intval($_POST['parcel_id'] ?? 0);
$newStatus = clean($_POST['status'] ?? '');
$remarks = clean($_POST['remarks'] ?? 'Status updated via panel');

if (!$parcelId || empty($newStatus)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit;
}

// 4. Verify Shop Access (Skip check for Admin)
if ($user['role_name'] !== 'admin') {
    $checkQuery = "SELECT shop_id FROM parcels WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $parcelId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Parcel not found']);
        exit;
    }
    
    $parcel = $result->fetch_assoc();
    
    // Ensure user belongs to the same shop
    // Note: Ensure your users table has 'shop_id' populated for salesmen
    if ($parcel['shop_id'] != $user['shop_id']) {
        echo json_encode(['success' => false, 'message' => 'Access denied: You can only update orders for your shop']);
        exit;
    }
}

// 5. Process Update
$conn->begin_transaction();

try {
    // Update parcel status
    $updateQuery = "UPDATE parcels SET status = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sii", $newStatus, $user['id'], $parcelId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update parcel status');
    }
    
    // If delivered, update delivered_at timestamp
    if ($newStatus === 'delivered') {
        $deliveredQuery = "UPDATE parcels SET delivered_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($deliveredQuery);
        $stmt->bind_param("i", $parcelId);
        $stmt->execute();
    }
    
    // Log status change
    $logQuery = "INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("issi", $parcelId, $newStatus, $remarks, $user['id']);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Status updated to ' . ucfirst(str_replace('_', ' ', $newStatus))
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>