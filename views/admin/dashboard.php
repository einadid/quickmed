<?php
/**
 * Admin Dashboard - Master Control (Re-designed)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Master Control - Admin';

// --- 1. LIVE STATS LOGIC (From Code 1) ---
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role_id = 1) as total_customers,
    (SELECT COUNT(*) FROM shops WHERE is_active = 1) as active_shops,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders) as total_revenue,
    (SELECT COUNT(*) FROM prescriptions WHERE status = 'pending') as pending_rx,
    (SELECT COUNT(*) FROM contact_messages) as total_messages,
    (SELECT COUNT(*) FROM reviews WHERE is_approved = 0) as pending_reviews";
$stats = $conn->query($statsQuery)->fetch_assoc();

// Profit Calc (From Code 1)
$profitQuery = "SELECT COALESCE(SUM((oi.price - sm.purchase_price) * oi.quantity), 0) as profit 
                FROM order_items oi 
                JOIN shop_medicines sm ON oi.medicine_id = sm.medicine_id AND oi.shop_id = sm.shop_id";
$profit = $conn->query($profitQuery)->fetch_assoc()['profit'];

// Recent Activities (From Code 1)
$recentOrders = $conn->query("SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");

// Calculate Total Pending Tasks for the Alert Card
$totalPendingTasks = $stats['pending_rx'] + $stats['total_messages'] + $stats['pending_reviews'];

include __DIR__ . '/../../includes/header.php';

$dashTitle = 'Master Control';
$dashSubtitle = 'System Overview · ' . date('h:i A | d M Y');
$dashIcon = '👑';
include __DIR__ . '/../../includes/dashnav.php';
?>

<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <div class="grid md:grid-cols-2 gap-5 mb-6" data-aos="fade-up">
            <?php qm_stat('💰', '৳' . number_format($stats['total_revenue']), 'Total Revenue · Lifetime', 'lime'); ?>
            <?php qm_stat('📈', '৳' . number_format($profit), 'Net Profit · Pure Income', ''); ?>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <?php qm_stat('📦', number_format($stats['total_orders']), 'Total Orders', 'blue'); ?>
            <?php qm_stat('👥', number_format($stats['total_customers']), 'Customers', ''); ?>
            <?php qm_stat('🏪', (int)$stats['active_shops'], 'Active Shops', 'amber'); ?>
            <?php qm_stat('🔔', (int)$totalPendingTasks, 'Needs Attention', $totalPendingTasks > 0 ? 'red' : 'lime'); ?>
        </div>

        <h2 class="text-xl font-bold text-[#065f46] mb-5 font-display">🚀 QUICK ACTIONS</h2>
        <div class="qa-grid mb-6" data-aos="fade-up">
            <a href="medicines.php" class="qa-card"><span class="qa-icon">💊</span><span class="qa-label">Medicines</span></a>
            <a href="shops.php" class="qa-card"><span class="qa-icon">🏪</span><span class="qa-label">Shops</span></a>
            <a href="users.php" class="qa-card"><span class="qa-icon">👥</span><span class="qa-label">Users</span></a>
            <a href="codes.php" class="qa-card"><span class="qa-icon">🎫</span><span class="qa-label">Codes</span></a>
            <a href="prescriptions.php" class="qa-card"><span class="qa-icon">📋</span><span class="qa-label">Prescriptions</span>
                <?php if ($stats['pending_rx'] > 0): ?><span class="qa-count"><?= (int)$stats['pending_rx'] ?></span><?php endif; ?>
            </a>
            <a href="reports.php" class="qa-card"><span class="qa-icon">📊</span><span class="qa-label">Reports</span></a>
            <a href="flash-sales.php" class="qa-card"><span class="qa-icon">⚡</span><span class="qa-label">Flash Sales</span></a>
            <a href="messages.php" class="qa-card"><span class="qa-icon">💬</span><span class="qa-label">Messages</span>
                <?php if ($stats['total_messages'] > 0): ?><span class="qa-count"><?= (int)$stats['total_messages'] ?></span><?php endif; ?>
            </a>
            <a href="reviews.php" class="qa-card"><span class="qa-icon">⭐</span><span class="qa-label">Reviews</span>
                <?php if ($stats['pending_reviews'] > 0): ?><span class="qa-count"><?= (int)$stats['pending_reviews'] ?></span><?php endif; ?>
            </a>
            <a href="audit-logs.php" class="qa-card"><span class="qa-icon">🛡️</span><span class="qa-label">Audit Logs</span></a>
        </div>

        <div class="dash-shell" data-aos="fade-up">
            <div class="dash-shell-head">
                <h2>📋 Recent Transactions</h2>
                <a href="reports.php" class="btn btn-lime btn-sm">View All →</a>
            </div>
            <div class="table-wrap" style="border:none;border-radius:0;box-shadow:none">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td class="font-display font-bold text-[#065f46]">#<?= htmlspecialchars($order['order_number']) ?></td>
                                <td><?= htmlspecialchars($order['full_name'] ?: $order['customer_name']) ?></td>
                                <td><?= qm_badge($order['payment_status'] ?? 'pending') ?></td>
                                <td class="font-bold"><?= qm_money($order['total_amount']) ?></td>
                                <td class="text-sm text-gray-500">
                                    <?= date('d M Y', strtotime($order['created_at'])) ?>
                                    <span class="text-xs text-gray-400 block"><?= date('h:i A', strtotime($order['created_at'])) ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>