<?php
/**
 * Admin - Manage Shop Inventory & Prices (With Live Search)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$shopId = intval($_GET['shop_id'] ?? 0);

if (!$shopId) {
    redirect('views/admin/shops.php');
}

// Get Shop Name
$shop = $conn->query("SELECT name, city FROM shops WHERE id = $shopId")->fetch_assoc();

// Handle Price Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $medicineId = intval($_POST['medicine_id']);
    $sellPrice = floatval($_POST['sell_price']);
    $buyPrice = floatval($_POST['buy_price']);
    
    $updateQuery = "UPDATE shop_medicines SET price = ?, purchase_price = ? WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ddii", $sellPrice, $buyPrice, $shopId, $medicineId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Price updated successfully';
    } else {
        $_SESSION['error'] = 'Update failed';
    }
    
    // Redirect to same page to prevent resubmission
    header("Location: shop-inventory.php?shop_id=$shopId");
    exit();
}

// Get Inventory
$query = "SELECT m.id, m.name, m.generic_name, sm.price, sm.purchase_price, sm.stock_quantity 
          FROM shop_medicines sm 
          JOIN medicines m ON sm.medicine_id = m.id 
          WHERE sm.shop_id = ? 
          ORDER BY m.name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$inventory = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-deep-green">Manage Prices</h1>
                <p class="text-gray-600"><?= htmlspecialchars($shop['name']) ?> (<?= htmlspecialchars($shop['city']) ?>)</p>
            </div>
            <div class="flex gap-4 w-full md:w-auto">
                <a href="<?= SITE_URL ?>/views/admin/shops.php" class="btn btn-outline">← Back to Shops</a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 sticky top-20 z-10">
            <div class="relative">
                <span class="absolute left-4 top-3.5 text-gray-400">🔍</span>
                <input type="text" id="inventorySearch" class="w-full pl-12 pr-4 py-3 border-2 border-deep-green rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500 transition-all" placeholder="Search by Medicine Name or Generic Name...">
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card bg-white border-4 border-deep-green overflow-hidden">
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scroll">
                <table class="table w-full" id="inventoryTable">
                    <thead class="sticky top-0 z-10 bg-deep-green text-white">
                        <tr>
                            <th>Medicine Name</th>
                            <th>Generic</th>
                            <th>Stock</th>
                            <th>Buy Price (৳)</th>
                            <th>Sell Price (৳)</th>
                            <th>Profit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($inventory->num_rows === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">No inventory found in this shop.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($item = $inventory->fetch_assoc()): 
                                $profit = $item['price'] - $item['purchase_price'];
                                $profitClass = $profit >= 0 ? 'text-green-600' : 'text-red-600';
                            ?>
                                <tr class="hover:bg-green-50 transition-colors search-item">
                                    <form method="POST">
                                        <input type="hidden" name="medicine_id" value="<?= $item['id'] ?>">
                                        
                                        <td class="font-bold text-deep-green name-cell">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </td>
                                        <td class="text-xs text-gray-600 generic-cell">
                                            <?= htmlspecialchars($item['generic_name']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $item['stock_quantity'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $item['stock_quantity'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <input type="number" name="buy_price" step="0.01" value="<?= $item['purchase_price'] ?>" class="input w-24 text-sm border-gray-300 focus:border-deep-green">
                                        </td>
                                        <td>
                                            <input type="number" name="sell_price" step="0.01" value="<?= $item['price'] ?>" class="input w-24 text-sm border-gray-300 focus:border-deep-green font-bold">
                                        </td>
                                        <td class="font-bold <?= $profitClass ?>">
                                            <?= number_format($profit, 2) ?>
                                        </td>
                                        <td>
                                            <button type="submit" name="update_price" class="btn btn-primary btn-sm shadow-sm hover:shadow-md transition-all">
                                                💾 Save
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
    .custom-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #065f46; border-radius: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
</style>

<script>
// Live Search Function
document.getElementById('inventorySearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.search-item');
    
    rows.forEach(row => {
        const name = row.querySelector('.name-cell').textContent.toLowerCase();
        const generic = row.querySelector('.generic-cell').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || generic.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>