<?php
// 1. ERROR HANDLING & HEADERS (গুরুত্বপূর্ণ)
error_reporting(0); // সার্ভারের ওয়ার্নিং হাইড করুন
ini_set('display_errors', 0);
header('Content-Type: application/json'); // ব্রাউজারকে বলুন এটা JSON ডাটা

try {
    // 2. CONFIGURATION LOAD
    // পাথ চেক করে লোড করা হচ্ছে
    if (file_exists(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/../config.php';
    } else {
        throw new Exception("Config file missing.");
    }

    // 3. AUTHENTICATION CHECK
    if (!function_exists('isLoggedIn')) {
        // যদি config ফাইলে ফাংশন না থাকে
        throw new Exception("System Error: Auth functions not loaded.");
    }

    if (!isLoggedIn()) {
        throw new Exception('login_required');
    }

    $user = getCurrentUser();
    // Role Check (নিরাপদ ভাবে)
    $role = isset($user['role_name']) ? $user['role_name'] : '';
    if ($role !== 'customer') {
        throw new Exception('Only customers can add items to cart.');
    }

    // 4. INPUT VALIDATION
    $medicineId = isset($_POST['medicine_id']) ? intval($_POST['medicine_id']) : 0;
    $shopId = isset($_POST['shop_id']) ? intval($_POST['shop_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($medicineId <= 0 || $shopId <= 0 || $quantity <= 0) {
        throw new Exception('Invalid product or quantity.');
    }

    // 5. CHECK STOCK AVAILABILITY
    $stockStmt = $conn->prepare("SELECT stock_quantity FROM shop_medicines WHERE medicine_id = ? AND shop_id = ?");
    if (!$stockStmt) throw new Exception("Database Error: " . $conn->error);
    
    $stockStmt->bind_param("ii", $medicineId, $shopId);
    $stockStmt->execute();
    $stockRes = $stockStmt->get_result();

    if ($stockRes->num_rows === 0) {
        $stockStmt->close();
        throw new Exception('This item is not available in this shop.');
    }

    $stockData = $stockRes->fetch_assoc();
    $currentStock = $stockData['stock_quantity'];
    $stockStmt->close();

    // 6. ADD TO CART LOGIC
    // কার্টে আগে থেকে আছে কিনা চেক করা
    $checkCart = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND medicine_id = ? AND shop_id = ?");
    $checkCart->bind_param("iii", $user['id'], $medicineId, $shopId);
    $checkCart->execute();
    $cartRes = $checkCart->get_result();
    $checkCart->close();

    if ($cartRes->num_rows > 0) {
        // UPDATE EXISTING ITEM
        $cartItem = $cartRes->fetch_assoc();
        $newTotalQuantity = $cartItem['quantity'] + $quantity;

        // স্টক লিমিট চেক (আগের + নতুন)
        if ($newTotalQuantity > $currentStock) {
            throw new Exception("Stock limit reached! You already have {$cartItem['quantity']} in cart. Max available: $currentStock");
        }

        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newTotalQuantity, $cartItem['id']);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update cart.");
        }
        $updateStmt->close();

    } else {
        // INSERT NEW ITEM
        if ($quantity > $currentStock) {
            throw new Exception("Out of stock! Only $currentStock items available.");
        }

        $insertStmt = $conn->prepare("INSERT INTO cart (user_id, medicine_id, shop_id, quantity) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("iiii", $user['id'], $medicineId, $shopId, $quantity);
        
        if (!$insertStmt->execute()) {
            throw new Exception("Failed to add to cart.");
        }
        $insertStmt->close();
    }

    // 7. GET UPDATED CART COUNT
    $countStmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $countStmt->bind_param("i", $user['id']);
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $cartCount = 0;
    
    if ($row = $countRes->fetch_assoc()) {
        $cartCount = $row['total'] ?? 0;
    }
    $countStmt->close();

    // 8. SUCCESS RESPONSE
    echo json_encode([
        'success' => true, 
        'message' => 'Added to cart successfully!', 
        'cart_count' => (int)$cartCount
    ]);

} catch (Exception $e) {
    // 9. ERROR RESPONSE
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>