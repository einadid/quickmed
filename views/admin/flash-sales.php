<?php
/**
 * Admin - Manage Flash Sales
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Flash Sales - Admin';

// Handle Add/Edit Sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sale'])) {
    $medicineId = intval($_POST['medicine_id']);
    $shopId = intval($_POST['shop_id']);
    $discountPercent = floatval($_POST['discount_percent']);
    $stockLimit = intval($_POST['stock_limit']);
    $expiresAt = clean($_POST['expires_at']);
    
    // Get original price
    $priceQuery = "SELECT price FROM shop_medicines WHERE medicine_id = ? AND shop_id = ?";
    $stmt = $conn->prepare($priceQuery);
    $stmt->bind_param("ii", $medicineId, $shopId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Medicine not found in selected shop';
    } else {
        $originalPrice = $result->fetch_assoc()['price'];
        $salePrice = $originalPrice - ($originalPrice * ($discountPercent / 100));
        
        // Insert Flash Sale
        $query = "INSERT INTO flash_sales (medicine_id, shop_id, discount_percent, original_price, sale_price, stock_limit, starts_at, expires_at, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 1)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iidddis", $medicineId, $shopId, $discountPercent, $originalPrice, $salePrice, $stockLimit, $expiresAt);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Flash Sale created successfully';
        } else {
            $_SESSION['error'] = 'Failed to create Flash Sale';
        }
    }
    redirect('flash-sales.php');
}

// Handle Delete/Stop Sale
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM flash_sales WHERE id = $id");
    $_SESSION['success'] = 'Flash Sale removed';
    redirect('flash-sales.php');
}

// Get all active sales
$salesQuery = "SELECT fs.*, m.name as medicine_name, s.name as shop_name 
               FROM flash_sales fs
               JOIN medicines m ON fs.medicine_id = m.id
               JOIN shops s ON fs.shop_id = s.id
               ORDER BY fs.created_at DESC";
$sales = $conn->query($salesQuery);

// Get Medicines & Shops for Dropdown
$medicines = $conn->query("SELECT id, name FROM medicines ORDER BY name");
$shops = $conn->query("SELECT id, name FROM shops WHERE is_active = 1");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">⚡ Manage Flash Sales</h1>
            <div class="flex gap-4">
                <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary">+ Add New Sale</button>
            </div>
        </div>

        <!-- Active Sales Table -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Shop</th>
                            <th>Discount</th>
                            <th>Sale Price</th>
                            <th>Stock Left</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sale = $sales->fetch_assoc()): 
                            $isExpired = strtotime($sale['expires_at']) < time();
                        ?>
                            <tr class="<?= $isExpired ? 'bg-gray-100 opacity-60' : '' ?>">
                                <td class="font-bold"><?= htmlspecialchars($sale['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($sale['shop_name']) ?></td>
                                <td class="text-red-600 font-bold"><?= $sale['discount_percent'] ?>% OFF</td>
                                <td class="text-green-600 font-bold">৳<?= number_format($sale['sale_price'], 2) ?></td>
                                <td><?= $sale['stock_limit'] - $sale['sold_count'] ?> / <?= $sale['stock_limit'] ?></td>
                                <td>
                                    <?= date('d M, h:i A', strtotime($sale['expires_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($isExpired): ?>
                                        <span class="badge badge-danger">Expired</span>
                                    <?php elseif ($sale['is_active']): ?>
                                        <span class="badge badge-success animate-pulse">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="deleteSale(<?= $sale['id'] ?>)" class="btn btn-outline btn-sm border-red-500 text-red-600">
                                        🗑️ Stop
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Add Sale Modal -->
<div id="saleModal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header bg-lime-accent">
            <h3 class="text-2xl font-bold text-deep-green">⚡ Create Flash Sale</h3>
            <button onclick="closeModal()" class="text-3xl">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <div class="mb-4">
                    <label class="block font-bold mb-2">Select Medicine</label>
                    <select name="medicine_id" class="input border-4 border-deep-green" required>
                        <option value="">-- Choose Medicine --</option>
                        <?php 
                        $medicines->data_seek(0);
                        while ($m = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-bold mb-2">Select Shop</label>
                    <select name="shop_id" class="input border-4 border-deep-green" required>
                        <option value="">-- Choose Shop --</option>
                        <?php 
                        $shops->data_seek(0);
                        while ($s = $shops->fetch_assoc()): 
                        ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-bold mb-2">Discount (%)</label>
                        <input type="number" name="discount_percent" class="input border-4 border-deep-green" min="1" max="90" required>
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Stock Limit</label>
                        <input type="number" name="stock_limit" class="input border-4 border-deep-green" min="1" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block font-bold mb-2">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="input border-4 border-deep-green" required>
                </div>

                <button type="submit" name="save_sale" class="btn btn-primary w-full py-3 text-xl neon-border">
                    🚀 Launch Sale
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('saleModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('saleModal').classList.add('hidden');
}

function deleteSale(id) {
    Swal.fire({
        title: 'Stop Flash Sale?',
        text: "This will remove the item from flash deals section!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, stop it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?delete=' + id;
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>