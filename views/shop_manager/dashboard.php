<?php
/**
 * Shop Manager Dashboard - QuickMed (Redesigned)
 * Updated: Removed POS System Link
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('shop_manager');

$pageTitle = 'Dashboard - QuickMed Manager';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('../../index.php');
}

// --- DATA FETCHING ---

// 1. Get Shop Info
$shopStmt = $conn->prepare("SELECT * FROM shops WHERE id = ?");
$shopStmt->bind_param("i", $shopId);
$shopStmt->execute();
$shop = $shopStmt->get_result()->fetch_assoc();

// 2. Key Metrics (Stats)
$statsQuery = "SELECT 
    COUNT(DISTINCT p.id) as total_orders,
    SUM(p.subtotal) as total_sales,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_orders,
    (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = ? AND stock_quantity > 0) as active_products,
    (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = ? AND stock_quantity <= reorder_level) as low_stock_items
    FROM parcels p WHERE p.shop_id = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iii", $shopId, $shopId, $shopId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// 3. Today's Performance
$today = date('Y-m-d');
$todayStmt = $conn->prepare("SELECT COUNT(*) as count, SUM(subtotal) as sales FROM parcels WHERE shop_id = ? AND DATE(created_at) = ?");
$todayStmt->bind_param("is", $shopId, $today);
$todayStmt->execute();
$todayStats = $todayStmt->get_result()->fetch_assoc();

// 4. Chart Data (Last 7 Days Sales)
$chartQuery = "SELECT DATE(created_at) as date, SUM(subtotal) as sales 
               FROM parcels 
               WHERE shop_id = ? AND created_at >= DATE(NOW()) - INTERVAL 7 DAY 
               GROUP BY DATE(created_at) 
               ORDER BY date ASC";
$chartStmt = $conn->prepare($chartQuery);
$chartStmt->bind_param("i", $shopId);
$chartStmt->execute();
$chartResult = $chartStmt->get_result();

$chartLabels = [];
$chartData = [];
while ($row = $chartResult->fetch_assoc()) {
    $chartLabels[] = date('d M', strtotime($row['date']));
    $chartData[] = (float)$row['sales'];
}

// 5. Recent Orders
$parcelsStmt = $conn->prepare("SELECT p.*, o.order_number, o.customer_name FROM parcels p JOIN orders o ON p.order_id = o.id WHERE p.shop_id = ? ORDER BY p.created_at DESC LIMIT 5");
$parcelsStmt->bind_param("i", $shopId);
$parcelsStmt->execute();
$parcels = $parcelsStmt->get_result();

// 6. Low Stock Items
$lowStockStmt = $conn->prepare("SELECT m.name, m.power, sm.stock_quantity, sm.reorder_level FROM shop_medicines sm JOIN medicines m ON sm.medicine_id = m.id WHERE sm.shop_id = ? AND sm.stock_quantity <= sm.reorder_level ORDER BY sm.stock_quantity ASC LIMIT 5");
$lowStockStmt->bind_param("i", $shopId);
$lowStockStmt->execute();
$lowStock = $lowStockStmt->get_result();

include __DIR__ . '/../../includes/header.php';

$dashTitle = 'Shop Manager';
$dashSubtitle = '📍 ' . $shop['name'] . ' · ' . $shop['city'] . ' · ' . date('l, d F Y');
$dashIcon = '🏥';
include __DIR__ . '/../../includes/dashnav.php';
?>

<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <?php qm_stat('💰', qm_money($todayStats['sales'] ?? 0), "Today's Revenue", 'lime'); ?>
            <?php qm_stat('📈', qm_money($stats['total_sales'] ?? 0), 'Total · ' . (int)$stats['total_orders'] . ' orders', ''); ?>
            <?php qm_stat('📦', (int)$stats['active_products'], 'Active Products', 'blue'); ?>
            <?php qm_stat('⚠️', (int)$stats['low_stock_items'], 'Low Stock Items', 'rose'); ?>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-10">
            <div class="card lg:col-span-2" data-aos="zoom-in">
                <div class="card-header">📊 Sales Overview <span class="badge badge-neutral">Last 7 Days</span></div>
                <div class="relative h-72 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="card" data-aos="fade-left">
                <div class="card-header">⚡ Quick Actions</div>
                <div class="qa-grid qa-grid-2">
                    <a href="inventory.php" class="qa-card"><span class="qa-icon">💊</span><span class="qa-label">Inventory</span></a>
                    <a href="online-orders.php" class="qa-card" style="position:relative">
                        <span class="qa-icon">🌐</span><span class="qa-label">Online Orders</span>
                        <?php if ($stats['delivered_orders'] < $stats['total_orders']): ?>
                            <span class="absolute top-2 right-2 h-3 w-3 bg-red-500 rounded-full border-2 border-white"></span>
                        <?php endif; ?>
                    </a>
                    <a href="stock-alert.php" class="qa-card"><span class="qa-icon">📉</span><span class="qa-label">Low Stock</span></a>
                    <a href="reports.php" class="qa-card"><span class="qa-icon">📑</span><span class="qa-label">Reports</span></a>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">

            <div class="dash-shell" data-aos="fade-up">
                <div class="dash-shell-head">
                    <h2>📋 Recent Orders</h2>
                    <a href="parcels.php" class="btn btn-lime btn-sm">View All →</a>
                </div>
                <div class="table-wrap" style="border:none;border-radius:0;box-shadow:none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $parcels->fetch_assoc()): ?>
                            <tr>
                                <td class="font-display text-sm text-[#065f46] font-bold">#<?= htmlspecialchars($p['order_number']) ?></td>
                                <td class="text-sm"><?= htmlspecialchars($p['customer_name']) ?></td>
                                <td class="font-bold text-sm"><?= qm_money($p['subtotal']) ?></td>
                                <td><?= qm_badge($p['status']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($parcels->num_rows === 0): ?>
                                <tr><td colspan="4" class="p-6 text-center text-gray-400">No recent orders</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dash-shell" data-aos="fade-up" data-aos-delay="100">
                <div class="dash-shell-head">
                    <h2>⚠️ Restock Needed</h2>
                    <a href="inventory.php" class="btn btn-danger btn-sm">Manage</a>
                </div>
                <div class="table-wrap" style="border:none;border-radius:0;box-shadow:none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Current</th>
                                <th>Alert Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ls = $lowStock->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <p class="font-bold text-sm text-gray-800"><?= htmlspecialchars($ls['name']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($ls['power']) ?></p>
                                </td>
                                <td class="font-bold text-red-600 text-lg"><?= (int)$ls['stock_quantity'] ?></td>
                                <td class="text-sm text-gray-500"><?= (int)$ls['reorder_level'] ?></td>
                                <td>
                                    <a href="inventory.php?search=<?= urlencode($ls['name']) ?>" class="btn btn-outline btn-sm">Add Stock</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($lowStock->num_rows === 0): ?>
                                <tr><td colspan="4" class="p-6 text-center text-green-600 font-bold">All stocks are healthy! ✅</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
// 1. LIVE CLOCK
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { hour12: false });
    var _clk = document.getElementById('liveClock'); if (_clk) _clk.textContent = timeString;
}
setInterval(updateClock, 1000);
updateClock();

// 2. SALES CHART CONFIG
const ctx = document.getElementById('revenueChart').getContext('2d');
const salesData = <?= json_encode($chartData) ?>;
const labels = <?= json_encode($chartLabels) ?>;

// Gradient for Chart
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(132, 204, 22, 0.5)'); // Lime Accent
gradient.addColorStop(1, 'rgba(132, 204, 22, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Daily Sales (৳)',
            data: salesData,
            borderColor: '#065f46', // Deep Green
            backgroundColor: gradient,
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#065f46',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4 // Smooth curves
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#065f46',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10,
                cornerRadius: 8,
                displayColors: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                ticks: { font: { family: "'Inter', sans-serif" } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { family: "'Inter', sans-serif" } }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>