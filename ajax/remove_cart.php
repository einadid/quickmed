<?php
/**
 * Remove Item from Cart
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('customer')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$cartId = intval($_POST['cart_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($cartId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
    exit;
}

// Delete cart item
$deleteQuery = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("ii", $cartId, $userId);

if ($stmt->execute()) {
    logAudit('REMOVE_FROM_CART', 'cart', $cartId);
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}