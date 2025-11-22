<?php
/**
 * Shopping Cart Page (Responsive Fixed & AJAX Enabled)
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

<section class="w-full max-w-[100vw] overflow-x-hidden px-4 py-8 md:py-16 min-h-screen">
    <div class="container mx-auto max-w-6xl">
        
        <div class="text-center mb-8 md:mb-12" data-aos="fade-down">
            <h1 class="text-3xl md:text-5xl font-bold text-deep-green mb-4 font-mono uppercase">
                🛒 <?= __('your_cart') ?>
            </h1>
            <div class="bg-lime-accent inline-block px-4 py-2 md:px-6 md:py-3 border-4 border-deep-green">
                <p class="text-deep-green font-bold text-base md:text-xl">
                    <span id="total-items-count"><?= $totalItems ?></span> Items in Your Cart
                </p>
            </div>
        </div>

        <?php if (empty($cartByShop)): ?>
            <div class="bg-white text-center py-12 md:py-20 rounded-lg shadow-sm mx-auto" data-aos="zoom-in">
                <div class="text-7xl md:text-9xl mb-6">🛒</div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-600 mb-6"><?= __('cart_empty') ?></h2>
                <a href="<?= SITE_URL ?>/shop.php" class="inline-block bg-deep-green text-white px-8 py-3 rounded-lg font-bold hover:bg-lime-accent hover:text-deep-green transition">
                    🛍️ <?= __('continue_shopping') ?>
                </a>
            </div>
        <?php else: ?>
            
            <div class="flex flex-col lg:grid lg:grid-cols-3 gap-8 relative">
                
                <div class="lg:col-span-2 space-y-6 order-1">
                    <?php foreach ($cartByShop as $shopId => $shopData): ?>
                        <div class="bg-white border-2 md:border-4 border-deep-green rounded-lg overflow-hidden shadow-sm" data-aos="fade-up">
                            
                            <div class="bg-deep-green text-white p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div>
                                    <h3 class="text-lg md:text-2xl font-bold">🏪 <?= htmlspecialchars($shopData['shop_name']) ?></h3>
                                    <p class="text-lime-accent text-sm">📍 <?= htmlspecialchars($shopData['city']) ?></p>
                                </div>
                                <div class="w-full sm:w-auto border-t sm:border-t-0 border-white/20 pt-2 sm:pt-0 mt-2 sm:mt-0 flex justify-between sm:block">
                                    <span class="text-xs text-gray-300 sm:block">Shop Subtotal: </span>
                                    <span class="text-xl font-bold text-lime-accent">
                                        ৳<span id="shop-subtotal-<?= $shopId ?>"><?= number_format($shopData['subtotal'], 2) ?></span>
                                    </span>
                                </div>
                            </div>

                            <div class="p-3 md:p-4 space-y-4" id="shop-items-<?= $shopId ?>">
                                <?php foreach ($shopData['items'] as $item): ?>
                                    <div class="flex flex-col sm:flex-row gap-4 p-3 border border-gray-200 rounded-lg hover:border-lime-accent transition-all cart-item shop-item-<?= $shopId ?>" 
                                         id="item-row-<?= $item['cart_id'] ?>"
                                         data-price="<?= $item['price'] ?>"
                                         data-shop-id="<?= $shopId ?>">
                                        
                                        <div class="flex justify-center sm:justify-start flex-shrink-0">
                                            <img 
                                                src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" 
                                                alt="<?= htmlspecialchars($item['name']) ?>"
                                                class="w-24 h-24 object-contain border border-gray-200 rounded bg-gray-50"
                                            >
                                        </div>
                                        
                                        <div class="flex-1 flex flex-col justify-between w-full">
                                            <div>
                                                <div class="flex justify-between items-start">
                                                    <h4 class="text-lg font-bold text-deep-green break-words">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </h4>
                                                    
                                                    <button onclick="removeFromCart(<?= $item['cart_id'] ?>)" class="sm:hidden text-red-500 px-2 -mr-2">
                                                        ✕
                                                    </button>
                                                </div>
                                                
                                                <p class="text-sm text-gray-600">
                                                    <?= htmlspecialchars($item['power']) ?> | <?= htmlspecialchars($item['form']) ?>
                                                </p>
                                                
                                                <?php if ($item['requires_prescription']): ?>
                                                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded mt-1">
                                                        ⚠️ Prescription Required
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="flex flex-wrap items-center justify-between mt-4 gap-3">
                                                
                                                <div class="flex items-center border border-deep-green rounded overflow-hidden h-10">
                                                    <button 
                                                        onclick="updateQuantity(<?= $item['cart_id'] ?>, -1, <?= $item['stock_quantity'] ?>)"
                                                        class="px-3 h-full bg-gray-100 hover:bg-deep-green hover:text-white font-bold text-xl transition-colors active:bg-deep-green active:text-white flex items-center"
                                                    >-</button>
                                                    <input 
                                                        type="number" 
                                                        value="<?= $item['quantity'] ?>" 
                                                        id="qty-<?= $item['cart_id'] ?>"
                                                        class="w-12 h-full text-center font-bold text-base border-x border-deep-green item-qty focus:outline-none appearance-none m-0"
                                                        min="1"
                                                        max="<?= $item['stock_quantity'] ?>"
                                                        onchange="updateQuantityDirect(<?= $item['cart_id'] ?>, this.value, <?= $item['stock_quantity'] ?>)"
                                                    >
                                                    <button 
                                                        onclick="updateQuantity(<?= $item['cart_id'] ?>, 1, <?= $item['stock_quantity'] ?>)"
                                                        class="px-3 h-full bg-gray-100 hover:bg-deep-green hover:text-white font-bold text-xl transition-colors active:bg-deep-green active:text-white flex items-center"
                                                    >+</button>
                                                </div>
                                                
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 hidden sm:block">Price</p>
                                                    <p class="text-xl font-bold text-deep-green">
                                                        ৳<span id="item-total-<?= $item['cart_id'] ?>"><?= number_format($item['item_total'], 2) ?></span>
                                                    </p>
                                                </div>
                                                
                                                <button 
                                                    onclick="removeFromCart(<?= $item['cart_id'] ?>)"
                                                    class="hidden sm:block px-3 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-600 rounded transition-colors ml-auto sm:ml-0"
                                                    title="Remove"
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                            
                                            <p class="text-xs text-gray-400 mt-2">
                                                Stock: <?= $item['stock_quantity'] ?> available
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="lg:col-span-1 order-2">
                    <div class="bg-green-50 border-4 border-deep-green p-6 rounded-lg sticky top-24 shadow-lg w-full" data-aos="fade-left">
                        <h3 class="text-xl font-bold text-deep-green mb-4 uppercase border-b-2 border-deep-green pb-2">
                            📋 Order Summary
                        </h3>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-base text-gray-700">
                                <span>Subtotal (<span id="summary-items-count"><?= $totalItems ?></span> items):</span>
                                <span class="font-bold">৳<span id="global-subtotal"><?= number_format($totalAmount, 2) ?></span></span>
                            </div>
                            
                            <div class="flex justify-between text-base text-gray-700 border-t border-gray-300 pt-2">
                                <span>Delivery Charge:</span>
                                <span class="font-bold text-orange-600">+ ৳<?= number_format(HOME_DELIVERY_CHARGE, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between text-2xl font-bold bg-white border-2 border-deep-green px-3 py-3 mt-2 rounded">
                                <span>Total:</span>
                                <span class="text-deep-green">৳<span id="global-total"><?= number_format($totalAmount + HOME_DELIVERY_CHARGE, 2) ?></span></span>
                            </div>
                        </div>
                        
                        <a href="<?= SITE_URL ?>/checkout.php" class="block w-full bg-deep-green text-white text-center font-bold py-3 rounded hover:bg-lime-accent hover:text-deep-green transition-all uppercase shadow-md mb-3">
                            ✅ Proceed to Checkout
                        </a>
                        
                        <a href="<?= SITE_URL ?>/shop.php" class="block w-full bg-white text-deep-green text-center font-bold py-3 rounded border-2 border-deep-green hover:bg-gray-50 transition-all uppercase">
                            ← Continue Shopping
                        </a>
                        
                        <?php
                        $user = getCurrentUser();
                        $availablePoints = $user['points'] ?? 0;
                        $pointsDiscount = floor($availablePoints / 100) * 10; 
                        ?>
                        
                        <?php if ($availablePoints >= 100): ?>
                            <div class="bg-white border-2 border-lime-accent p-3 mt-4 rounded text-center">
                                <p class="font-bold text-deep-green text-sm mb-1">💰 Available Points</p>
                                <p class="text-2xl font-bold text-lime-accent mb-1">⭐ <?= $availablePoints ?></p>
                                <p class="text-xs text-gray-600">
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
    return amount.toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
    // Update input value visually if direct manipulation didn't already
    document.getElementById(`qty-${cartId}`).value = newQuantity;

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
                location.reload(); 
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