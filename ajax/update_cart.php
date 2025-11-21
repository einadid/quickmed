<?php
/**
 * Update Cart Quantity
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('customer')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$cartId = intval($_POST['cart_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($cartId <= 0 || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$userId = $_SESSION['user_id'];

// Verify cart item belongs to user and check stock
$checkQuery = "SELECT c.medicine_id, c.shop_id, sm.stock_quantity 
               FROM cart c
               JOIN shop_medicines sm ON c.medicine_id = sm.medicine_id AND c.shop_id = sm.shop_id
               WHERE c.id = ? AND c.user_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

$cartData = $result->fetch_assoc();

if ($quantity > $cartData['stock_quantity']) {
    echo json_encode(['success' => false, 'message' => "Only {$cartData['stock_quantity']} items available"]);
    exit;
}

// Update quantity
$updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ii", $quantity, $cartId);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cart updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}