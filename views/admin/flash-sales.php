<?php
/**
 * Manage Flash Sales (Admin & Shop Manager)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

// Get Current User details
$user = getCurrentUser();
$userRole = $user['role'] ?? '';
$userShopId = $user['shop_id'] ?? 0;

// 1. Permission Check: Allow Admin OR Shop Manager
if ($userRole !== 'admin' && $userRole !== 'shop_manager') {
    $_SESSION['error'] = 'Unauthorized access';
    redirect('../../index.php');
}

$pageTitle = 'Manage Flash Sales';

// Determine Dashboard Link based on Role
$dashboardLink = ($userRole === 'admin') 
    ? SITE_URL . '/views/admin/dashboard.php' 
    : SITE_URL . '/views/shop_manager/dashboard.php';

// ==========================================
// HANDLE ADD FLASH SALE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sale'])) {
    $medicineId = intval($_POST['medicine_id']);
    $discountPercent = floatval($_POST['discount_percent']);
    $stockLimit = intval($_POST['stock_limit']);
    $expiresAt = clean($_POST['expires_at']);
    
    // Determine Shop ID: Admin selects from dropdown, Manager is forced to their own shop
    if ($userRole === 'admin') {
        $targetShopId = intval($_POST['shop_id']);
    } else {
        $targetShopId = $userShopId;
    }

    // Get original price from the specific shop inventory
    $priceQuery = "SELECT price, stock_quantity FROM shop_medicines WHERE medicine_id = ? AND shop_id = ?";
    $stmt = $conn->prepare($priceQuery);
    $stmt->bind_param("ii", $medicineId, $targetShopId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Medicine not found in the selected shop inventory.';
    } else {
        $medData = $result->fetch_assoc();
        $originalPrice = $medData['price'];
        $currentStock = $medData['stock_quantity'];

        // Validation: Check if stock limit exceeds available stock
        if ($stockLimit > $currentStock) {
            $_SESSION['error'] = "Stock limit ($stockLimit) cannot exceed current shop stock ($currentStock).";
        } else {
            // Calculate Sale Price
            $salePrice = $originalPrice - ($originalPrice * ($discountPercent / 100));
            
            // Insert Flash Sale
            $query = "INSERT INTO flash_sales (medicine_id, shop_id, discount_percent, original_price, sale_price, stock_limit, starts_at, expires_at, is_active) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 1)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iidddis", $medicineId, $targetShopId, $discountPercent, $originalPrice, $salePrice, $stockLimit, $expiresAt);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Flash Sale created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create Flash Sale: ' . $stmt->error;
            }
        }
    }
    redirect('flash-sales.php');
}

// ==========================================
// HANDLE DELETE / STOP SALE
// ==========================================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Security: Managers can only delete their own sales
    if ($userRole === 'shop_manager') {
        $checkQuery = "SELECT id FROM flash_sales WHERE id = ? AND shop_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $id, $userShopId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $_SESSION['error'] = "Unauthorized action.";
            redirect('flash-sales.php');
        }
    }

    $conn->query("DELETE FROM flash_sales WHERE id = $id");
    $_SESSION['success'] = 'Flash Sale removed';
    redirect('flash-sales.php');
}

// ==========================================
// DATA FETCHING
// ==========================================

// 1. Get Active Sales (Filter by shop if Manager)
$whereClause = "";
if ($userRole === 'shop_manager') {
    $whereClause = "WHERE fs.shop_id = $userShopId";
}

$salesQuery = "SELECT fs.*, m.name as medicine_name, s.name as shop_name 
               FROM flash_sales fs
               JOIN medicines m ON fs.medicine_id = m.id
               JOIN shops s ON fs.shop_id = s.id
               $whereClause
               ORDER BY fs.created_at DESC";
$sales = $conn->query($salesQuery);

// 2. Get Medicines (Global list)
$medicines = $conn->query("SELECT id, name, power FROM medicines ORDER BY name");

// 3. Get Shops (Only needed for Admin dropdown)
$shops = null;
if ($userRole === 'admin') {
    $shops = $conn->query("SELECT id, name, city FROM shops WHERE is_active = 1");
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">⚡ Manage Flash Sales</h1>
                <?php if($userRole === 'shop_manager'): ?>
                    <p class="text-gray-600 mt-2">Create deals for your specific branch.</p>
                <?php endif; ?>
            </div>
            <div class="flex gap-4">
                <a href="<?= $dashboardLink ?>" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary shadow-lg hover:scale-105 transition-transform">
                    + Add New Sale
                </button>
            </div>
        </div>

        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-deep-green text-white">
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
                        <?php if ($sales->num_rows > 0): ?>
                            <?php while ($sale = $sales->fetch_assoc()): 
                                $isExpired = strtotime($sale['expires_at']) < time();
                                $stockLeft = $sale['stock_limit'] - $sale['sold_count'];
                            ?>
                                <tr class="<?= $isExpired ? 'bg-gray-100 opacity-60' : '' ?>">
                                    <td class="font-bold"><?= htmlspecialchars($sale['medicine_name']) ?></td>
                                    <td>
                                        <span class="text-sm font-semibold text-gray-600">
                                            <?= htmlspecialchars($sale['shop_name']) ?>
                                        </span>
                                    </td>
                                    <td class="text-red-600 font-bold text-lg"><?= $sale['discount_percent'] ?>% OFF</td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="line-through text-gray-400 text-xs">৳<?= number_format($sale['original_price'], 2) ?></span>
                                            <span class="text-green-600 font-bold">৳<?= number_format($sale['sale_price'], 2) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <progress class="progress progress-success w-20" value="<?= $stockLeft ?>" max="<?= $sale['stock_limit'] ?>"></progress>
                                            <span class="text-sm font-bold"><?= $stockLeft ?> / <?= $sale['stock_limit'] ?></span>
                                        </div>
                                    </td>
                                    <td class="text-sm">
                                        <?= date('d M, h:i A', strtotime($sale['expires_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-red-100 text-red-800">Expired</span>
                                        <?php elseif ($sale['is_active'] && $stockLeft > 0): ?>
                                            <span class="badge bg-green-100 text-green-800 animate-pulse">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-gray-200 text-gray-800">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick="deleteSale(<?= $sale['id'] ?>)" class="btn btn-outline btn-xs border-red-500 text-red-600 hover:bg-red-500 hover:text-white">
                                            🛑 Stop
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">No active flash sales found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div id="saleModal" class="modal-overlay hidden">
    <div class="modal max-w-lg w-full">
        <div class="modal-header bg-lime-accent">
            <h3 class="text-2xl font-bold text-deep-green">⚡ Create Flash Sale</h3>
            <button onclick="closeModal()" class="text-3xl">&times;</button>
        </div>
        <div class="modal-body p-6">
            <form method="POST">
                
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-deep-green">Select Medicine</label>
                    <select name="medicine_id" class="input border-2 border-deep-green w-full" required>
                        <option value="">-- Choose Medicine --</option>
                        <?php 
                        $medicines->data_seek(0);
                        while ($m = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['name']) ?> <?= $m['power'] ? '('.$m['power'].')' : '' ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">* Ensure this medicine is in stock at the selected shop.</p>
                </div>

                <?php if ($userRole === 'admin'): ?>
                    <div class="mb-4">
                        <label class="block font-bold mb-2 text-deep-green">Select Shop</label>
                        <select name="shop_id" class="input border-2 border-deep-green w-full" required>
                            <option value="">-- Choose Shop --</option>
                            <?php 
                            if ($shops) {
                                $shops->data_seek(0);
                                while ($s = $shops->fetch_assoc()): 
                            ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['city']) ?>)
                                </option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="shop_id" value="<?= $userShopId ?>">
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Discount (%)</label>
                        <input type="number" name="discount_percent" class="input border-2 border-deep-green w-full" min="1" max="90" step="0.1" placeholder="e.g. 20" required>
                    </div>
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Stock Limit</label>
                        <input type="number" name="stock_limit" class="input border-2 border-deep-green w-full" min="1" placeholder="Qty" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="input border-2 border-deep-green w-full" required>
                </div>

                <button type="submit" name="save_sale" class="btn btn-primary w-full py-3 text-xl font-bold shadow-lg">
                    🚀 Launch Sale
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        text: "This will remove the item from the flash deals section immediately!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#10B981',
        confirmButtonText: 'Yes, stop it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?delete=' + id;
        }
    });
}

// Close modal on outside click
window.onclick = function(event) {
    let modal = document.getElementById('saleModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>