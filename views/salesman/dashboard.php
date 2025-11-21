<?php
/**
 * Salesman Dashboard - POS & Orders
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$pageTitle = 'Salesman Dashboard - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned to your account';
    redirect('../../index.php');
}

// Get shop info
$shopQuery = "SELECT * FROM shops WHERE id = ?";
$shopStmt = $conn->prepare($shopQuery);
$shopStmt->bind_param("i", $shopId);
$shopStmt->execute();
$shop = $shopStmt->get_result()->fetch_assoc();

// Today's stats
$today = date('Y-m-d');
$statsQuery = "SELECT 
    COUNT(DISTINCT p.id) as today_orders,
    SUM(p.subtotal) as today_sales,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_today
    FROM parcels p
    WHERE p.shop_id = ? AND DATE(p.created_at) = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("is", $shopId, $today);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// Recent parcels
$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone,
                 COUNT(oi.id) as items_count
                 FROM parcels p
                 JOIN orders o ON p.order_id = o.id
                 LEFT JOIN order_items oi ON p.id = oi.parcel_id
                 WHERE p.shop_id = ?
                 GROUP BY p.id
                 ORDER BY p.created_at DESC
                 LIMIT 10";
$parcelsStmt = $conn->prepare($parcelsQuery);
$parcelsStmt->bind_param("i", $shopId);
$parcelsStmt->execute();
$parcels = $parcelsStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-12" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green mb-2 font-mono uppercase">
                    👨‍💼 Salesman Dashboard
                </h1>
                <p class="text-xl text-gray-600">
                    🏪 <?= htmlspecialchars($shop['name']) ?> - <?= htmlspecialchars($shop['city']) ?>
                </p>
            </div>
            <a href="online-orders.php" class="btn btn-outline btn-lg border-4 border-lime-accent text-deep-green hover:bg-lime-accent w-full py-6 flex flex-col items-center justify-center gap-2 transform hover:scale-105 transition-all shadow-lg">
    <span class="text-4xl">🌐</span>
    <span class="font-bold text-xl">View Online Orders</span>
    <span class="text-sm">Check delivery address & items</span>
</a>
            <a href="<?= SITE_URL ?>/views/salesman/pos.php" class="btn btn-primary btn-lg neon-border">
                🧾 Open POS System
            </a>
            
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <!-- Today's Orders -->
            <div class="card bg-lime-accent border-4 border-deep-green" data-aos="fade-up" data-aos-delay="0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">TODAY'S ORDERS</p>
                        <p class="text-5xl font-bold text-deep-green"><?= $stats['today_orders'] ?? 0 ?></p>
                    </div>
                    <div class="text-6xl">📦</div>
                </div>
            </div>

            <!-- Today's Sales -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">TODAY'S SALES</p>
                        <p class="text-4xl font-bold text-deep-green">৳<?= number_format($stats['today_sales'] ?? 0, 2) ?></p>
                    </div>
                    <div class="text-6xl">💰</div>
                </div>
            </div>

            <!-- Delivered -->
            <div class="card bg-white border-4 border-lime-accent" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">DELIVERED TODAY</p>
                        <p class="text-5xl font-bold text-lime-accent"><?= $stats['delivered_today'] ?? 0 ?></p>
                    </div>
                    <div class="text-6xl">✅</div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📋 Recent Orders
            </h2>

            <?php if ($parcels->num_rows === 0): ?>
                <div class="text-center py-12">
                    <div class="text-8xl mb-4">📦</div>
                    <p class="text-xl text-gray-500">No orders yet</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Parcel #</th>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($parcel = $parcels->fetch_assoc()): ?>
                                <tr>
                                    <td class="font-mono font-bold"><?= htmlspecialchars($parcel['parcel_number']) ?></td>
                                    <td class="font-mono"><?= htmlspecialchars($parcel['order_number']) ?></td>
                                    <td>
                                        <div class="font-bold"><?= htmlspecialchars($parcel['customer_name']) ?></div>
                                        <div class="text-sm text-gray-600"><?= htmlspecialchars($parcel['customer_phone']) ?></div>
                                    </td>
                                    <td><?= $parcel['items_count'] ?></td>
                                    <td class="font-bold">৳<?= number_format($parcel['subtotal'], 2) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'processing' => 'badge-info',
                                            'packed' => 'badge-warning',
                                            'ready' => 'badge-warning',
                                            'out_for_delivery' => 'badge-info',
                                            'delivered' => 'badge-success',
                                            'cancelled' => 'badge-danger'
                                        ];
                                        ?>
                                        <span class="badge <?= $statusColors[$parcel['status']] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, h:i A', strtotime($parcel['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= SITE_URL ?>/views/salesman/parcel-details.php?id=<?= $parcel['id'] ?>" class="btn btn-outline btn-sm">
                                            View
                                        </a>
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