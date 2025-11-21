<?php
/**
 * POS Submit Handler (Fixed)
 */
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('salesman')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$user = getCurrentUser();
$shopId = $user['shop_id'];
$customerName = $input['customer_name'] ?: 'Walk-in Customer';
$memberId = $input['member_id'];
$vatPercent = floatval($input['vat_percent'] ?? 0);

$conn->begin_transaction();

try {
    $subtotal = 0;
    $cartItems = $input['cart'];
    
    // 1. Calculate Subtotal & Verify Stock
    foreach ($cartItems as $item) {
        $check = $conn->query("SELECT price, stock_quantity FROM shop_medicines WHERE medicine_id = {$item['id']} AND shop_id = $shopId")->fetch_assoc();
        
        if ($check['stock_quantity'] < $item['quantity']) {
            throw new Exception("Stock insufficient for " . $item['name']);
        }
        $subtotal += $check['price'] * $item['quantity'];
    }

    // 2. VAT & Total
    $vatAmount = $subtotal * ($vatPercent / 100);
    $totalAmount = $subtotal + $vatAmount;
    $pointsEarned = floor($totalAmount); // 1 Tk = 1 Point

    // 3. Handle Member
    $userId = null;
    if ($memberId) {
        $memRes = $conn->query("SELECT id FROM users WHERE member_id = '$memberId' AND role_id = 1");
        if ($memRes->num_rows > 0) {
            $userId = $memRes->fetch_assoc()['id'];
        }
    }

    // 4. Create Order
    $orderNum = generateOrderNumber();
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, customer_name, subtotal, total_amount, points_earned, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'paid')");
    $uid = $userId ?: $user['id']; // Fallback to salesman ID if no member
    $stmt->bind_param("issddd", $uid, $orderNum, $customerName, $subtotal, $totalAmount, $pointsEarned);
    $stmt->execute();
    $orderId = $stmt->insert_id;

    // 5. Create Parcel
    $parcelNum = generateParcelNumber($orderId, $shopId);
    $itemCount = count($cartItems);
    $stmt = $conn->prepare("INSERT INTO parcels (order_id, shop_id, parcel_number, items_count, subtotal, status, updated_by) VALUES (?, ?, ?, ?, ?, 'delivered', ?)");
    $stmt->bind_param("iisidi", $orderId, $shopId, $parcelNum, $itemCount, $totalAmount, $user['id']);
    $stmt->execute();
    $parcelId = $stmt->insert_id;

    // 6. Insert Items & Reduce Stock
    foreach ($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, parcel_id, medicine_id, shop_id, medicine_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisidd", $orderId, $parcelId, $item['id'], $shopId, $item['name'], $item['quantity'], $item['price'], $itemTotal);
        $stmt->execute();

        $conn->query("UPDATE shop_medicines SET stock_quantity = stock_quantity - {$item['quantity']} WHERE shop_id = $shopId AND medicine_id = {$item['id']}");
    }

    // 7. Award Points
    if ($userId && $pointsEarned > 0) {
        $conn->query("UPDATE users SET points = points + $pointsEarned WHERE id = $userId");
        $conn->query("INSERT INTO points_log (user_id, order_id, points, type, description) VALUES ($userId, $orderId, $pointsEarned, 'order', 'POS Purchase')");
    }

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId, 'message' => 'Sale Successful!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>