<?php
/**
 * Add to Cart Handler (FIXED)
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'login_required']);
    exit;
}

// 2. Check Role (Only Customers)
$user = getCurrentUser();
if ($user['role_name'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Only customers can add to cart.']);
    exit;
}

// 3. Validate Inputs
$medicineId = intval($_POST['medicine_id'] ?? 0);
$shopId = intval($_POST['shop_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($medicineId <= 0 || $shopId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item details.']);
    exit;
}

// 4. Check Stock Availability
$stockQuery = "SELECT stock_quantity, price FROM shop_medicines WHERE medicine_id = ? AND shop_id = ?";
$stmt = $conn->prepare($stockQuery);
$stmt->bind_param("ii", $medicineId, $shopId);
$stmt->execute();
$stockResult = $stmt->get_result();

if ($stockResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item not available in this shop.']);
    exit;
}

$itemData = $stockResult->fetch_assoc();
$currentStock = $itemData['stock_quantity'];

// 5. Check Existing Cart Quantity
$checkCart = "SELECT id, quantity FROM cart WHERE user_id = ? AND medicine_id = ? AND shop_id = ?";
$cartStmt = $conn->prepare($checkCart);
$cartStmt->bind_param("iii", $user['id'], $medicineId, $shopId);
$cartStmt->execute();
$cartRes = $cartStmt->get_result();

$newQuantity = $quantity;

if ($cartRes->num_rows > 0) {
    $cartItem = $cartRes->fetch_assoc();
    $newQuantity += $cartItem['quantity'];
    $cartId = $cartItem['id'];
    
    // Check total stock limit
    if ($newQuantity > $currentStock) {
        echo json_encode(['success' => false, 'message' => "Only $currentStock items available in stock."]);
        exit;
    }
    
    // Update
    $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $newQuantity, $cartId);
    $updateStmt->execute();
} else {
    // Check stock for new item
    if ($quantity > $currentStock) {
        echo json_encode(['success' => false, 'message' => "Only $currentStock items available in stock."]);
        exit;
    }
    
    // Insert
    $insertQuery = "INSERT INTO cart (user_id, medicine_id, shop_id, quantity) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iiii", $user['id'], $medicineId, $shopId, $quantity);
    $insertStmt->execute();
}

// 6. Get Updated Cart Count
$countQuery = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $user['id']);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

echo json_encode(['success' => true, 'message' => 'Added to cart!', 'cart_count' => $total]);
?>