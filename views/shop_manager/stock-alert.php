<?php
/**
 * Shop Manager - Stock Alert System
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('shop_manager');

$pageTitle = 'Stock Alerts - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('dashboard.php');
}

// Get low stock items
$lowStockQuery = "SELECT m.*, sm.stock_quantity, sm.reorder_level, sm.price, sm.expiry_date
                  FROM shop_medicines sm
                  JOIN medicines m ON sm.medicine_id = m.id
                  WHERE sm.shop_id = ?
                  AND sm.stock_quantity <= sm.reorder_level
                  ORDER BY sm.stock_quantity ASC";
$stmt = $conn->prepare($lowStockQuery);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$lowStock = $stmt->get_result();

// Get expired/expiring items
$expiryQuery = "SELECT m.*, sm.stock_quantity, sm.expiry_date, sm.price
                FROM shop_medicines sm
                JOIN medicines m ON sm.medicine_id = m.id
                WHERE sm.shop_id = ?
                AND sm.expiry_date IS NOT NULL
                AND sm.expiry_date <= DATE_ADD(NOW(), INTERVAL 90 DAY)
                ORDER BY sm.expiry_date ASC";
$expiryStmt = $conn->prepare($expiryQuery);
$expiryStmt->bind_param("i", $shopId);
$expiryStmt->execute();
$expiring = $expiryStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">⚠️ Stock Alerts</h1>
            <a href="<?= SITE_URL ?>/views/shop_manager/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Low Stock Alerts -->
        <div class="card bg-red-50 border-4 border-red-500 mb-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-red-600 mb-6 uppercase border-b-4 border-red-500 pb-3">
                🔴 Low Stock Items (<?= $lowStock->num_rows ?>)
            </h2>

            <?php if ($lowStock->num_rows === 0): ?>
                <div class="text-center py-12">
                    <div class="text-8xl mb-4">✅</div>
                    <p class="text-xl text-gray-600">All items are well stocked!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Power</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $lowStock->fetch_assoc()): ?>
                                <tr class="<?= $item['stock_quantity'] == 0 ? 'bg-red-100' : '' ?>">
                                    <td class="font-bold"><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['power']) ?></td>
                                    <td>
                                        <span class="text-3xl font-bold text-red-600">
                                            <?= $item['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td><?= $item['reorder_level'] ?></td>
                                    <td>
                                        <?php if ($item['stock_quantity'] == 0): ?>
                                            <span class="badge badge-danger">Out of Stock</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Low Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-bold">৳<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <a href="<?= SITE_URL ?>/views/shop_manager/inventory.php" class="btn btn-outline btn-sm">
                                            📦 Restock
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Expiring Items -->
        <div class="card bg-yellow-50 border-4 border-yellow-500" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-yellow-700 mb-6 uppercase border-b-4 border-yellow-500 pb-3">
                ⏰ Expiring Soon (Next 90 Days) - <?= $expiring->num_rows ?> Items
            </h2>

            <?php if ($expiring->num_rows === 0): ?>
                <div class="text-center py-12">
                    <div class="text-8xl mb-4">✅</div>
                    <p class="text-xl text-gray-600">No items expiring soon</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Power</th>
                                <th>Stock</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $expiring->fetch_assoc()): 
                                $expiryDate = strtotime($item['expiry_date']);
                                $today = strtotime('today');
                                $daysLeft = floor(($expiryDate - $today) / (60 * 60 * 24));
                            ?>
                                <tr class="<?= $daysLeft <= 0 ? 'bg-red-100' : ($daysLeft <= 30 ? 'bg-yellow-100' : '') ?>">
                                    <td class="font-bold"><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['power']) ?></td>
                                    <td class="font-bold"><?= $item['stock_quantity'] ?></td>
                                    <td><?= date('M d, Y', strtotime($item['expiry_date'])) ?></td>
                                    <td>
                                        <span class="text-2xl font-bold <?= $daysLeft <= 0 ? 'text-red-600' : ($daysLeft <= 30 ? 'text-yellow-600' : 'text-gray-600') ?>">
                                            <?= $daysLeft ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($daysLeft <= 0): ?>
                                            <span class="badge badge-danger">Expired</span>
                                        <?php elseif ($daysLeft <= 30): ?>
                                            <span class="badge badge-warning">Expiring Soon</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Monitor</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>