<?php
/**
 * Shopping Cart Page (AJAX Update Without Reload)
 */

require_once 'config.php';

requireLogin();
requireRole('customer');

$pageTitle = 'Shopping Cart - QuickMed';
$userId = $_SESSION['user_id'];

// Get cart items grouped by shop
$cartQuery = "SELECT c.id as cart_id, c.quantity,
              m.id as medicine_id, m.name, m.power, m.form, m.image, m.requires_prescription,
              sm.price, sm.stock_quantity,
              s.id as shop_id, s.name as shop_name, s.city
              FROM cart c
              JOIN medicines m ON c.medicine_id = m.id
              JOIN shop_medicines sm ON c.medicine_id = sm.medicine_id AND c.shop_id = sm.shop_id
              JOIN shops s ON c.shop_id = s.id
              WHERE c.user_id = ?
              ORDER BY s.name, m.name";
$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result();

// Group by shop
$cartByShop = [];
$totalAmount = 0;
$totalItems = 0;

while ($item = $cartItems->fetch_assoc()) {
    $shopId = $item['shop_id'];
    if (!isset($cartByShop[$shopId])) {
        $cartByShop[$shopId] = [
            'shop_name' => $item['shop_name'],
            'city' => $item['city'],
            'items' => [],
            'subtotal' => 0
        ];
    }
    
    $itemTotal = $item['price'] * $item['quantity'];
    $item['item_total'] = $itemTotal;
    
    $cartByShop[$shopId]['items'][] = $item;
    $cartByShop[$shopId]['subtotal'] += $itemTotal;
    $totalAmount += $itemTotal;
    $totalItems += $item['quantity'];
}

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green mb-4 font-mono uppercase">
                🛒 <?= __('your_cart') ?>
            </h1>
            <div class="bg-lime-accent inline-block px-6 py-3 border-4 border-deep-green">
                <p class="text-deep-green font-bold text-xl">
                    <span id="total-items-count"><?= $totalItems ?></span> Items in Your Cart
                </p>
            </div>
        </div>

        <?php if (empty($cartByShop)): ?>
            <div class="card bg-white text-center py-20" data-aos="zoom-in">
                <div class="text-9xl mb-6">🛒</div>
                <h2 class="text-3xl font-bold text-gray-600 mb-6"><?= __('cart_empty') ?></h2>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">
                    🛍️ <?= __('continue_shopping') ?>
                </a>
            </div>
        <?php else: ?>
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <?php foreach ($cartByShop as $shopId => $shopData): ?>
                        <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                            <div class="bg-deep-green text-white px-6 py-4 -mx-5 -mt-5 mb-5 flex justify-between items-center">
                                <div>
                                    <h3 class="text-2xl font-bold">🏪 <?= htmlspecialchars($shopData['shop_name']) ?></h3>
                                    <p class="text-lime-accent">📍 <?= htmlspecialchars($shopData['city']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm">Shop Subtotal</p>
                                    <p class="text-2xl font-bold text-lime-accent">
                                        ৳<span id="shop-subtotal-<?= $shopId ?>"><?= number_format($shopData['subtotal'], 2) ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4" id="shop-items-<?= $shopId ?>">
                                <?php foreach ($shopData['items'] as $item): ?>
                                    <div class="flex gap-4 p-4 border-2 border-gray-200 hover:border-lime-accent transition-all cart-item shop-item-<?= $shopId ?>" 
                                         id="item-row-<?= $item['cart_id'] ?>"
                                         data-price="<?= $item['price'] ?>"
                                         data-shop-id="<?= $shopId ?>">
                                        
                                        <img 
                                            src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" 
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                            class="w-24 h-24 object-contain border-2 border-deep-green bg-gray-50"
                                        >
                                        
                                        <div class="flex-1">
                                            <h4 class="text-xl font-bold text-deep-green mb-2">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </h4>
                                            <p class="text-gray-600 mb-2">
                                                <?= htmlspecialchars($item['power']) ?> | <?= htmlspecialchars($item['form']) ?>
                                            </p>
                                            
                                            <?php if ($item['requires_prescription']): ?>
                                                <span class="badge badge-warning text-xs">⚠️ Prescription Required</span>
                                            <?php endif; ?>
                                            
                                            <div class="flex items-center gap-4 mt-3">
                                                <div class="flex items-center border-2 border-deep-green">
                                                    <button 
                                                        onclick="updateQuantity(<?= $item['cart_id'] ?>, -1, <?= $item['stock_quantity'] ?>)"
                                                        class="px-4 py-2 bg-gray-100 hover:bg-deep-green hover:text-white font-bold text-xl transition-all"
                                                    >-</button>
                                                    <input 
                                                        type="number" 
                                                        value="<?= $item['quantity'] ?>" 
                                                        id="qty-<?= $item['cart_id'] ?>"
                                                        class="w-16 text-center font-bold text-lg border-x-2 border-deep-green py-2 item-qty"
                                                        min="1"
                                                        max="<?= $item['stock_quantity'] ?>"
                                                        onchange="updateQuantityDirect(<?= $item['cart_id'] ?>, this.value, <?= $item['stock_quantity'] ?>)"
                                                    >
                                                    <button 
                                                        onclick="updateQuantity(<?= $item['cart_id'] ?>, 1, <?= $item['stock_quantity'] ?>)"
                                                        class="px-4 py-2 bg-gray-100 hover:bg-deep-green hover:text-white font-bold text-xl transition-all"
                                                    >+</button>
                                                </div>
                                                
                                                <div class="text-right flex-1">
                                                    <p class="text-sm text-gray-500">৳<?= number_format($item['price'], 2) ?> × <span id="display-qty-<?= $item['cart_id'] ?>"><?= $item['quantity'] ?></span></p>
                                                    <p class="text-2xl font-bold text-deep-green">
                                                        ৳<span id="item-total-<?= $item['cart_id'] ?>"><?= number_format($item['item_total'], 2) ?></span>
                                                    </p>
                                                </div>
                                                
                                                <button 
                                                    onclick="removeFromCart(<?= $item['cart_id'] ?>)"
                                                    class="px-4 py-2 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white border-2 border-red-600 font-bold transition-all"
                                                    title="Remove"
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                            
                                            <p class="text-xs text-gray-500 mt-2">
                                                Stock: <?= $item['stock_quantity'] ?> available
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="lg:col-span-1">
                    <div class="card bg-light-green border-4 border-deep-green sticky top-24" data-aos="fade-left">
                        <h3 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                            📋 Order Summary
                        </h3>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-lg">
                                <span>Subtotal (<span id="summary-items-count"><?= $totalItems ?></span> items):</span>
                                <span class="font-bold">৳<span id="global-subtotal"><?= number_format($totalAmount, 2) ?></span></span>
                            </div>
                            
                            <div class="flex justify-between text-lg border-t-2 border-deep-green pt-4">
                                <span>Delivery Charge:</span>
                                <span class="font-bold text-orange-600">+ ৳<?= number_format(HOME_DELIVERY_CHARGE, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between text-2xl font-bold bg-white border-4 border-deep-green px-4 py-4">
                                <span>Total:</span>
                                <span class="text-deep-green">৳<span id="global-total"><?= number_format($totalAmount + HOME_DELIVERY_CHARGE, 2) ?></span></span>
                            </div>
                        </div>
                        
                        <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-primary w-full mb-4 text-xl py-4 neon-border">
                            ✅ <?= __('proceed_checkout') ?>
                        </a>
                        
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline w-full">
                            ← <?= __('continue_shopping') ?>
                        </a>
                        
                        <?php
                        $user = getCurrentUser();
                        $availablePoints = $user['points'] ?? 0;
                        $pointsDiscount = floor($availablePoints / 100) * 10; 
                        ?>
                        
                        <?php if ($availablePoints >= 100): ?>
                            <div class="bg-white border-4 border-lime-accent p-4 mt-4">
                                <p class="font-bold text-deep-green mb-2">💰 Available Points</p>
                                <p class="text-3xl font-bold text-lime-accent mb-2">⭐ <?= $availablePoints ?></p>
                                <p class="text-sm text-gray-600">
                                    You can get ৳<?= $pointsDiscount ?> discount at checkout!
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
const DELIVERY_CHARGE = <?= HOME_DELIVERY_CHARGE ?>;

// Helper function to format currency
function formatMoney(amount) {
    return amount.toFixed(2);
}

// Button Click Handler
function updateQuantity(cartId, change, maxStock) {
    const input = document.getElementById(`qty-${cartId}`);
    let currentQty = parseInt(input.value);
    let newQty = currentQty + change;
    
    validateAndUpdate(cartId, newQty, maxStock, input);
}

// Direct Input Handler
function updateQuantityDirect(cartId, newQty, maxStock) {
    const input = document.getElementById(`qty-${cartId}`);
    validateAndUpdate(cartId, newQty, maxStock, input);
}

// Validation Logic
function validateAndUpdate(cartId, newQty, maxStock, inputElement) {
    newQty = parseInt(newQty);
    
    if (isNaN(newQty) || newQty < 1) newQty = 1;
    
    if (newQty > maxStock) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock Limit',
            text: `Only ${maxStock} items available`,
            confirmButtonColor: '#065f46',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        inputElement.value = maxStock; // Reset to max
        newQty = maxStock;
    } else {
        inputElement.value = newQty; // Update visual input immediately
    }
    
    // Call AJAX
    updateCartQuantity(cartId, newQty);
}

// AJAX Update Function
async function updateCartQuantity(cartId, quantity) {
    try {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        
        const response = await fetch('<?= SITE_URL ?>/ajax/update_cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Success: Update UI without reload
            updateCartVisuals(cartId, quantity);
            
            // Show small success toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: false,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'success',
                title: 'Cart updated'
            });

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message,
                confirmButtonColor: '#065f46'
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Function to Recalculate Totals in DOM
function updateCartVisuals(cartId, newQuantity) {
    // 1. Update Item Total
    const itemRow = document.getElementById(`item-row-${cartId}`);
    const unitPrice = parseFloat(itemRow.dataset.price);
    const newItemTotal = unitPrice * newQuantity;
    
    document.getElementById(`item-total-${cartId}`).textContent = formatMoney(newItemTotal);
    document.getElementById(`display-qty-${cartId}`).textContent = newQuantity;

    // 2. Recalculate Shop Subtotal
    const shopId = itemRow.dataset.shopId;
    let shopSubtotal = 0;
    
    document.querySelectorAll(`.shop-item-${shopId}`).forEach(row => {
        const qty = parseInt(row.querySelector('.item-qty').value);
        const price = parseFloat(row.dataset.price);
        shopSubtotal += (qty * price);
    });
    
    document.getElementById(`shop-subtotal-${shopId}`).textContent = formatMoney(shopSubtotal);

    // 3. Recalculate Global Totals
    let globalSubtotal = 0;
    let totalItems = 0;

    document.querySelectorAll('.cart-item').forEach(row => {
        const qty = parseInt(row.querySelector('.item-qty').value);
        const price = parseFloat(row.dataset.price);
        globalSubtotal += (qty * price);
        totalItems += qty;
    });

    // Update Global Elements
    document.getElementById('global-subtotal').textContent = formatMoney(globalSubtotal);
    document.getElementById('global-total').textContent = formatMoney(globalSubtotal + DELIVERY_CHARGE);
    
    // Update Item Counts
    document.getElementById('total-items-count').textContent = totalItems;
    document.getElementById('summary-items-count').textContent = totalItems;
}

// Remove from cart
async function removeFromCart(cartId) {
    const result = await Swal.fire({
        title: 'Remove Item?',
        text: 'Are you sure you want to remove this item from cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'Cancel'
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('cart_id', cartId);
            
            const response = await fetch('<?= SITE_URL ?>/ajax/remove_cart.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove row immediately
                const row = document.getElementById(`item-row-${cartId}`);
                // Check if it's the last item in shop or cart
                location.reload(); // For removal, reload is safer to clean up empty shops
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonColor: '#065f46'
                });
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>