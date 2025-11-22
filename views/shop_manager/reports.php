<?php
/**
 * Shop Manager - Reports & Analytics (FIXED)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('shop_manager');

$pageTitle = 'Reports - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('dashboard.php');
}

// Date range logic
$startDate = isset($_GET['start_date']) ? clean($_GET['start_date']) : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? clean($_GET['end_date']) : date('Y-m-d');

// --- 1. GET SHOP INFO (FIXED: Replaced undefined function with Query) ---
$shopStmt = $conn->prepare("SELECT * FROM shops WHERE id = ?");
$shopStmt->bind_param("i", $shopId);
$shopStmt->execute();
$shop = $shopStmt->get_result()->fetch_assoc();
$shopStmt->close();

// --- 2. SALES DATA (Chart) ---
$salesQuery = "SELECT 
    DATE(p.created_at) as date,
    COUNT(p.id) as order_count,
    SUM(p.subtotal) as revenue
    FROM parcels p
    WHERE p.shop_id = ? AND DATE(p.created_at) BETWEEN ? AND ?
    GROUP BY DATE(p.created_at)
    ORDER BY date ASC";
$salesStmt = $conn->prepare($salesQuery);
$salesStmt->bind_param("iss", $shopId, $startDate, $endDate);
$salesStmt->execute();
$salesData = $salesStmt->get_result();

// --- 3. SUMMARY STATS ---
$statsQuery = "SELECT 
    COUNT(DISTINCT p.id) as total_orders,
    SUM(p.subtotal) as total_revenue,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_orders,
    AVG(p.subtotal) as avg_order_value
    FROM parcels p
    WHERE p.shop_id = ? AND DATE(p.created_at) BETWEEN ? AND ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iss", $shopId, $startDate, $endDate);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// --- 4. TOP SELLING PRODUCTS ---
$topProductsQuery = "SELECT m.name, m.power,
    COUNT(oi.id) as order_count,
    SUM(oi.quantity) as total_sold,
    SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN medicines m ON oi.medicine_id = m.id
    JOIN parcels p ON oi.parcel_id = p.id
    WHERE oi.shop_id = ? AND DATE(p.created_at) BETWEEN ? AND ?
    GROUP BY m.id
    ORDER BY total_sold DESC
    LIMIT 10";
$topProductsStmt = $conn->prepare($topProductsQuery);
$topProductsStmt->bind_param("iss", $shopId, $startDate, $endDate);
$topProductsStmt->execute();
$topProducts = $topProductsStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">📊 Reports & Analytics</h1>
                <p class="text-xl text-gray-600 mt-2">
                    <?= htmlspecialchars($shop['name'] ?? 'Unknown Shop') ?>
                </p>
            </div>
            <a href="<?= SITE_URL ?>/views/shop_manager/dashboard.php" class="bg-white text-deep-green border-2 border-deep-green px-6 py-2 rounded-lg font-bold hover:bg-deep-green hover:text-white transition">← Dashboard</a>
        </div>

        <div class="bg-white p-6 rounded-xl border-4 border-deep-green mb-8 shadow-lg" data-aos="fade-up">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block font-bold mb-2 text-deep-green">Start Date</label>
                    <input type="date" name="start_date" value="<?= $startDate ?>" class="w-full p-3 bg-gray-50 border-2 border-deep-green rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500" required>
                </div>
                <div>
                    <label class="block font-bold mb-2 text-deep-green">End Date</label>
                    <input type="date" name="end_date" value="<?= $endDate ?>" class="w-full p-3 bg-gray-50 border-2 border-deep-green rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500" required>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="w-full bg-deep-green text-white font-bold py-3 rounded-lg hover:bg-lime-600 transition shadow-[4px_4px_0px_#000] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                        🔍 Generate Report
                    </button>
                </div>
            </form>
        </div>

        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-lime-accent p-6 rounded-xl border-4 border-deep-green shadow-lg transform hover:-translate-y-1 transition" data-aos="fade-up">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL ORDERS</p>
                <p class="text-5xl font-bold text-deep-green"><?= $stats['total_orders'] ?? 0 ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl border-4 border-deep-green shadow-lg transform hover:-translate-y-1 transition" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL REVENUE</p>
                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['total_revenue'] ?? 0, 2) ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl border-4 border-deep-green shadow-lg transform hover:-translate-y-1 transition" data-aos="fade-up" data-aos-delay="200">
                <p class="text-sm font-bold text-deep-green mb-2">DELIVERED</p>
                <p class="text-5xl font-bold text-lime-600"><?= $stats['delivered_orders'] ?? 0 ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl border-4 border-deep-green shadow-lg transform hover:-translate-y-1 transition" data-aos="fade-up" data-aos-delay="300">
                <p class="text-sm font-bold text-deep-green mb-2">AVG ORDER VALUE</p>
                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['avg_order_value'] ?? 0, 2) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border-4 border-deep-green mb-8 shadow-lg" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📈 Sales Trend
            </h2>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl border-4 border-deep-green shadow-lg" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                🔥 Top Selling Products
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-deep-green border-b-2 border-deep-green">
                            <th class="p-3">#</th>
                            <th class="p-3">Product</th>
                            <th class="p-3">Power</th>
                            <th class="p-3">Sold</th>
                            <th class="p-3">Orders</th>
                            <th class="p-3">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; while ($product = $topProducts->fetch_assoc()): ?>
                            <tr class="border-b border-gray-200 hover:bg-lime-50 transition">
                                <td class="p-3 text-2xl font-bold text-lime-600">#<?= $rank++ ?></td>
                                <td class="p-3 font-bold"><?= htmlspecialchars($product['name']) ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($product['power']) ?></td>
                                <td class="p-3 text-xl font-bold text-deep-green"><?= $product['total_sold'] ?></td>
                                <td class="p-3"><?= $product['order_count'] ?></td>
                                <td class="p-3 font-bold text-deep-green">৳<?= number_format($product['revenue'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($rank === 1): ?>
                            <tr><td colspan="6" class="p-4 text-center text-gray-500">No sales data for this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
// Sales chart
const salesDates = [];
const salesRevenue = [];
<?php 
$salesData->data_seek(0);
while ($row = $salesData->fetch_assoc()): 
?>
salesDates.push('<?= date('M d', strtotime($row['date'])) ?>');
salesRevenue.push(<?= $row['revenue'] ?>);
<?php endwhile; ?>

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesDates,
        datasets: [{
            label: 'Revenue (৳)',
            data: salesRevenue,
            borderColor: '#065f46',
            backgroundColor: 'rgba(132, 204, 22, 0.2)',
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            pointBackgroundColor: '#84cc16',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#e5e7eb' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>