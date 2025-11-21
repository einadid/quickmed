<?php
/**
 * Add Medicine to Cart
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

if (!hasRole('customer')) {
    echo json_encode(['success' => false, 'message' => 'Only customers can add to cart']);
    exit;
}

$medicineId = intval($_POST['medicine_id'] ?? 0);
$shopId = intval($_POST['shop_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($medicineId <= 0 || $shopId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Check stock availability
$stockQuery = "SELECT stock_quantity FROM shop_medicines WHERE medicine_id = ? AND shop_id = ?";
$stmt = $conn->prepare($stockQuery);
$stmt->bind_param("ii", $medicineId, $shopId);
$stmt->execute();
$stockResult = $stmt->get_result();

if ($stockResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Medicine not available']);
    exit;
}

$stock = $stockResult->fetch_assoc()['stock_quantity'];

if ($stock < $quantity) {
    echo json_encode(['success' => false, 'message' => "Only $stock items available"]);
    exit;
}

$userId = $_SESSION['user_id'];

// Check if already in cart
$checkCart = "SELECT id, quantity FROM cart WHERE user_id = ? AND medicine_id = ? AND shop_id = ?";
$checkStmt = $conn->prepare($checkCart);
$checkStmt->bind_param("iii", $userId, $medicineId, $shopId);
$checkStmt->execute();
$cartResult = $checkStmt->get_result();

if ($cartResult->num_rows > 0) {
    // Update quantity
    $cartItem = $cartResult->fetch_assoc();
    $newQuantity = $cartItem['quantity'] + $quantity;
    
    if ($newQuantity > $stock) {
        echo json_encode(['success' => false, 'message' => "Cannot add more. Only $stock items available"]);
        exit;
    }
    
    $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
    $updateStmt->execute();
} else {
    // Insert new item
    $insertQuery = "INSERT INTO cart (user_id, medicine_id, shop_id, quantity) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iiii", $userId, $medicineId, $shopId, $quantity);
    $insertStmt->execute();
}

// Get updated cart count
$countQuery = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$cartCount = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

logAudit('ADD_TO_CART', 'cart', null, null, ['medicine_id' => $medicineId, 'quantity' => $quantity]);

echo json_encode([
    'success' => true, 
    'message' => 'Item added to cart!',
    'cart_count' => $cartCount
]);