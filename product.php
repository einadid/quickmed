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

<?php qm_hero($product['name'], trim(($product['power'] ?? '') . ' | ' . ($product['form'] ?? ''), ' |'), $product['category'] ?? 'Medicine', '💊'); ?>

<section class="container mx-auto px-4 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6"><?= qm_back(SITE_URL . '/shop.php', '← Back to Shop') ?></div>
        <div class="card card-pad-lg relative">

        <div class="grid md:grid-cols-2 gap-10">
            <!-- Image -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 flex items-center justify-center">
                <img
                    src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    class="max-h-80 w-full object-contain hover:scale-105 transition-transform duration-500"
                    onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                >
            </div>

            <!-- Info -->
            <div class="flex flex-col justify-center">
                <div class="mb-3 flex flex-wrap gap-2">
                    <span class="badge badge-lime"><?= htmlspecialchars($product['category'] ?? 'General') ?></span>
                    <?php if (!empty($product['form'])): ?>
                        <span class="badge badge-neutral"><?= htmlspecialchars($product['form']) ?></span>
                    <?php endif; ?>
                </div>

                <h2 class="text-3xl font-bold text-[#065f46] mb-1"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-lg text-gray-500 mb-5 font-display"><?= htmlspecialchars($product['power'] ?? '') ?></p>

                <div class="mb-5 space-y-1.5 text-sm text-gray-700 bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p><strong>Generic:</strong> <?= htmlspecialchars($product['generic_name'] ?? '—') ?></p>
                    <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand'] ?? '—') ?></p>
                    <p><strong>Manufacturer:</strong> <?= htmlspecialchars($product['manufacturer'] ?? '—') ?></p>
                    <p><strong>Shop:</strong> <?= htmlspecialchars($product['shop_name']) ?> (<?= htmlspecialchars($product['city'] ?? '') ?>)</p>
                </div>

                <div class="flex flex-wrap items-center gap-4 mb-6">
                    <?php if ($product['price'] > 0): ?>
                        <span class="text-4xl font-bold text-[#065f46] font-display"><?= qm_money($product['price']) ?></span>
                    <?php else: ?>
                        <?= qm_badge('Out of Stock') ?>
                    <?php endif; ?>

                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="badge badge-success">In Stock: <?= (int)$product['stock_quantity'] ?></span>
                    <?php else: ?>
                        <?= qm_badge('Out of Stock') ?>
                    <?php endif; ?>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="flex flex-wrap gap-3">
                        <div class="qty-stepper">
                            <button type="button" onclick="updateQty(-1)">−</button>
                            <input type="number" id="qty" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                            <button type="button" onclick="updateQty(1)">+</button>
                        </div>

                        <button onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, document.getElementById('qty').value)"
                                class="btn btn-primary btn-lg flex-1">
                            🛒 Add to Cart
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($product['requires_prescription']): ?>
                    <div class="alert alert-warning mt-6">
                        <span>⚠️</span>
                        <div><b>Prescription Required.</b><br><span class="text-sm">You must upload a prescription to order this item.</span></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <?php if (!empty($product['description'])): ?>
        <div class="mt-10 pt-6 border-t border-gray-200">
            <h3 class="text-xl font-bold text-[#065f46] mb-3 font-display">DESCRIPTION</h3>
            <p class="text-gray-600 leading-relaxed">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
        </div>
        <?php endif; ?>
        </div>
    </div>
</section>

<script>
function updateQty(change) {
    const input = document.getElementById('qty');
    if (!input) return;
    let val = parseInt(input.value) + change;
    if (isNaN(val) || val < 1) val = 1;
    if (val > parseInt(input.max)) val = parseInt(input.max);
    input.value = val;
}

async function addToCart(medicineId, shopId, quantity) {
    quantity = quantity || 1;
    const siteUrl = '<?= SITE_URL ?>';
    try {
        const formData = new FormData();
        formData.append('medicine_id', medicineId);
        formData.append('shop_id', shopId);
        formData.append('quantity', quantity);
        const response = await fetch(siteUrl + '/ajax/add_to_cart.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            Swal.fire({ icon: 'success', title: 'Added to cart!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, background: '#065f46', color: '#fff' });
            setTimeout(() => { window.location.href = siteUrl + '/cart.php'; }, 900);
        } else if (result.message === 'login_required') {
            Swal.fire({ title: 'Login Required', text: 'Please login to shop', icon: 'warning', showCancelButton: true, confirmButtonText: 'Login', confirmButtonColor: '#065f46' })
            .then((res) => { if (res.isConfirmed) window.location.href = siteUrl + '/login.php'; });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: result.message, confirmButtonColor: '#065f46' });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'System Error', text: 'Please try again.', confirmButtonColor: '#065f46' });
    }
}
</script>

<?php include 'includes/footer.php'; ?>