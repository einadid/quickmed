<?php
/**
 * POS System - Point of Sale for Walk-in Customers
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$pageTitle = 'POS System - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('dashboard.php');
}

// Get shop medicines
$medicinesQuery = "SELECT m.*, sm.price, sm.stock_quantity
                   FROM medicines m
                   JOIN shop_medicines sm ON m.id = sm.medicine_id
                   WHERE sm.shop_id = ? AND sm.stock_quantity > 0
                   ORDER BY m.name ASC";
$stmt = $conn->prepare($medicinesQuery);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$medicines = $stmt->get_result();

// Handle POS sale
// Handle POS sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_sale'])) {
    $cartItems = json_decode($_POST['cart_items'], true);
    $customerName = clean($_POST['customer_name'] ?? 'Walk-in Customer');
    $customerPhone = clean($_POST['customer_phone'] ?? '');
    $memberId = clean($_POST['member_id'] ?? '');
    
    if (empty($cartItems)) {
        $_SESSION['error'] = 'Cart is empty';
    } else {
        $conn->begin_transaction();
        
        try {
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            // Calculate points (1 BDT = 1 Point)
            $pointsEarned = floor($subtotal);
            
            // Check if member exists
            $userId = null;
            if (!empty($memberId)) {
                $memberQuery = "SELECT id FROM users WHERE member_id = ? AND role_id = 1";
                $memberStmt = $conn->prepare($memberQuery);
                $memberStmt->bind_param("s", $memberId);
                $memberStmt->execute();
                $memberResult = $memberStmt->get_result();
                if ($memberResult->num_rows > 0) {
                    $userId = $memberResult->fetch_assoc()['id'];
                }
            }
            
            // Use salesman's user_id if no member
            if (!$userId) {
                $userId = $user['id'];
                $pointsEarned = 0; // No points for non-members
            }
            
            // Create order
            $orderNumber = generateOrderNumber();
            $orderQuery = "INSERT INTO orders (user_id, order_number, customer_name, customer_phone, customer_address, 
                          delivery_type, delivery_charge, subtotal, total_amount, payment_method, payment_status, points_earned)
                          VALUES (?, ?, ?, ?, 'POS Sale', 'pickup', 0, ?, ?, 'cod', 'paid', ?)";
            $orderStmt = $conn->prepare($orderQuery);
            $orderStmt->bind_param("isssddi", $userId, $orderNumber, $customerName, $customerPhone, $subtotal, $subtotal, $pointsEarned);
            $orderStmt->execute();
            $orderId = $orderStmt->insert_id();
            
            // Create parcel
            $parcelNumber = generateParcelNumber($orderId, $shopId);
            $parcelQuery = "INSERT INTO parcels (order_id, shop_id, parcel_number, items_count, subtotal, status, updated_by)
                           VALUES (?, ?, ?, ?, ?, 'delivered', ?)";
            $parcelStmt = $conn->prepare($parcelQuery);
            $itemsCount = count($cartItems);
            $parcelStmt->bind_param("iisidi", $orderId, $shopId, $parcelNumber, $itemsCount, $subtotal, $user['id']);
            $parcelStmt->execute();
            $parcelId = $parcelStmt->insert_id();
            
            // Add status log
            $logQuery = "INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by)
                        VALUES (?, 'delivered', 'POS Sale - Instant Delivery', ?)";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("ii", $parcelId, $user['id']);
            $logStmt->execute();
            
            // Add items and reduce stock
            foreach ($cartItems as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                
                $itemQuery = "INSERT INTO order_items (order_id, parcel_id, medicine_id, shop_id, medicine_name, quantity, price, subtotal)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $itemStmt = $conn->prepare($itemQuery);
                $itemStmt->bind_param("iiiisidd", $orderId, $parcelId, $item['id'], $shopId, $item['name'], 
                                     $item['quantity'], $item['price'], $itemTotal);
                $itemStmt->execute();
                
                // Reduce stock
                $stockQuery = "UPDATE shop_medicines SET stock_quantity = stock_quantity - ? 
                              WHERE medicine_id = ? AND shop_id = ?";
                $stockStmt = $conn->prepare($stockQuery);
                $stockStmt->bind_param("iii", $item['quantity'], $item['id'], $shopId);
                $stockStmt->execute();
            }
            
            // Award points to member
            if ($userId && $pointsEarned > 0) {
                // Update user points
                $updatePointsQuery = "UPDATE users SET points = points + ? WHERE id = ?";
                $updatePointsStmt = $conn->prepare($updatePointsQuery);
                $updatePointsStmt->bind_param("ii", $pointsEarned, $userId);
                $updatePointsStmt->execute();
                
                // Log points
                $pointsLogQuery = "INSERT INTO points_log (user_id, order_id, points, type, description)
                                  VALUES (?, ?, ?, 'order', 'POS Purchase')";
                $pointsLogStmt = $conn->prepare($pointsLogQuery);
                $pointsLogStmt->bind_param("iii", $userId, $orderId, $pointsEarned);
                $pointsLogStmt->execute();
            }
            
            logAudit('POS_SALE', 'orders', $orderId, null, ['order_number' => $orderNumber, 'total' => $subtotal]);
            
            $conn->commit();
            
            $successMsg = "Sale completed! Order #$orderNumber";
            if ($pointsEarned > 0) {
                $successMsg .= " | ⭐ Member earned $pointsEarned points!";
            }
            $_SESSION['success'] = $successMsg;
            
            // Open print page in new window
            echo "<script>window.open('" . SITE_URL . "/views/salesman/print-invoice.php?id=$orderId', '_blank', 'width=800,height=600');</script>";
            echo "<script>setTimeout(function(){ window.location.href = 'pos.php'; }, 100);</script>";
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Sale failed: ' . $e->getMessage();
        }
    }
}

// Include header
include __DIR__ . '/../../includes/header.php';
?>

<!-- Print CSS -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printArea, #printArea * {
        visibility: visible;
    }
    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">
                🧾 POS System
            </h1>
            <a href="<?= SITE_URL ?>/views/salesman/dashboard.php" class="btn btn-outline">
                ← Back to Dashboard
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Product Search & List -->
            <div class="lg:col-span-2">
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                    <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                        🔍 Search Medicine
                    </h2>

                    <!-- Search -->
                    <input 
                        type="text" 
                        id="posSearch" 
                        class="input border-4 border-deep-green mb-6 text-lg" 
                        placeholder="Type medicine name..."
                        autocomplete="off"
                    >

                    <!-- Products Grid -->
                    <div id="productsGrid" class="grid md:grid-cols-2 gap-4 max-h-[600px] overflow-y-auto">
                        <?php $medicines->data_seek(0); while ($med = $medicines->fetch_assoc()): ?>
                            <div class="product-item border-4 border-gray-200 p-4 hover:border-lime-accent transition-all cursor-pointer"
                                 data-id="<?= $med['id'] ?>"
                                 data-name="<?= htmlspecialchars($med['name']) ?>"
                                 data-power="<?= htmlspecialchars($med['power']) ?>"
                                 data-price="<?= $med['price'] ?>"
                                 data-stock="<?= $med['stock_quantity'] ?>"
                                 onclick="addToPOSCart(this)">
                                <div class="flex items-center gap-4">
                                    <img 
                                        src="<?= SITE_URL ?>/uploads/medicines/<?= $med['image'] ?? 'placeholder.png' ?>" 
                                        alt="<?= htmlspecialchars($med['name']) ?>"
                                        class="w-16 h-16 object-contain border-2 border-deep-green"
                                    >
                                    <div class="flex-1">
                                        <h4 class="font-bold text-deep-green"><?= htmlspecialchars($med['name']) ?></h4>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($med['power']) ?></p>
                                        <p class="text-lg font-bold text-lime-accent">৳<?= number_format($med['price'], 2) ?></p>
                                        <p class="text-xs text-gray-500">Stock: <?= $med['stock_quantity'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- POS Cart -->
            <div class="lg:col-span-1">
                <div class="card bg-lime-accent border-4 border-deep-green sticky top-24" data-aos="fade-left">
                    <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                        🛒 Current Sale
                    </h2>

                    <!-- Customer Info -->
                    <div class="mb-6 space-y-3">
                        <input 
                            type="text" 
                            id="memberIdInput" 
                            class="input border-4 border-deep-green" 
                            placeholder="Member ID (for points)"
                            onblur="checkMemberId(this.value)"
                        >
                        <div id="memberInfo" class="hidden bg-lime-accent p-3 border-2 border-deep-green">
                            <p class="font-bold text-deep-green" id="memberName"></p>
                            <p class="text-sm" id="memberPoints"></p>
                        </div>
                        
                        <input 
                            type="text" 
                            id="customerName" 
                            class="input border-4 border-deep-green" 
                            placeholder="Customer Name"
                        >
                        <input 
                            type="tel" 
                            id="customerPhone" 
                            class="input border-4 border-deep-green" 
                            placeholder="Phone (Optional)"
                        >
                    </div>

                    <!-- Cart Items -->
                    <div id="posCartItems" class="mb-6 max-h-64 overflow-y-auto space-y-2">
                        <div class="text-center text-gray-600 py-8">
                            Cart is empty
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-white border-4 border-deep-green p-4 mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg">Subtotal:</span>
                            <span class="text-2xl font-bold text-deep-green" id="posSubtotal">৳0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-3xl font-bold">
                            <span>Total:</span>
                            <span class="text-deep-green" id="posTotal">৳0.00</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <button 
                        onclick="completeSale()" 
                        id="completeSaleBtn"
                        class="btn btn-primary w-full mb-3 text-xl py-4 neon-border"
                        disabled
                    >
                        💰 Complete Sale
                    </button>

                    <button 
                        onclick="clearPOSCart()" 
                        class="btn btn-outline w-full border-deep-green text-deep-green"
                    >
                        🗑️ Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
// Check Member ID
let currentMemberId = null;
let memberData = null;

async function checkMemberId(memberId) {
    if (!memberId || memberId.trim() === '') {
        document.getElementById('memberInfo').classList.add('hidden');
        currentMemberId = null;
        memberData = null;
        return;
    }

    try {
        const siteUrl = window.location.origin + '/quickmed';
        const response = await fetch(siteUrl + '/ajax/check_member.php?member_id=' + encodeURIComponent(memberId));
        const result = await response.json();

        if (result.success) {
            memberData = result.member;
            currentMemberId = memberId;
            document.getElementById('memberName').textContent = '✅ ' + result.member.full_name;
            document.getElementById('memberPoints').textContent = '⭐ Current Points: ' + result.member.points;
            document.getElementById('memberInfo').classList.remove('hidden');
            
            // Auto-fill name if customer name is empty
            if (!document.getElementById('customerName').value) {
                document.getElementById('customerName').value = result.member.full_name;
            }
            if (!document.getElementById('customerPhone').value && result.member.phone) {
                document.getElementById('customerPhone').value = result.member.phone;
            }
        } else {
            document.getElementById('memberInfo').classList.add('hidden');
            currentMemberId = null;
            memberData = null;
            Swal.fire({
                icon: 'warning',
                title: 'Member Not Found',
                text: 'No member found with this ID',
                confirmButtonColor: '#065f46'
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Update completeSale function
async function completeSale() {
    if (posCart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Cart',
            text: 'Please add items to cart',
            confirmButtonColor: '#065f46'
        });
        return;
    }

    const memberIdInput = document.getElementById('memberIdInput').value.trim();
    const customerName = document.getElementById('customerName').value || 'Walk-in Customer';
    const customerPhone = document.getElementById('customerPhone').value;
    const total = calculatePOSTotal();

    // Calculate points to earn (1 BDT = 1 Point)
    const pointsToEarn = Math.floor(total);

    let confirmHtml = `
        <div class="text-left">
            <p class="mb-2"><strong>Customer:</strong> ${customerName}</p>
            ${customerPhone ? `<p class="mb-2"><strong>Phone:</strong> ${customerPhone}</p>` : ''}
            ${memberIdInput ? `<p class="mb-2 text-lime-accent"><strong>⭐ Member ID:</strong> ${memberIdInput}</p>` : ''}
            ${memberIdInput ? `<p class="mb-2 text-lime-accent"><strong>Points to Earn:</strong> ${pointsToEarn}</p>` : ''}
            <p class="mb-2"><strong>Items:</strong> ${posCart.length}</p>
            <p class="text-2xl font-bold text-green-600">Total: ৳${total.toFixed(2)}</p>
        </div>
    `;

    const result = await Swal.fire({
        title: 'Complete Sale?',
        html: confirmHtml,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, complete sale!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="complete_sale" value="1">
            <input type="hidden" name="customer_name" value="${customerName}">
            <input type="hidden" name="customer_phone" value="${customerPhone}">
            <input type="hidden" name="member_id" value="${memberIdInput}">
            <input type="hidden" name="cart_items" value='${JSON.stringify(posCart)}'>
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<script src="<?= SITE_URL ?>/assets/js/pos.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
