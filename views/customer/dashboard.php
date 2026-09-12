<?php
/**
 * Customer Dashboard - QuickMed (Redesigned)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('customer');

$pageTitle = 'My Dashboard - QuickMed';
$user = getCurrentUser();
$userId = $_SESSION['user_id'];

// 1. Customer Stats
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
    (SELECT SUM(total_amount) FROM orders WHERE user_id = ?) as total_spent,
    (SELECT COUNT(*) FROM prescriptions WHERE user_id = ?) as prescriptions_uploaded,
    (SELECT COUNT(*) FROM parcels p JOIN orders o ON p.order_id = o.id WHERE o.user_id = ? AND p.status = 'delivered') as delivered_orders";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iiii", $userId, $userId, $userId, $userId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// 2. Spending History (Last 6 Months) for Chart
$chartQuery = "SELECT DATE_FORMAT(created_at, '%M') as month, SUM(total_amount) as spent 
               FROM orders 
               WHERE user_id = ? AND created_at >= DATE(NOW()) - INTERVAL 6 MONTH 
               GROUP BY month 
               ORDER BY created_at ASC";
$chartStmt = $conn->prepare($chartQuery);
$chartStmt->bind_param("i", $userId);
$chartStmt->execute();
$chartResult = $chartStmt->get_result();

$months = [];
$spending = [];
while ($row = $chartResult->fetch_assoc()) {
    $months[] = $row['month'];
    $spending[] = (float)$row['spent'];
}

// 3. Recent Orders with Parcel Status
$recentOrdersQuery = "SELECT o.*, p.status as parcel_status, p.id as parcel_id 
                      FROM orders o 
                      LEFT JOIN parcels p ON o.id = p.order_id 
                      WHERE o.user_id = ? 
                      ORDER BY o.created_at DESC LIMIT 5";
$recentStmt = $conn->prepare($recentOrdersQuery);
$recentStmt->bind_param("i", $userId);
$recentStmt->execute();
$recentOrders = $recentStmt->get_result();

include __DIR__ . '/../../includes/header.php';

$dashTitle = 'My Dashboard';
$dashSubtitle = 'Welcome to your health hub · Member since ' . date('Y', strtotime($user['created_at']));
$dashIcon = '👋';
include __DIR__ . '/../../includes/dashnav.php';
?>

<section class="container mx-auto px-4 py-10 min-h-screen">
    <div class="max-w-6xl mx-auto">

        <!-- Welcome + loyalty strip -->
        <div class="grid lg:grid-cols-3 gap-5 mb-8">
            <div class="card lg:col-span-2 flex items-center gap-5" data-aos="fade-right">
                <?= qm_avatar($user, 'w-20 h-20') ?>
                <div>
                    <h2 class="text-2xl font-bold text-[#065f46]"><span id="greeting">Hello</span>, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h2>
                    <p class="text-gray-500 text-sm">Member ID: <b class="font-mono"><?= htmlspecialchars($user['member_id'] ?? 'N/A') ?></b></p>
                </div>
            </div>
            <div class="card card-green flex items-center justify-between" data-aos="fade-left">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#84cc16]">Loyalty Points</p>
                    <p class="text-3xl font-bold font-display">⭐ <?= number_format($user['points'] ?? 0) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-300">Cash Value</p>
                    <p class="text-xl font-bold text-[#84cc16]">৳<?= floor(($user['points'] ?? 0) / 100) * 10 ?></p>
                </div>
            </div>
        </div>

        <!-- 1. QUICK ACTIONS -->
        <div class="qa-grid mb-10" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/shop.php" class="qa-card"><span class="qa-icon">🛍️</span><span class="qa-label">Shop Medicine</span></a>
            <a href="<?= SITE_URL ?>/prescription-upload.php" class="qa-card"><span class="qa-icon">📋</span><span class="qa-label">Upload Rx</span></a>
            <a href="<?= SITE_URL ?>/my-orders.php" class="qa-card"><span class="qa-icon">📦</span><span class="qa-label">Track Order</span></a>
            <a href="<?= SITE_URL ?>/cart.php" class="qa-card"><span class="qa-icon">🛒</span><span class="qa-label">View Cart</span></a>
        </div>

        <!-- 2. STATS & CHART ROW -->
        <div class="grid lg:grid-cols-3 gap-6 mb-10">
            <div class="space-y-5">
                <?php qm_stat('💰', qm_money($stats['total_spent'] ?? 0), 'Total Spent', ''); ?>
                <?php qm_stat('📦', (int)$stats['total_orders'], 'Total Orders', 'blue'); ?>
                <?php qm_stat('📄', (int)$stats['prescriptions_uploaded'], 'Prescriptions', 'amber'); ?>
            </div>

            <!-- Spending Chart -->
            <div class="card lg:col-span-2" data-aos="zoom-in">
                <div class="card-header">📊 Spending Overview <span class="badge badge-neutral">Last 6 Months</span></div>
                <div class="relative h-64 w-full">
                    <?php if (empty($spending)): ?>
                        <div class="h-full flex items-center justify-center text-gray-400">
                            No spending history yet.
                        </div>
                    <?php else: ?>
                        <canvas id="spendingChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 3. RECENT ORDERS -->
        <div class="dash-shell" data-aos="fade-up">
            <div class="dash-shell-head">
                <h2>📋 Recent Orders</h2>
                <a href="<?= SITE_URL ?>/my-orders.php" class="btn btn-lime btn-sm">View All →</a>
            </div>

            <?php if ($recentOrders->num_rows === 0): ?>
                <div class="p-8 text-center">
                    <div class="text-5xl mb-4 opacity-30">🛒</div>
                    <p class="text-gray-500 font-medium mb-4">You haven't placed any orders yet.</p>
                    <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-sm">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-wrap" style="border:none;border-radius:0;box-shadow:none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td class="font-display font-bold text-[#065f46]">
                                        #<?= htmlspecialchars($order['order_number']) ?>
                                    </td>
                                    <td class="text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="font-bold"><?= qm_money($order['total_amount']) ?></td>
                                    <td><?= qm_badge($order['parcel_status'] ?: 'pending') ?></td>
                                    <td class="text-right">
                                        <a href="<?= SITE_URL ?>/my-orders.php" class="btn btn-outline btn-sm">Details →</a>
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

<script>
// 1. Dynamic Greeting
const hour = new Date().getHours();
const greetingSpan = document.getElementById('greeting');
if (hour < 12) greetingSpan.innerText = 'Good Morning';
else if (hour < 18) greetingSpan.innerText = 'Good Afternoon';
else greetingSpan.innerText = 'Good Evening';

// 2. Spending Chart
<?php if(!empty($spending)): ?>
const ctx = document.getElementById('spendingChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(6, 95, 70, 0.2)');
gradient.addColorStop(1, 'rgba(6, 95, 70, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Monthly Spending (৳)',
            data: <?= json_encode($spending) ?>,
            borderColor: '#065f46',
            backgroundColor: gradient,
            borderWidth: 2,
            pointBackgroundColor: '#84cc16',
            pointBorderColor: '#fff',
            pointRadius: 5,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>