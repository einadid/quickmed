<?php
/**
 * Single Product Details Page
 */

require_once 'config.php';

$productId = intval($_GET['id'] ?? 0);

if (!$productId) {
    redirect('shop.php');
}

// Fetch Product Details
$query = "SELECT m.*, sm.price, sm.stock_quantity, sm.shop_id, s.name as shop_name, s.city 
          FROM medicines m
          JOIN shop_medicines sm ON m.id = sm.medicine_id
          JOIN shops s ON sm.shop_id = s.id
          WHERE m.id = ? AND sm.stock_quantity > 0 AND s.is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    // If not found in shop, show basic info without price/stock
    $baseQuery = "SELECT * FROM medicines WHERE id = ?";
    $stmt = $conn->prepare($baseQuery);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        include 'includes/header.php';
        echo "<div class='text-center py-20'><h1 class='text-4xl font-bold text-red-600'>Product Not Found</h1></div>";
        include 'includes/footer.php';
        exit;
    }
    $product['price'] = 0;
    $product['stock_quantity'] = 0;
    $product['shop_name'] = 'Not Available';
}

$pageTitle = $product['name'] . ' - QuickMed';
include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-5xl mx-auto bg-white border-4 border-deep-green p-8 shadow-2xl relative">
        
        <a href="shop.php" class="absolute top-4 right-4 btn btn-outline">← Back to Shop</a>

        <div class="grid md:grid-cols-2 gap-12">
            <!-- Image -->
            <div class="bg-gray-50 border-2 border-gray-200 p-8 flex items-center justify-center">
                <img 
                    src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>" 
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    class="max-h-80 w-full object-contain transform hover:scale-110 transition-transform duration-500"
                    onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                >
            </div>

            <!-- Info -->
            <div class="flex flex-col justify-center">
                <span class="bg-lime-accent text-deep-green px-3 py-1 rounded-full text-xs font-bold w-max mb-4 uppercase tracking-widest">
                    <?= htmlspecialchars($product['category']) ?>
                </span>
                
                <h1 class="text-4xl font-bold text-deep-green mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                <p class="text-xl text-gray-500 mb-6 font-mono"><?= htmlspecialchars($product['power']) ?> | <?= htmlspecialchars($product['form']) ?></p>
                
                <div class="mb-6 space-y-2 text-sm text-gray-700">
                    <p><strong>Generic:</strong> <?= htmlspecialchars($product['generic_name']) ?></p>
                    <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
                    <p><strong>Manufacturer:</strong> <?= htmlspecialchars($product['manufacturer']) ?></p>
                    <p><strong>Shop:</strong> <?= htmlspecialchars($product['shop_name']) ?> (<?= htmlspecialchars($product['city'] ?? '') ?>)</p>
                </div>

                <div class="flex items-center gap-4 mb-8">
                    <?php if ($product['price'] > 0): ?>
                        <span class="text-4xl font-bold text-deep-green">৳<?= number_format($product['price'], 2) ?></span>
                    <?php else: ?>
                        <span class="text-2xl font-bold text-red-500">Out of Stock</span>
                    <?php endif; ?>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">In Stock: <?= $product['stock_quantity'] ?></span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">Unavailable</span>
                    <?php endif; ?>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="flex gap-4">
                        <div class="flex items-center border-2 border-deep-green rounded-lg">
                            <button onclick="updateQty(-1)" class="px-4 py-2 text-xl hover:bg-gray-100">-</button>
                            <input type="number" id="qty" value="1" min="1" max="<?= $product['stock_quantity'] ?>" class="w-16 text-center border-x-2 border-deep-green py-2 font-bold focus:outline-none">
                            <button onclick="updateQty(1)" class="px-4 py-2 text-xl hover:bg-gray-100">+</button>
                        </div>
                        
                        <button onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, document.getElementById('qty').value)" 
                                class="flex-1 btn btn-primary text-xl shadow-lg transform hover:-translate-y-1 transition-all">
                            🛒 Add to Cart
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($product['requires_prescription']): ?>
                    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <p class="text-sm text-yellow-800 font-bold">⚠️ Prescription Required</p>
                        <p class="text-xs text-yellow-700">You must upload a prescription to order this item.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <div class="mt-12 pt-8 border-t-2 border-gray-200">
            <h3 class="text-2xl font-bold text-deep-green mb-4">Description</h3>
            <p class="text-gray-600 leading-relaxed">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
        </div>
    </div>
</section>

<script>
function updateQty(change) {
    const input = document.getElementById('qty');
    let val = parseInt(input.value) + change;
    if (val < 1) val = 1;
    if (val > parseInt(input.max)) val = parseInt(input.max);
    input.value = val;
}
</script>

<?php include 'includes/footer.php'; ?>