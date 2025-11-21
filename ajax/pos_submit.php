<?php
/**
 * POS Submit Handler (FIXED & TESTED)
 */

// Disable error display to prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0); 

require_once __DIR__ . '/../config.php';

// Fallback for clean function if not in config
if (!function_exists('clean')) {
    function clean($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

header('Content-Type: application/json');

try {
    // 1. Auth Check
    if (!isLoggedIn()) {
        throw new Exception('Unauthorized access');
    }

    $user = getCurrentUser();
    
    // Allow Salesman, Manager, Admin
    if (!in_array($user['role_name'], ['salesman', 'shop_manager', 'admin'])) {
        throw new Exception('Permission denied');
    }

    // 2. Get Input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    if (empty($input['cart'])) {
        throw new Exception('Cart is empty');
    }

    $shopId = $user['shop_id'];
    if (!$shopId && $user['role_name'] !== 'admin') {
        throw new Exception('No shop assigned to user');
    }

    // Default Shop for Admin (Optional)
    if (!$shopId) $shopId = 1;

    // --- DATA SANITIZATION & PREPARATION (UPDATED) ---
    
    // Customer Name
    $customerName = isset($input['customer_name']) && !empty($input['customer_name']) 
                    ? clean($input['customer_name']) 
                    : 'Walk-in Customer';

    // Customer Address (UPDATED LOGIC)
    $customerAddress = isset($input['customer_address']) && !empty($input['customer_address'])
                    ? clean($input['customer_address'])
                    : 'POS Sale';

    $memberId = isset($input['member_id']) ? clean($input['member_id']) : '';
    $prescriptionId = intval($input['prescription_id'] ?? 0);
    $vatPercent = floatval($input['vat_percent'] ?? 0);
    $pointsUsed = intval($input['points_used'] ?? 0);

    $conn->begin_transaction();

    // 3. Calculate Totals
    $subtotal = 0;
    $cartItems = $input['cart'];

    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("SELECT price, stock_quantity FROM shop_medicines WHERE medicine_id = ? AND shop_id = ?");
        $stmt->bind_param("ii", $item['id'], $shopId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 0) {
            throw new Exception("Item ID {$item['id']} not found in this shop");
        }
        
        $check = $res->fetch_assoc();
        
        if ($check['stock_quantity'] < $item['quantity']) {
            throw new Exception("Stock insufficient for " . $item['name']);
        }
        $subtotal += $check['price'] * $item['quantity'];
    }

    // 4. Points & Discounts
    $pointsDiscount = floor($pointsUsed / 100) * 10;
    $taxableAmount = $subtotal - $pointsDiscount;
    $vatAmount = $taxableAmount * ($vatPercent / 100);
    $totalAmount = $taxableAmount + $vatAmount;
    $pointsEarned = floor($totalAmount); // 1 Tk = 1 Point

    // 5. Member Handling
    $userId = null;
    if ($memberId) {
        $memStmt = $conn->prepare("SELECT id, points FROM users WHERE member_id = ? AND role_id = 1");
        $memStmt->bind_param("s", $memberId);
        $memStmt->execute();
        $memRes = $memStmt->get_result();
        
        if ($memRes->num_rows > 0) {
            $member = $memRes->fetch_assoc();
            $userId = $member['id'];
            
            if ($pointsUsed > $member['points']) {
                throw new Exception("Insufficient points balance");
            }
        }
    }

    // Determine Delivery Type
    $deliveryType = ($prescriptionId > 0) ? 'home' : 'pickup';
    $uid = $userId ?: $user['id'];
    $orderNum = generateOrderNumber();

    // 6. Create Order (UPDATED BINDING)
    $orderQuery = "INSERT INTO orders (user_id, order_number, customer_name, customer_address, delivery_type, subtotal, points_used, points_discount, total_amount, points_earned, payment_status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid')";
    
    $stmt = $conn->prepare($orderQuery);
    if (!$stmt) throw new Exception("Order Prepare Failed: " . $conn->error);
    
    // Params: i(uid), s(num), s(name), s(address), s(del_type), d(sub), d(pts_used), d(pts_disc), d(total), d(pts_earned)
    $stmt->bind_param("issssddddi", $uid, $orderNum, $customerName, $customerAddress, $deliveryType, $subtotal, $pointsUsed, $pointsDiscount, $totalAmount, $pointsEarned);
    
    if (!$stmt->execute()) throw new Exception("Order Execute Failed: " . $stmt->error);
    
    $orderId = $stmt->insert_id;

    // 7. Create Parcel
    $parcelNum = generateParcelNumber($orderId, $shopId);
    $itemCount = count($cartItems);
    
    $parcelQuery = "INSERT INTO parcels (order_id, shop_id, parcel_number, items_count, subtotal, status, updated_by) 
                    VALUES (?, ?, ?, ?, ?, 'delivered', ?)";
    
    $stmt = $conn->prepare($parcelQuery);
    $stmt->bind_param("iisidi", $orderId, $shopId, $parcelNum, $itemCount, $totalAmount, $user['id']);
    $stmt->execute();
    $parcelId = $stmt->insert_id;

    // 8. Insert Items & Reduce Stock
    foreach ($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        
        $itemQuery = "INSERT INTO order_items (order_id, parcel_id, medicine_id, shop_id, medicine_name, quantity, price, subtotal) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($itemQuery);
        $stmt->bind_param("iiiisidd", $orderId, $parcelId, $item['id'], $shopId, $item['name'], $item['quantity'], $item['price'], $itemTotal);
        $stmt->execute();

        // Update Stock
        $stockUpdate = "UPDATE shop_medicines SET stock_quantity = stock_quantity - ? WHERE shop_id = ? AND medicine_id = ?";
        $stmt = $conn->prepare($stockUpdate);
        $stmt->bind_param("iii", $item['quantity'], $shopId, $item['id']);
        $stmt->execute();
    }

    // 9. Handle Points (Deduct & Add)
    if ($userId) {
        $netPointsChange = $pointsEarned - $pointsUsed;
        
        if ($netPointsChange != 0) {
            $ptUpdate = "UPDATE users SET points = points + ? WHERE id = ?";
            $stmt = $conn->prepare($ptUpdate);
            $stmt->bind_param("ii", $netPointsChange, $userId);
            $stmt->execute();
        }

        // Log Points
        if ($pointsUsed > 0) {
            $logStmt = $conn->prepare("INSERT INTO points_log (user_id, order_id, points, type, description) VALUES (?, ?, ?, 'redeem', 'POS Discount')");
            $neg = -$pointsUsed;
            $logStmt->bind_param("iii", $userId, $orderId, $neg);
            $logStmt->execute();
        }
        
        if ($pointsEarned > 0) {
            $logStmt = $conn->prepare("INSERT INTO points_log (user_id, order_id, points, type, description) VALUES (?, ?, ?, 'order', 'POS Purchase')");
            $logStmt->bind_param("iii", $userId, $orderId, $pointsEarned);
            $logStmt->execute();
        }
    }

    // 10. Update Prescription Status
    if ($prescriptionId > 0) {
        $prescUpdate = "UPDATE prescriptions SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($prescUpdate);
        $stmt->bind_param("ii", $user['id'], $prescriptionId);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId, 'message' => 'Sale Successful!']);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    // Return JSON error even if exception occurs
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>