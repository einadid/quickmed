<?php
/**
 * Customer Dashboard
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('customer');

$pageTitle = 'My Dashboard - QuickMed';
$user = getCurrentUser();

// Get customer stats
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
    (SELECT SUM(total_amount) FROM orders WHERE user_id = ?) as total_spent,
    (SELECT COUNT(*) FROM prescriptions WHERE user_id = ?) as prescriptions_uploaded,
    (SELECT COUNT(*) FROM parcels p 
     JOIN orders o ON p.order_id = o.id 
     WHERE o.user_id = ? AND p.status = 'delivered') as delivered_orders";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// Recent orders
$recentOrdersQuery = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$recentStmt = $conn->prepare($recentOrdersQuery);
$recentStmt->bind_param("i", $_SESSION['user_id']);
$recentStmt->execute();
$recentOrders = $recentStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <!-- Welcome Header -->
        <div class="mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green mb-2 font-mono uppercase">
                👋 Welcome Back!
            </h1>
            <p class="text-2xl text-gray-600"><?= htmlspecialchars($user['full_name']) ?></p>
        </div>

        <!-- Live Points Header Section (ADDED) -->
        <div class="bg-gradient-to-r from-deep-green to-emerald-800 text-white p-6 rounded-lg shadow-xl mb-12 relative overflow-hidden border-b-4 border-lime-accent" data-aos="zoom-in">
            <!-- Animated Background Elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full transform translate-x-10 -translate-y-10"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-lime-accent opacity-10 rounded-full transform -translate-x-5 translate-y-5"></div>
            
            <div class="flex justify-between items-center relative z-10">
                <div>
                    <p class="text-lime-accent font-bold text-sm uppercase tracking-wider mb-1">Available Balance</p>
                    <h2 class="text-4xl font-bold flex items-center gap-2">
                        <?= number_format($user['points']) ?> <span class="text-xl">pts</span>
                    </h2>
                    <p class="text-xs text-gray-300 mt-1">Member ID: <span class="font-mono text-white"><?= $user['member_id'] ?? 'N/A' ?></span></p>
                </div>
                
                <div class="text-right">
                    <div class="bg-white/10 backdrop-blur-sm p-3 rounded-lg border border-white/20">
                        <p class="text-xs text-gray-200">Cash Value</p>
                        <p class="text-2xl font-bold text-lime-accent">৳<?= floor($user['points'] / 100) * 10 ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar to Next Reward -->
            <?php 
            $nextReward = ceil(($user['points'] + 1) / 1000) * 1000;
            $progress = ($user['points'] % 1000) / 1000 * 100;
            ?>
            <div class="mt-6">
                <div class="flex justify-between text-xs text-gray-300 mb-1">
                    <span>Progress to next bonus</span>
                    <span><?= $nextReward - $user['points'] ?> pts needed</span>
                </div>
                <div class="w-full bg-black/20 h-2 rounded-full overflow-hidden">
                    <div class="bg-lime-accent h-full transition-all duration-1000" style="width: <?= $progress ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="card bg-lime-accent border-4 border-deep-green" data-aos="fade-up">
                <p class="text-sm font-bold text-deep-green mb-2">LOYALTY POINTS</p>
                <p class="text-5xl font-bold text-deep-green">⭐<?= $user['points'] ?></p>
                <p class="text-sm text-gray-700 mt-2">= ৳<?= floor($user['points'] / 100) * 10 ?> discount</p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL ORDERS</p>
                <p class="text-5xl font-bold text-deep-green"><?= $stats['total_orders'] ?></p>
                <p class="text-sm text-gray-600"><?= $stats['delivered_orders'] ?> delivered</p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="200">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL SPENT</p>
                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['total_spent'] ?? 0, 2) ?></p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="300">
                <p class="text-sm font-bold text-deep-green mb-2">PRESCRIPTIONS</p>
                <p class="text-5xl font-bold text-deep-green"><?= $stats['prescriptions_uploaded'] ?></p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-4 gap-4 mb-12" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary text-center py-6">
                🛍️ Shop Medicines
            </a>
            <a href="<?= SITE_URL ?>/my-orders.php" class="btn btn-outline text-center py-6">
                📦 My Orders
            </a>
            <a href="<?= SITE_URL ?>/prescription-upload.php" class="btn btn-outline text-center py-6">
                📋 Upload Prescription
            </a>
            <a href="<?= SITE_URL ?>/cart.php" class="btn btn-outline text-center py-6">
                🛒 View Cart
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📋 Recent Orders
            </h2>

            <?php if ($recentOrders->num_rows === 0): ?>
                <div class="text-center py-12">
                    <div class="text-8xl mb-4">📦</div>
                    <p class="text-xl text-gray-500 mb-6">No orders yet</p>
                    <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Points</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td class="font-mono font-bold"><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= $order['items_count'] ?? '-' ?></td>
                                    <td class="font-bold">৳<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <?php if ($order['points_earned'] > 0): ?>
                                            <span class="text-green-600">+<?= $order['points_earned'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= SITE_URL ?>/my-orders.php#order-<?= $order['id'] ?>" class="btn btn-outline btn-sm">
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