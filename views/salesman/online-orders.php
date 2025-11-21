<?php
/**
 * Salesman - Online Orders View
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman'); // Role Check

$pageTitle = 'Online Orders - Salesman';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('dashboard.php');
}

// Filter by status
$status = clean($_GET['status'] ?? 'all');
$whereClause = "p.shop_id = $shopId AND o.delivery_type = 'home'"; 

if ($status !== 'all') {
    $whereClause .= " AND p.status = '$status'";
}

$ordersQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.customer_address,
                COUNT(oi.id) as items_count
                FROM parcels p
                JOIN orders o ON p.order_id = o.id
                LEFT JOIN order_items oi ON p.id = oi.parcel_id
                WHERE $whereClause
                GROUP BY p.id
                ORDER BY p.created_at DESC";
$orders = $conn->query($ordersQuery);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">📦 Online Orders</h1>
            <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Same Grid Layout as Manager -->
        <div class="grid md:grid-cols-2 gap-6">
            <?php if ($orders->num_rows === 0): ?>
                <div class="col-span-2 text-center py-12 text-gray-500">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-xl">No online orders found</p>
                </div>
            <?php else: ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green hover:shadow-retro-lg transition-all">
                        <div class="flex justify-between items-start mb-4 border-b-2 border-gray-200 pb-2">
                            <div>
                                <h3 class="text-xl font-bold text-deep-green">Order #<?= $order['order_number'] ?></h3>
                                <p class="text-sm text-gray-500">Parcel #<?= $order['parcel_number'] ?></p>
                            </div>
                            <span class="badge badge-info"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span>
                        </div>

                        <div class="mb-4">
                            <p class="font-bold">👤 <?= htmlspecialchars($order['customer_name']) ?></p>
                            <p class="text-sm">📞 <?= htmlspecialchars($order['customer_phone']) ?></p>
                            <div class="mt-2 bg-off-white p-2 border border-gray-300 text-sm">
                                <strong>📍 Address:</strong><br>
                                <?= nl2br(htmlspecialchars($order['customer_address'])) ?>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-4 pt-4 border-t-2 border-gray-200">
                            <div>
                                <p class="text-sm text-gray-600">Items: <strong><?= $order['items_count'] ?></strong></p>
                                <p class="text-lg font-bold text-deep-green">৳<?= number_format($order['subtotal'], 2) ?></p>
                            </div>
                            <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                👁️ View & Process
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>