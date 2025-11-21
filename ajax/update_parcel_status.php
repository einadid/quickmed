<?php
/**
 * Update Parcel Status - AJAX Handler
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Allow only staff
$user = getCurrentUser();
if (!in_array($user['role_name'], ['shop_manager', 'salesman', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$parcelId = intval($_POST['parcel_id'] ?? 0);
$newStatus = clean($_POST['status'] ?? '');
$remarks = clean($_POST['remarks'] ?? 'Status updated via panel');

if (!$parcelId || empty($newStatus)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Verify shop access (Admin can access all)
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
    if ($parcel['shop_id'] != $user['shop_id']) {
        echo json_encode(['success' => false, 'message' => 'Access denied for this shop']);
        exit;
    }
}

$conn->begin_transaction();

try {
    // Update parcel status
    $updateQuery = "UPDATE parcels SET status = ?, updated_by = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sii", $newStatus, $user['id'], $parcelId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update parcel status');
    }
    
    // If delivered, update timestamp
    if ($newStatus === 'delivered') {
        $deliveredQuery = "UPDATE parcels SET delivered_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($deliveredQuery);
        $stmt->bind_param("i", $parcelId);
        $stmt->execute();
        
        // Award points if pending
        // (Optional: Logic to ensure points awarded only once)
    }
    
    // Log status change
    $logQuery = "INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("issi", $parcelId, $newStatus, $remarks, $user['id']);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Status updated to ' . ucfirst($newStatus)]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}