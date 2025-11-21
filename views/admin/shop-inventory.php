<?php
/**
 * Admin - Manage Shop Inventory & Prices
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$shopId = intval($_GET['shop_id'] ?? 0);

if (!$shopId) {
    // Show Shop List if no shop selected
    redirect('views/admin/shops.php');
}

// Handle Price Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $medicineId = intval($_POST['medicine_id']);
    $sellPrice = floatval($_POST['sell_price']);
    $buyPrice = floatval($_POST['buy_price']);
    
    $updateQuery = "UPDATE shop_medicines SET price = ?, purchase_price = ? WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ddii", $sellPrice, $buyPrice, $shopId, $medicineId);
    $stmt->execute();
    
    redirect("views/admin/shop-inventory.php?shop_id=$shopId");
}

// Get Inventory
$query = "SELECT m.id, m.name, sm.price, sm.purchase_price, sm.stock_quantity 
          FROM shop_medicines sm 
          JOIN medicines m ON sm.medicine_id = m.id 
          WHERE sm.shop_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$inventory = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-deep-green mb-6">Manage Prices (Shop ID: <?= $shopId ?>)</h1>
    
    <div class="card bg-white border-4 border-deep-green">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Stock</th>
                    <th>Purchase Price</th>
                    <th>Selling Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $inventory->fetch_assoc()): ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="medicine_id" value="<?= $item['id'] ?>">
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['stock_quantity'] ?></td>
                            <td>
                                <input type="number" name="buy_price" step="0.01" value="<?= $item['purchase_price'] ?>" class="input w-24">
                            </td>
                            <td>
                                <input type="number" name="sell_price" step="0.01" value="<?= $item['price'] ?>" class="input w-24">
                            </td>
                            <td>
                                <button type="submit" name="update_price" class="btn btn-primary btn-sm">Update</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>