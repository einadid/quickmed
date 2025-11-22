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
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<section class="bg-gray-50 min-h-screen pb-20">
    
    <div class="bg-deep-green text-white pt-24 pb-32 relative overflow-hidden rounded-b-[3rem] shadow-xl">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <div class="absolute top-10 left-10 text-9xl transform -rotate-12">🏥</div>
            <div class="absolute bottom-10 right-10 text-9xl transform rotate-12">💊</div>
        </div>

        <div class="container mx-auto px-6 relative z-10 flex flex-col md:flex-row justify-between items-center">
            <div data-aos="fade-right">
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-lime-accent text-deep-green text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Shop Manager</span>
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lime-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-lime-500"></span>
                    </span>
                    <span class="text-lime-accent text-sm font-mono">System Live</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-2">
                    Hello, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>! 👋
                </h1>
                <p class="text-gray-200 text-lg flex items-center gap-2">
                    📍 <?= htmlspecialchars($shop['name']) ?> <span class="text-lime-accent">•</span> <?= htmlspecialchars($shop['city']) ?>
                </p>
            </div>
            
            <div class="text-right mt-6 md:mt-0" data-aos="fade-left">
                <div class="text-5xl font-mono font-bold text-lime-accent" id="liveClock">00:00:00</div>
                <div class="text-gray-300 text-lg"><?= date('l, d F Y') ?></div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-20 relative z-20">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-lime-accent transform hover:-translate-y-2 transition-all duration-300" data-aos="fade-up" data-aos-delay="0">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Today's Revenue</p>
                        <h3 class="text-3xl font-bold text-deep-green mt-1">৳<?= number_format($todayStats['sales'] ?? 0) ?></h3>
                        <p class="text-xs text-gray-400 mt-1"><?= $todayStats['count'] ?? 0 ?> orders processed</p>
                    </div>
                    <div class="bg-lime-100 p-3 rounded-full text-2xl">💰</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-deep-green transform hover:-translate-y-2 transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Lifetime</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-1">৳<?= number_format($stats['total_sales'] ?? 0) ?></h3>
                        <p class="text-xs text-green-600 mt-1 font-bold">Total <?= $stats['total_orders'] ?> orders</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full text-2xl">📈</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-blue-500 transform hover:-translate-y-2 transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Inventory</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['active_products'] ?></h3>
                        <p class="text-xs text-blue-500 mt-1 font-bold">Active Products</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full text-2xl">📦</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-red-500 transform hover:-translate-y-2 transition-all duration-300 animate-pulse" data-aos="fade-up" data-aos-delay="300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-red-400 text-xs font-bold uppercase tracking-wider">Attention Needed</p>
                        <h3 class="text-3xl font-bold text-red-600 mt-1"><?= $stats['low_stock_items'] ?></h3>
                        <p class="text-xs text-red-400 mt-1 font-bold">Items Low Stock</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full text-2xl">⚠️</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 mb-10">
            
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100" data-aos="zoom-in">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-deep-green flex items-center gap-2">
                        📊 Sales Overview <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">Last 7 Days</span>
                    </h3>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 flex flex-col justify-between" data-aos="fade-left">
                <h3 class="text-xl font-bold text-deep-green mb-4">⚡ Quick Actions</h3>
                
                <div class="grid grid-cols-2 gap-4 h-full">
                    <a href="inventory.php" class="bg-gray-50 p-4 rounded-xl hover:bg-lime-50 hover:border-lime-accent border border-transparent transition text-center group flex flex-col justify-center items-center">
                        <div class="text-3xl mb-2 group-hover:scale-110 transition-transform">💊</div>
                        <div class="text-sm font-bold text-gray-700">Inventory</div>
                    </a>

                    <a href="online-orders.php" class="bg-gray-50 p-4 rounded-xl hover:bg-blue-50 hover:border-blue-300 border border-transparent transition text-center group relative flex flex-col justify-center items-center">
                        <div class="text-3xl mb-2 group-hover:scale-110 transition-transform">🌐</div>
                        <div class="text-sm font-bold text-gray-700">Online Orders</div>
                        <?php if ($stats['delivered_orders'] < $stats['total_orders']): ?>
                            <span class="absolute top-2 right-2 h-3 w-3 bg-red-500 rounded-full border-2 border-white"></span>
                        <?php endif; ?>
                    </a>

                    <a href="stock-alert.php" class="bg-gray-50 p-4 rounded-xl hover:bg-red-50 hover:border-red-300 border border-transparent transition text-center group flex flex-col justify-center items-center">
                        <div class="text-3xl mb-2 group-hover:rotate-12 transition-transform">📉</div>
                        <div class="text-sm font-bold text-gray-700">Low Stock</div>
                    </a>

                    <a href="reports.php" class="bg-gray-50 p-4 rounded-xl hover:bg-purple-50 hover:border-purple-300 border border-transparent transition text-center group flex flex-col justify-center items-center">
                        <div class="text-3xl mb-2 group-hover:scale-110 transition-transform">📑</div>
                        <div class="text-sm font-bold text-gray-700">Reports</div>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100" data-aos="fade-up">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-deep-green">📋 Recent Orders</h3>
                    <a href="parcels.php" class="text-sm text-lime-600 font-bold hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Order ID</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($p = $parcels->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono text-sm text-deep-green font-bold">#<?= $p['order_number'] ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($p['customer_name']) ?></td>
                                <td class="px-6 py-4 font-bold text-sm">৳<?= number_format($p['subtotal']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase 
                                        <?= $p['status'] == 'delivered' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                        <?= $p['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($parcels->num_rows === 0): ?>
                                <tr><td colspan="4" class="p-6 text-center text-gray-400">No recent orders</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-red-100" data-aos="fade-up" data-aos-delay="100">
                <div class="p-6 border-b border-red-50 bg-red-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-red-600">⚠️ Restock Needed</h3>
                    <a href="inventory.php" class="text-sm text-red-600 font-bold hover:underline">Manage</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white text-xs uppercase text-red-400 border-b border-red-100">
                            <tr>
                                <th class="px-6 py-3">Medicine</th>
                                <th class="px-6 py-3">Current</th>
                                <th class="px-6 py-3">Alert Level</th>
                                <th class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($ls = $lowStock->fetch_assoc()): ?>
                            <tr class="hover:bg-red-50 transition group">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-sm text-gray-800"><?= htmlspecialchars($ls['name']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($ls['power']) ?></p>
                                </td>
                                <td class="px-6 py-4 font-bold text-red-600 text-lg"><?= $ls['stock_quantity'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= $ls['reorder_level'] ?></td>
                                <td class="px-6 py-4">
                                    <a href="inventory.php?search=<?= urlencode($ls['name']) ?>" class="text-xs bg-deep-green text-white px-2 py-1 rounded hover:bg-lime-600">Add Stock</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($lowStock->num_rows === 0): ?>
                                <tr><td colspan="4" class="p-6 text-center text-green-500 font-bold">All stocks are healthy! ✅</td></tr>
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
    document.getElementById('liveClock').textContent = timeString;
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