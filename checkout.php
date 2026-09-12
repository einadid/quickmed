<?php
/**
 * Checkout Page - Responsive Mobile Fix with Overflow Protection
 */

require_once 'config.php';

requireLogin();
requireRole('customer');

$pageTitle = 'Checkout - QuickMed';
$userId = $_SESSION['user_id'];

// Get cart items
$cartQuery = "SELECT c.*, m.name, m.requires_prescription, sm.price, sm.stock_quantity, s.name as shop_name
              FROM cart c
              JOIN medicines m ON c.medicine_id = m.id
              JOIN shop_medicines sm ON c.medicine_id = sm.medicine_id AND c.shop_id = sm.shop_id
              JOIN shops s ON c.shop_id = s.id
              WHERE c.user_id = ?";
$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result();

if ($cartItems->num_rows === 0) {
    $_SESSION['error'] = 'Your cart is empty';
    redirect('cart.php');
}

// Calculate totals
$subtotal = 0;
$requiresPrescription = false;
$cartData = [];

while ($item = $cartItems->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
    if ($item['requires_prescription']) {
        $requiresPrescription = true;
    }
    $cartData[] = $item;
}

// Get user info
$user = getCurrentUser();

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = clean($_POST['customer_name'] ?? '');
    $customerPhone = clean($_POST['customer_phone'] ?? '');
    $customerAddress = clean($_POST['customer_address'] ?? '');
    $deliveryType = $_POST['delivery_type'] ?? 'home';
    $usePoints = isset($_POST['use_points']) ? intval($_POST['use_points']) : 0;
    
    $errors = [];
    
    if (empty($customerName)) $errors[] = 'Name is required';
    if (empty($customerPhone)) $errors[] = 'Phone is required';
    if (empty($customerAddress) && $deliveryType === 'home') $errors[] = 'Address is required for home delivery';
    
    // Check if prescription required items have uploaded prescription
    if ($requiresPrescription) {
        $prescCheck = "SELECT COUNT(*) as count FROM prescriptions 
                        WHERE user_id = ? AND status IN ('pending', 'approved')";
        $prescStmt = $conn->prepare($prescCheck);
        
        if ($prescStmt === false) {
            $errors[] = 'Database error: ' . $conn->error;
        } else {
            $prescStmt->bind_param("i", $userId);
            $prescStmt->execute();
            $prescResult = $prescStmt->get_result();
            
            if ($prescResult && $prescResult->num_rows > 0) {
                $prescCount = $prescResult->fetch_assoc()['count'];
                
                if ($prescCount == 0) {
                    $errors[] = 'Please upload prescription for prescription-required medicines';
                }
            } else {
                $errors[] = 'Database error checking prescriptions';
            }
        }
    }
    
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Calculate delivery charge
            $deliveryCharge = ($deliveryType === 'home') ? HOME_DELIVERY_CHARGE : 0;
            
            // --- FREE DELIVERY LOGIC (PHP) ---
            if ($subtotal >= 1000) {
                $deliveryCharge = 0;
            }
            
            // Calculate points discount
            $pointsDiscount = 0;
            if ($usePoints > 0 && $usePoints <= $user['points']) {
                $pointsDiscount = floor($usePoints / 100) * 10; // 100 points = 10 BDT
                $usePoints = floor($usePoints / 100) * 100; // Round down to nearest 100
            } else {
                $usePoints = 0;
            }
            
            $totalAmount = $subtotal + $deliveryCharge - $pointsDiscount;
            
            if ($totalAmount < MIN_ORDER_AMOUNT) {
                throw new Exception('Minimum order amount is ৳' . MIN_ORDER_AMOUNT);
            }
            
            // Calculate Proportional Points Earned
            $pointsEarned = floor(($totalAmount / 1000) * POINTS_PER_1000_BDT);
            
            // Create order
            $orderNumber = generateOrderNumber();
            $orderQuery = "INSERT INTO orders (user_id, order_number, customer_name, customer_phone, customer_address, 
                           delivery_type, delivery_charge, subtotal, points_used, points_discount, total_amount, points_earned)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $orderStmt = $conn->prepare($orderQuery);
            
            if ($orderStmt === false) {
                throw new Exception('Database error: ' . $conn->error);
            }
            
            $orderStmt->bind_param("isssssddiddi", 
                $userId, $orderNumber, $customerName, $customerPhone, $customerAddress,
                $deliveryType, $deliveryCharge, $subtotal, $usePoints, $pointsDiscount, $totalAmount, $pointsEarned
            );
            $orderStmt->execute();
            $orderId = $orderStmt->insert_id;
            
            // Group cart items by shop and create parcels
            $shopGroups = [];
            foreach ($cartData as $item) {
                $shopId = $item['shop_id'];
                if (!isset($shopGroups[$shopId])) {
                    $shopGroups[$shopId] = [];
                }
                $shopGroups[$shopId][] = $item;
            }
            
            foreach ($shopGroups as $shopId => $items) {
                $parcelSubtotal = 0;
                $itemsCount = count($items);
                
                // Create parcel
                $parcelNumber = generateParcelNumber($orderId, $shopId);
                $parcelQuery = "INSERT INTO parcels (order_id, shop_id, parcel_number, items_count, subtotal, status)
                                VALUES (?, ?, ?, ?, ?, 'processing')";
                $parcelStmt = $conn->prepare($parcelQuery);
                
                if ($parcelStmt === false) {
                    throw new Exception('Database error creating parcel: ' . $conn->error);
                }
                
                // Calculate parcel subtotal first
                foreach ($items as $item) {
                    $parcelSubtotal += $item['price'] * $item['quantity'];
                }
                
                $parcelStmt->bind_param("iisid", $orderId, $shopId, $parcelNumber, $itemsCount, $parcelSubtotal);
                $parcelStmt->execute();
                $parcelId = $parcelStmt->insert_id;
                
                // Add parcel status log
                $logQuery = "INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by)
                             VALUES (?, 'processing', 'Order placed', ?)";
                $logStmt = $conn->prepare($logQuery);
                
                if ($logStmt) {
                    $logStmt->bind_param("ii", $parcelId, $userId);
                    $logStmt->execute();
                }
                
                // Create order items and reduce stock
                foreach ($items as $item) {
                    $itemTotal = $item['price'] * $item['quantity'];
                    
                    $itemQuery = "INSERT INTO order_items (order_id, parcel_id, medicine_id, shop_id, medicine_name, quantity, price, subtotal)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $itemStmt = $conn->prepare($itemQuery);
                    
                    if ($itemStmt === false) {
                        throw new Exception('Database error creating order item: ' . $conn->error);
                    }
                    
                    $itemStmt->bind_param("iiiisidd", 
                        $orderId, $parcelId, $item['medicine_id'], $shopId, $item['name'], 
                        $item['quantity'], $item['price'], $itemTotal
                    );
                    $itemStmt->execute();
                    
                    // Reduce stock
                    $stockQuery = "UPDATE shop_medicines SET stock_quantity = stock_quantity - ? 
                                   WHERE medicine_id = ? AND shop_id = ?";
                    $stockStmt = $conn->prepare($stockQuery);
                    
                    if ($stockStmt) {
                        $stockStmt->bind_param("iii", $item['quantity'], $item['medicine_id'], $shopId);
                        $stockStmt->execute();
                    }
                }
            }
            
            // Update user points
            $newPoints = $user['points'] - $usePoints + $pointsEarned;
            $updatePointsQuery = "UPDATE users SET points = ? WHERE id = ?";
            $updatePointsStmt = $conn->prepare($updatePointsQuery);
            
            if ($updatePointsStmt) {
                $updatePointsStmt->bind_param("ii", $newPoints, $userId);
                $updatePointsStmt->execute();
            }
            
            // Log points transactions
            if ($usePoints > 0) {
                $pointsLogQuery = "INSERT INTO points_log (user_id, order_id, points, type, description)
                                   VALUES (?, ?, ?, 'redeem', 'Points redeemed for order')";
                $pointsLogStmt = $conn->prepare($pointsLogQuery);
                
                if ($pointsLogStmt) {
                    $pointsUsedNegative = -$usePoints;
                    $pointsLogStmt->bind_param("iii", $userId, $orderId, $pointsUsedNegative);
                    $pointsLogStmt->execute();
                }
            }
            
            if ($pointsEarned > 0) {
                $pointsLogQuery = "INSERT INTO points_log (user_id, order_id, points, type, description)
                                   VALUES (?, ?, ?, 'order', 'Points earned from order')";
                $pointsLogStmt = $conn->prepare($pointsLogQuery);
                
                if ($pointsLogStmt) {
                    $pointsLogStmt->bind_param("iii", $userId, $orderId, $pointsEarned);
                    $pointsLogStmt->execute();
                }
            }
            
            // Clear cart
            $clearCartQuery = "DELETE FROM cart WHERE user_id = ?";
            $clearCartStmt = $conn->prepare($clearCartQuery);
            
            if ($clearCartStmt) {
                $clearCartStmt->bind_param("i", $userId);
                $clearCartStmt->execute();
            }
            
            // Log audit
            logAudit('ORDER_PLACED', 'orders', $orderId, null, ['order_number' => $orderNumber, 'total' => $totalAmount]);
            
            $conn->commit();
            
            $_SESSION['success'] = "Order placed successfully! Order Number: $orderNumber. You earned $pointsEarned points!";
            redirect('my-orders.php');
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Order failed: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

include 'includes/header.php';
?>

<?php qm_hero(__('checkout'), 'Complete your order in a few simple steps.', 'Secure Checkout', '💳'); ?>

<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="max-w-6xl mx-auto">

        <form method="POST" action="" class="grid lg:grid-cols-3 gap-6 lg:gap-8">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="lg:col-span-2 space-y-6">
                <div class="card" data-aos="fade-right">
                    <div class="card-header">📋 <?= __('delivery_info') ?></div>
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="label">👤 <?= __('full_name') ?> *</label>
                            <input type="text" name="customer_name" class="input" required value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>
                        <div>
                            <label class="label">📱 <?= __('phone') ?> *</label>
                            <input type="tel" name="customer_phone" class="input" required value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                        <div class="md:col-span-2">
                            <label class="label">📍 <?= __('address') ?> *</label>
                            <textarea name="customer_address" rows="3" class="input" required placeholder="House/Flat, Road, Area, City"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card" data-aos="fade-right" data-aos-delay="100">
                    <div class="card-header">🚚 <?= __('delivery_type') ?></div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_type" value="home" checked class="hidden peer">
                            <div class="border-2 border-gray-200 rounded-xl p-5 peer-checked:bg-[#ecfccb] peer-checked:border-[#84cc16] peer-checked:shadow-lg transition-all h-full hover:border-[#84cc16]">
                                <div class="text-4xl mb-3">🏠</div>
                                <h4 class="text-lg font-bold text-[#065f46] mb-1"><?= __('home_delivery') ?></h4>
                                <p class="text-gray-500 mb-2 text-sm">Delivered to your doorstep</p>
                                <?php if ($subtotal >= 1000): ?>
                                    <span class="badge badge-success">FREE</span>
                                <?php else: ?>
                                    <p class="text-xl font-bold text-[#065f46]">+ <?= qm_money(HOME_DELIVERY_CHARGE) ?></p>
                                <?php endif; ?>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_type" value="pickup" class="hidden peer">
                            <div class="border-2 border-gray-200 rounded-xl p-5 peer-checked:bg-[#ecfccb] peer-checked:border-[#84cc16] peer-checked:shadow-lg transition-all h-full hover:border-[#84cc16]">
                                <div class="text-4xl mb-3">🏪</div>
                                <h4 class="text-lg font-bold text-[#065f46] mb-1"><?= __('store_pickup') ?></h4>
                                <p class="text-gray-500 mb-2 text-sm">Pick up from nearest shop</p>
                                <span class="badge badge-success">FREE</span>
                            </div>
                        </label>
                    </div>
                </div>

                <?php if ($user['points'] >= 100): ?>
                <div class="card card-lime" data-aos="fade-right" data-aos-delay="200">
                    <div class="card-header">⭐ Use Loyalty Points</div>
                    <p class="mb-4">You have <strong class="text-2xl text-[#065f46]"><?= number_format($user['points']) ?></strong> points available</p>
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                        <input
                            type="number"
                            name="use_points"
                            id="usePoints"
                            min="0"
                            max="<?= $user['points'] ?>"
                            step="100"
                            class="input md:flex-1"
                            placeholder="Enter points (multiples of 100)"
                            onchange="calculateDiscount()"
                            onkeyup="calculateDiscount()"
                        >
                        <span id="pointsDiscount" class="text-lg font-bold text-[#065f46]"></span>
                    </div>
                    <p class="form-hint">💡 100 points = ৳10 discount</p>
                </div>
                <?php endif; ?>

                <?php if ($requiresPrescription): ?>
                <div data-aos="fade-right" data-aos-delay="300">
                    <div class="alert alert-warning">
                        <span class="text-2xl">⚠️</span>
                        <div class="flex-1">
                            <b>Prescription Required.</b>
                            <p class="text-sm">Some items in your cart need a valid prescription. Please upload it first.</p>
                            <a href="<?= SITE_URL ?>/prescription-upload.php" class="btn btn-outline btn-sm mt-3">📋 Upload Prescription</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="card card-accent sticky top-24" data-aos="fade-left">
                        <div class="card-header">📦 Order Summary</div>
                        
                        <div class="space-y-3 mb-6 max-h-64 overflow-y-auto custom-scrollbar">
                            <?php foreach ($cartData as $item): ?>
                                <div class="flex gap-3 p-3 bg-gray-50 border border-gray-200 rounded hover:border-lime-accent transition-all">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-sm truncate"><?= htmlspecialchars($item['name']) ?></p>
                                        <p class="text-xs text-gray-600">Qty: <?= $item['quantity'] ?> × ৳<?= number_format($item['price'], 2) ?></p>
                                    </div>
                                    <p class="font-bold text-deep-green whitespace-nowrap">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="space-y-3 mb-6 border-t border-gray-200 pt-4">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span class="font-bold"><?= qm_money($subtotal) ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span>Delivery:</span>
                                <span class="font-bold" id="deliveryCharge"><?= qm_money(HOME_DELIVERY_CHARGE) ?></span>
                            </div>

                            <div class="flex justify-between text-green-700" id="pointsDiscountRow" style="display: none;">
                                <span>Points Discount:</span>
                                <span class="font-bold" id="pointsDiscountAmount">- ৳0.00</span>
                            </div>

                            <div class="flex justify-between text-xl font-bold bg-[#065f46] text-white p-4 rounded-xl mt-2">
                                <span>Total:</span>
                                <span id="grandTotal"><?= qm_money($subtotal + HOME_DELIVERY_CHARGE) ?></span>
                            </div>
                        </div>

                        <div class="bg-[#ecfccb] border border-[#84cc16] p-4 mb-6 rounded-xl text-center">
                            <p class="text-xs font-bold text-[#065f46] mb-1">🎁 POINTS YOU'LL EARN</p>
                            <p class="text-2xl font-bold text-[#065f46]" id="pointsEarn">
                                ⭐ <?= floor(($subtotal + HOME_DELIVERY_CHARGE) / 1000) * POINTS_PER_1000_BDT ?>
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            ✅ PLACE ORDER
                        </button>
                        
                        <div class="text-center mt-4 space-y-2">
                            <p class="text-xs md:text-sm text-gray-600">
                                🔒 Secure Cash on Delivery
                            </p>
                            <p class="text-[10px] md:text-xs text-gray-500">
                                By placing order, you agree to our Terms & Conditions
                            </p>
                        </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
const subtotal = <?= $subtotal ?>;
const homeDeliveryCharge = <?= HOME_DELIVERY_CHARGE ?>;

// Delivery type change
document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        calculateTotal();
    });
});

// Points calculation
function calculateDiscount() {
    const usePoints = parseInt(document.getElementById('usePoints')?.value || 0);
    const validPoints = Math.floor(usePoints / 100) * 100;
    const discount = (validPoints / 100) * 10;
    
    document.getElementById('pointsDiscount').textContent = discount > 0 ? `= ৳${discount.toFixed(2)} off` : '';
    
    if (discount > 0) {
        document.getElementById('pointsDiscountRow').style.display = 'flex';
        document.getElementById('pointsDiscountAmount').textContent = `- ৳${discount.toFixed(2)}`;
    } else {
        document.getElementById('pointsDiscountRow').style.display = 'none';
    }
    
    calculateTotal();
}

function calculateTotal() {
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
    let deliveryCharge = deliveryType === 'home' ? homeDeliveryCharge : 0;
    
    // FREE DELIVERY LOGIC (JS)
    if (subtotal >= 1000) {
        deliveryCharge = 0;
        document.getElementById('deliveryCharge').innerHTML = '<span class="text-green-600 font-bold">FREE</span>';
    } else {
        // Reset to normal amount if pickup or subtotal < 1000
        if(deliveryType === 'home') {
             document.getElementById('deliveryCharge').innerText = '৳' + deliveryCharge.toFixed(2);
        } else {
             document.getElementById('deliveryCharge').innerHTML = '<span class="text-green-600 font-bold">FREE</span>';
        }
    }

    const usePoints = parseInt(document.getElementById('usePoints')?.value || 0);
    
    // Calculate Discount
    const validPoints = Math.floor(usePoints / 100) * 100;
    const pointsDiscount = (validPoints / 100) * 10;
    
    // Calculate Total
    const total = subtotal + deliveryCharge - pointsDiscount;
    document.getElementById('grandTotal').textContent = '৳' + total.toFixed(2);
    
    // Calculate Proportional Points
    const pointsEarn = Math.floor((total / 1000) * <?= POINTS_PER_1000_BDT ?>);
    
    document.getElementById('pointsEarn').textContent = '⭐ ' + pointsEarn;
}

// Initialize on load
calculateTotal();
</script>

<?php include 'includes/footer.php'; ?>