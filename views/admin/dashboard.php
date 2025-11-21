<?php
/**
 * Admin Dashboard - Complete System Overview
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Admin Dashboard - QuickMed';

// Overall Statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role_id = 1) as total_customers,
    (SELECT COUNT(*) FROM users WHERE role_id IN (2,3) AND is_active = 1) as total_staff,
    (SELECT COUNT(*) FROM medicines) as total_medicines,
    (SELECT COUNT(*) FROM shops WHERE is_active = 1) as active_shops,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT SUM(total_amount) FROM orders) as total_revenue,
    (SELECT COUNT(*) FROM prescriptions WHERE status = 'pending') as pending_prescriptions,
    (SELECT COUNT(*) FROM parcels WHERE status = 'delivered') as delivered_parcels,
    (SELECT COUNT(*) FROM parcels) as total_parcels";
$stats = $conn->query($statsQuery)->fetch_assoc();

// Today's stats
$today = date('Y-m-d');
$todayQuery = "SELECT 
    COUNT(*) as today_orders,
    SUM(total_amount) as today_revenue
    FROM orders
    WHERE DATE(created_at) = ?";
$todayStmt = $conn->prepare($todayQuery);
$todayStmt->bind_param("s", $today);
$todayStmt->execute();
$todayStats = $todayStmt->get_result()->fetch_assoc();

// Recent orders
$recentOrdersQuery = "SELECT o.*, u.full_name
                      FROM orders o
                      LEFT JOIN users u ON o.user_id = u.id
                      ORDER BY o.created_at DESC
                      LIMIT 10";
$recentOrders = $conn->query($recentOrdersQuery);

// Shop performance
$shopPerformanceQuery = "SELECT s.name, s.city,
                         COUNT(p.id) as total_orders,
                         SUM(p.subtotal) as total_sales
                         FROM shops s
                         LEFT JOIN parcels p ON s.id = p.shop_id
                         WHERE s.is_active = 1
                         GROUP BY s.id
                         ORDER BY total_sales DESC";
$shopPerformance = $conn->query($shopPerformanceQuery);

// Low stock across all shops
$lowStockQuery = "SELECT m.name, s.name as shop_name, sm.stock_quantity, sm.reorder_level
                  FROM shop_medicines sm
                  JOIN medicines m ON sm.medicine_id = m.id
                  JOIN shops s ON sm.shop_id = s.id
                  WHERE sm.stock_quantity <= sm.reorder_level
                  ORDER BY sm.stock_quantity ASC
                  LIMIT 10";
$lowStock = $conn->query($lowStockQuery);

// Delivery success rate
$deliveryRate = 0;
if ($stats['total_parcels'] > 0) {
    $deliveryRate = round(($stats['delivered_parcels'] / $stats['total_parcels']) * 100, 1);
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-12" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green mb-2 font-mono uppercase">
                    👑 Admin Dashboard
                </h1>
                <p class="text-xl text-gray-600">Complete System Overview</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Logged in as</p>
                <p class="text-xl font-bold text-deep-green"><?= htmlspecialchars(getCurrentUser()['full_name']) ?></p>
            </div>
        </div>

        <!-- Today's Stats -->
        <div class="grid md:grid-cols-2 gap-6 mb-8" data-aos="fade-up">
            <div class="card bg-lime-accent border-4 border-deep-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">TODAY'S ORDERS</p>
                        <p class="text-5xl font-bold text-deep-green"><?= $todayStats['today_orders'] ?? 0 ?></p>
                    </div>
                    <div class="text-7xl">📦</div>
                </div>
            </div>

            <div class="card bg-white border-4 border-deep-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">TODAY'S REVENUE</p>
                        <p class="text-4xl font-bold text-deep-green">৳<?= number_format($todayStats['today_revenue'] ?? 0, 2) ?></p>
                    </div>
                    <div class="text-7xl">💰</div>
                </div>
            </div>
        </div>

        <!-- Overall Stats Grid -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="0">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL REVENUE</p>
                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['total_revenue'] ?? 0, 2) ?></p>
                <p class="text-sm text-gray-600"><?= $stats['total_orders'] ?> orders</p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-bold text-deep-green mb-2">CUSTOMERS</p>
                <p class="text-4xl font-bold text-deep-green"><?= $stats['total_customers'] ?></p>
                <p class="text-sm text-gray-600">Registered users</p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="200">
                <p class="text-sm font-bold text-deep-green mb-2">ACTIVE SHOPS</p>
                <p class="text-4xl font-bold text-deep-green"><?= $stats['active_shops'] ?></p>
                <p class="text-sm text-gray-600"><?= $stats['total_staff'] ?> staff members</p>
            </div>

            <div class="card bg-white border-4 border-lime-accent" data-aos="fade-up" data-aos-delay="300">
                <p class="text-sm font-bold text-deep-green mb-2">DELIVERY SUCCESS</p>
                <p class="text-4xl font-bold text-lime-accent"><?= $deliveryRate ?>%</p>
                <p class="text-sm text-gray-600"><?= $stats['delivered_parcels'] ?> delivered</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-6 gap-4 mb-12" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/views/admin/medicines.php" class="btn btn-primary text-center py-6">
                💊 Medicines
            </a>
            <a href="<?= SITE_URL ?>/views/admin/shops.php" class="btn btn-outline text-center py-6">
                🏪 Shops
            </a>
            <a href="<?= SITE_URL ?>/views/admin/users.php" class="btn btn-outline text-center py-6">
                👥 Users
            </a>
            <a href="<?= SITE_URL ?>/views/admin/codes.php" class="btn btn-outline text-center py-6">
                🎫 Codes
            </a>
            <a href="<?= SITE_URL ?>/views/admin/prescriptions.php" class="btn btn-outline text-center py-6">
                📋 Prescriptions
                <?php if ($stats['pending_prescriptions'] > 0): ?>
                    <span class="badge badge-danger ml-2"><?= $stats['pending_prescriptions'] ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/views/admin/reports.php" class="btn btn-outline text-center py-6">
                📊 Reports
            </a>
            <a href="<?= SITE_URL ?>/views/admin/flash-sales.php" class="btn btn-outline text-center py-6 border-lime-accent text-deep-green hover:bg-lime-accent">
    ⚡ Flash Sales
</a>
<a href="<?= SITE_URL ?>/views/admin/messages.php" class="btn btn-outline text-center py-6 border-lime-accent text-deep-green hover:bg-lime-accent">
    💬 Messages
</a>
<a href="<?= SITE_URL ?>/views/admin/reviews.php" class="btn btn-outline text-center py-6 border-yellow-500 text-yellow-700 hover:bg-yellow-500 hover:text-white">
    ⭐ Reviews
</a>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    📋 Recent Orders
                </h2>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td class="font-mono"><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= htmlspecialchars($order['full_name'] ?? $order['customer_name']) ?></td>
                                    <td class="font-bold">৳<?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="text-sm"><?= timeAgo($order['created_at']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Shop Performance -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    🏪 Shop Performance
                </h2>

                <div class="space-y-4">
                    <?php while ($shop = $shopPerformance->fetch_assoc()): ?>
                        <div class="border-2 border-gray-200 p-4 hover:border-lime-accent transition-all">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <p class="font-bold text-lg"><?= htmlspecialchars($shop['name']) ?></p>
                                    <p class="text-sm text-gray-600">📍 <?= htmlspecialchars($shop['city']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-deep-green">৳<?= number_format($shop['total_sales'] ?? 0, 2) ?></p>
                                    <p class="text-xs text-gray-500"><?= $shop['total_orders'] ?? 0 ?> orders</p>
                                </div>
                            </div>
                            <div class="bg-gray-200 h-2 border-2 border-gray-300">
                                <?php
                                $maxSales = $stats['total_revenue'] > 0 ? $stats['total_revenue'] : 1;
                                $percentage = ($shop['total_sales'] / $maxSales) * 100;
                                ?>
                                <div class="bg-lime-accent h-full transition-all" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <?php if ($lowStock->num_rows > 0): ?>
        <div class="card bg-red-50 border-4 border-red-500 mt-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-red-600 mb-6 uppercase border-b-4 border-red-500 pb-3">
                ⚠️ Critical Stock Alert
            </h2>

            <div class="grid md:grid-cols-2 gap-4">
                <?php while ($item = $lowStock->fetch_assoc()): ?>
                    <div class="bg-white border-2 border-red-300 p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-red-600"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="text-sm text-gray-600">🏪 <?= htmlspecialchars($item['shop_name']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-red-600"><?= $item['stock_quantity'] ?></p>
                                <p class="text-xs text-gray-500">Min: <?= $item['reorder_level'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>