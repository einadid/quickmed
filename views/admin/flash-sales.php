<?php
/**
 * Manage Flash Sales - Admin & Shop Manager (Unified System)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
$user = getCurrentUser();

// Allow Admin & Shop Manager Only
if (!in_array($user['role_name'], ['admin', 'shop_manager'])) {
    redirect('../../index.php');
}

$pageTitle = 'Manage Flash Sales';
$shopId = $user['shop_id']; // Null for Admin

// =============================================
// HANDLE ADD SALE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sale'])) {
    $medicineId = intval($_POST['medicine_id']);
    $selectedShop = ($user['role_name'] === 'admin') ? intval($_POST['shop_id']) : $shopId;
    $discount = floatval($_POST['discount_percent']);
    $stock = intval($_POST['stock_limit']);
    $expires = clean($_POST['expires_at']);
    
    // Get Original Price
    $priceQuery = $conn->query("SELECT price FROM shop_medicines WHERE medicine_id = $medicineId AND shop_id = $selectedShop");
    
    if ($priceQuery->num_rows > 0) {
        $originalPrice = $priceQuery->fetch_assoc()['price'];
        $salePrice = $originalPrice - ($originalPrice * ($discount / 100));
        
        // Insert Flash Sale
        $stmt = $conn->prepare("INSERT INTO flash_sales (medicine_id, shop_id, discount_percent, original_price, sale_price, stock_limit, starts_at, expires_at, is_active) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 1)");
        $stmt->bind_param("iidddis", $medicineId, $selectedShop, $discount, $originalPrice, $salePrice, $stock, $expires);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Flash Sale launched successfully!';
        } else {
            $_SESSION['error'] = 'Failed to launch sale: ' . $stmt->error;
        }
    } else {
        $_SESSION['error'] = 'Medicine not found in the selected shop.';
    }
    
    // Redirect to prevent resubmission
    echo "<script>window.location.href='flash-sales.php';</script>";
    exit();
}

// =============================================
// HANDLE DELETE / STOP SALE
// =============================================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    if ($user['role_name'] === 'shop_manager') {
        // Manager can only delete own shop sales
        $stmt = $conn->prepare("DELETE FROM flash_sales WHERE id = ? AND shop_id = ?");
        $stmt->bind_param("ii", $id, $shopId);
        $stmt->execute();
    } else {
        // Admin can delete any
        $conn->query("DELETE FROM flash_sales WHERE id = $id");
    }
    
    $_SESSION['success'] = 'Sale stopped successfully.';
    echo "<script>window.location.href='flash-sales.php';</script>";
    exit();
}

// =============================================
// FETCH DATA
// =============================================

// Fetch Sales Query
$whereClause = "1=1";
if ($user['role_name'] === 'shop_manager') {
    $whereClause = "fs.shop_id = $shopId";
}

$salesQuery = "SELECT fs.*, m.name as medicine_name, s.name as shop_name, m.image
               FROM flash_sales fs
               JOIN medicines m ON fs.medicine_id = m.id
               JOIN shops s ON fs.shop_id = s.id
               WHERE $whereClause
               ORDER BY fs.is_active DESC, fs.expires_at ASC";
$sales = $conn->query($salesQuery);

// Fetch Dropdown Data
if ($user['role_name'] === 'shop_manager') {
    // Only medicines in manager's shop
    $medicines = $conn->query("SELECT m.id, m.name FROM medicines m 
                               JOIN shop_medicines sm ON m.id = sm.medicine_id 
                               WHERE sm.shop_id = $shopId AND sm.stock_quantity > 0 
                               ORDER BY m.name");
} else {
    // Admin sees all
    $medicines = $conn->query("SELECT id, name FROM medicines ORDER BY name");
    $shops = $conn->query("SELECT id, name FROM shops WHERE is_active = 1 ORDER BY name");
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">⚡ Flash Sales</h1>
                <p class="text-gray-600">Manage limited-time deals</p>
            </div>
            <div class="flex gap-4">
                <?php if ($user['role_name'] === 'shop_manager'): ?>
                    <a href="../shop_manager/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
                <?php endif; ?>
                
                <button onclick="openAddModal()" class="btn btn-primary shadow-lg hover:scale-105 transition-transform">
                    🚀 Launch New Sale
                </button>
            </div>
        </div>

        <!-- Sales Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($sales->num_rows === 0): ?>
                <div class="col-span-3 text-center py-20 bg-white rounded-xl border-4 border-dashed border-gray-200">
                    <div class="text-6xl mb-4">⚡</div>
                    <p class="text-xl text-gray-500">No active flash sales.</p>
                </div>
            <?php else: ?>
                <?php while ($sale = $sales->fetch_assoc()): 
                    $isExpired = strtotime($sale['expires_at']) < time();
                ?>
                    <div class="card bg-white border-4 <?= $isExpired ? 'border-gray-300 opacity-75' : 'border-lime-accent' ?> p-0 overflow-hidden hover:shadow-xl transition-all group" data-aos="fade-up">
                        <!-- Discount Badge -->
                        <div class="absolute top-0 right-0 bg-red-600 text-white px-4 py-2 font-bold rounded-bl-xl z-10">
                            -<?= $sale['discount_percent'] ?>%
                        </div>

                        <div class="p-6 relative">
                            <!-- Image -->
                            <div class="h-32 flex items-center justify-center bg-gray-50 rounded-lg mb-4">
                                <img src="<?= SITE_URL ?>/uploads/medicines/<?= $sale['image'] ?? 'placeholder.png' ?>" class="h-24 object-contain mix-blend-multiply" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'">
                            </div>

                            <h3 class="font-bold text-xl text-deep-green mb-1"><?= htmlspecialchars($sale['medicine_name']) ?></h3>
                            <p class="text-xs text-gray-500 mb-3">🏪 <?= htmlspecialchars($sale['shop_name']) ?></p>

                            <div class="flex justify-between items-end mb-4">
                                <div>
                                    <span class="text-sm text-gray-400 line-through">৳<?= $sale['original_price'] ?></span>
                                    <p class="text-2xl font-bold text-red-600">৳<?= number_format($sale['sale_price'], 2) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Stock Left</p>
                                    <p class="font-bold text-deep-green"><?= $sale['stock_limit'] - $sale['sold_count'] ?> / <?= $sale['stock_limit'] ?></p>
                                </div>
                            </div>

                            <!-- Timer -->
                            <div class="bg-gray-100 rounded p-2 text-center mb-4">
                                <p class="text-xs font-bold text-gray-500 uppercase">Expires In</p>
                                <p class="font-mono font-bold <?= $isExpired ? 'text-red-500' : 'text-deep-green' ?>">
                                    <?= $isExpired ? 'EXPIRED' : date('d M, h:i A', strtotime($sale['expires_at'])) ?>
                                </p>
                            </div>

                            <a href="?delete=<?= $sale['id'] ?>" onclick="return confirm('Stop this sale?')" class="btn btn-outline w-full border-red-500 text-red-600 hover:bg-red-50">
                                🛑 Stop Sale
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Add Modal -->
<div id="saleModal" class="modal-overlay hidden">
    <div class="modal max-w-lg">
        <div class="modal-header bg-lime-accent">
            <h3 class="text-2xl font-bold text-deep-green">Create Flash Sale</h3>
            <button onclick="closeModal()" class="text-3xl">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <!-- Medicine Selection -->
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-deep-green">Select Medicine</label>
                    <select name="medicine_id" class="input border-2 border-deep-green w-full" required>
                        <option value="">Select...</option>
                        <?php 
                        if ($medicines && $medicines->num_rows > 0) {
                            $medicines->data_seek(0);
                            while($m = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                </div>

                <!-- Shop Selection (Only for Admin) -->
                <?php if ($user['role_name'] === 'admin'): ?>
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-deep-green">Select Shop</label>
                    <select name="shop_id" class="input border-2 border-deep-green w-full" required>
                        <option value="">Select...</option>
                        <?php 
                        if ($shops && $shops->num_rows > 0) {
                            $shops->data_seek(0);
                            while($s = $shops->fetch_assoc()): 
                        ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <?php else: ?>
                    <!-- Hidden Shop ID for Manager -->
                    <input type="hidden" name="shop_id" value="<?= $shopId ?>">
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Discount (%)</label>
                        <input type="number" name="discount_percent" class="input border-2 border-deep-green w-full" required min="1" max="90">
                    </div>
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Stock Limit</label>
                        <input type="number" name="stock_limit" class="input border-2 border-deep-green w-full" required min="1">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="input border-2 border-deep-green w-full" required>
                </div>

                <button type="submit" name="save_sale" class="btn btn-primary w-full py-3 font-bold text-lg shadow-lg">
                    🚀 Launch Now
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    const modal = document.getElementById('saleModal');
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('saleModal');
    modal.classList.add('hidden');
}

// Close on outside click
window.onclick = function(event) {
    const modal = document.getElementById('saleModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>