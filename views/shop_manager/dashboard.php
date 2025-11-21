<?php
/**
 * Shop Manager Dashboard
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('shop_manager');

$pageTitle = 'Shop Manager Dashboard - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('../../index.php');
}

// Get shop info
$shopQuery = "SELECT * FROM shops WHERE id = ?";
$shopStmt = $conn->prepare($shopQuery);
$shopStmt->bind_param("i", $shopId);
$shopStmt->execute();
$shop = $shopStmt->get_result()->fetch_assoc();

// Stats
$statsQuery = "SELECT 
    COUNT(DISTINCT p.id) as total_orders,
    SUM(p.subtotal) as total_sales,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_orders,
    (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = ? AND stock_quantity > 0) as active_products,
    (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = ? AND stock_quantity <= reorder_level) as low_stock_items
    FROM parcels p
    WHERE p.shop_id = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iii", $shopId, $shopId, $shopId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// Today's orders
$today = date('Y-m-d');
$todayQuery = "SELECT COUNT(*) as count, SUM(subtotal) as sales
               FROM parcels 
               WHERE shop_id = ? AND DATE(created_at) = ?";
$todayStmt = $conn->prepare($todayQuery);
$todayStmt->bind_param("is", $shopId, $today);
$todayStmt->execute();
$todayStats = $todayStmt->get_result()->fetch_assoc();

// Recent parcels
$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name
                 FROM parcels p
                 JOIN orders o ON p.order_id = o.id
                 WHERE p.shop_id = ?
                 ORDER BY p.created_at DESC
                 LIMIT 10";
$parcelsStmt = $conn->prepare($parcelsQuery);
$parcelsStmt->bind_param("i", $shopId);
$parcelsStmt->execute();
$parcels = $parcelsStmt->get_result();

// Low stock items
$lowStockQuery = "SELECT m.name, m.power, sm.stock_quantity, sm.reorder_level
                  FROM shop_medicines sm
                  JOIN medicines m ON sm.medicine_id = m.id
                  WHERE sm.shop_id = ? AND sm.stock_quantity <= sm.reorder_level
                  ORDER BY sm.stock_quantity ASC
                  LIMIT 10";
$lowStockStmt = $conn->prepare($lowStockQuery);
$lowStockStmt->bind_param("i", $shopId);
$lowStockStmt->execute();
$lowStock = $lowStockStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-12" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green mb-2 font-mono uppercase">
                🏪 Shop Manager Dashboard
            </h1>
            <p class="text-2xl text-gray-600">
                <?= htmlspecialchars($shop['name']) ?> - <?= htmlspecialchars($shop['city']) ?>
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="card bg-lime-accent border-4 border-deep-green" data-aos="fade-up" data-aos-delay="0">
                <p class="text-sm font-bold text-deep-green mb-2">TODAY'S SALES</p>
                <p class="text-4xl font-bold text-deep-green mb-1">৳<?= number_format($todayStats['sales'] ?? 0, 2) ?></p>
                <p class="text-sm text-gray-700"><?= $todayStats['count'] ?? 0 ?> orders today</p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL REVENUE</p>
                <p class="text-4xl font-bold text-deep-green mb-1">৳<?= number_format($stats['total_sales'] ?? 0, 2) ?></p>
                <p class="text-sm text-gray-600"><?= $stats['total_orders'] ?> orders</p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="200">
                <p class="text-sm font-bold text-deep-green mb-2">ACTIVE PRODUCTS</p>
                <p class="text-4xl font-bold text-deep-green mb-1"><?= $stats['active_products'] ?></p>
                <p class="text-sm text-gray-600">In stock</p>
            </div>

            <div class="card bg-white border-4 border-red-500" data-aos="fade-up" data-aos-delay="300">
                <p class="text-sm font-bold text-red-600 mb-2">LOW STOCK ALERT</p>
                <p class="text-4xl font-bold text-red-600 mb-1"><?= $stats['low_stock_items'] ?></p>
                <p class="text-sm text-gray-600">Items need restock</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-4 gap-4 mb-12" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/views/shop_manager/inventory.php" class="btn btn-primary text-center py-6">
                📦 Manage Inventory
            </a>
            <a href="<?= SITE_URL ?>/views/shop_manager/parcels.php" class="btn btn-outline text-center py-6">
                🚚 View Orders
            </a>
            <a href="<?= SITE_URL ?>/views/shop_manager/reports.php" class="btn btn-outline text-center py-6">
                📊 View Reports
            </a>
            <a href="<?= SITE_URL ?>/views/shop_manager/stock-alert.php" class="btn btn-outline text-center py-6 border-red-500 text-red-600">
                ⚠️ Stock Alerts
            </a>
            <a href="online-orders.php" class="btn btn-outline btn-lg border-4 border-lime-accent text-deep-green hover:bg-lime-accent w-full py-6 flex flex-col items-center justify-center gap-2 transform hover:scale-105 transition-all shadow-lg">
    <span class="text-4xl">🌐</span>
    <span class="font-bold text-xl">View Online Orders</span>
    <span class="text-sm">Check delivery address & items</span>
</a>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    📋 Recent Orders
                </h2>

                <?php if ($parcels->num_rows === 0): ?>
                    <div class="text-center py-8 text-gray-500">No orders yet</div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php while ($parcel = $parcels->fetch_assoc()): ?>
                            <div class="border-2 border-gray-200 p-4 hover:border-lime-accent transition-all">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-bold">#<?= htmlspecialchars($parcel['order_number']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($parcel['customer_name']) ?></p>
                                    </div>
                                    <span class="badge badge-info">
                                        <?= ucfirst($parcel['status']) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-deep-green">৳<?= number_format($parcel['subtotal'], 2) ?></span>
                                    <span class="text-xs text-gray-500"><?= timeAgo($parcel['created_at']) ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Low Stock Alert -->
            <div class="card bg-red-50 border-4 border-red-500" data-aos="fade-left">
                <h2 class="text-2xl font-bold text-red-600 mb-6 uppercase border-b-4 border-red-500 pb-3">
                    ⚠️ Low Stock Items
                </h2>

                <?php if ($lowStock->num_rows === 0): ?>
                    <div class="text-center py-8 text-gray-500">All items well stocked!</div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php while ($item = $lowStock->fetch_assoc()): ?>
                            <div class="bg-white border-2 border-red-300 p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-red-600"><?= htmlspecialchars($item['name']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($item['power']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-red-600"><?= $item['stock_quantity'] ?></p>
                                        <p class="text-xs text-gray-500">Min: <?= $item['reorder_level'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>