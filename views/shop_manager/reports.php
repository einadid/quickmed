<?php
/**
 * Shop Manager - Reports & Analytics
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

// Date range
$startDate = clean($_GET['start_date'] ?? date('Y-m-01'));
$endDate = clean($_GET['end_date'] ?? date('Y-m-d'));

// Get shop info
$shop = getShopById($shopId);

// Sales data
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

// Summary stats
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

// Top selling products
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
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">📊 Reports & Analytics</h1>
                <p class="text-xl text-gray-600 mt-2"><?= htmlspecialchars($shop['name']) ?></p>
            </div>
            <a href="<?= SITE_URL ?>/views/shop_manager/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Date Filter -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block font-bold mb-2 text-deep-green">Start Date</label>
                    <input type="date" name="start_date" value="<?= $startDate ?>" class="input border-4 border-deep-green" required>
                </div>
                <div>
                    <label class="block font-bold mb-2 text-deep-green">End Date</label>
                    <input type="date" name="end_date" value="<?= $endDate ?>" class="input border-4 border-deep-green" required>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="btn btn-primary flex-1">🔍 Generate Report</button>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="card bg-lime-accent border-4 border-deep-green" data-aos="fade-up">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL ORDERS</p>
                <p class="text-5xl font-bold text-deep-green"><?= $stats['total_orders'] ?? 0 ?></p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-bold text-deep-green mb-2">TOTAL REVENUE</p>
                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['total_revenue'] ?? 0, 2) ?></p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="200">
                <p class="text-sm font-bold text-deep-green mb-2">DELIVERED</p>
                <p class="text-5xl font-bold text-lime-accent"><?= $stats['delivered_orders'] ?? 0 ?></p>
            </div>

            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="300">
                <p class="text-sm font-bold text-deep-green mb-2">AVG ORDER VALUE</p>
                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['avg_order_value'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📈 Sales Trend
            </h2>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Top Products -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                🔥 Top Selling Products
            </h2>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Power</th>
                            <th>Sold</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; while ($product = $topProducts->fetch_assoc()): ?>
                            <tr>
                                <td class="text-2xl font-bold text-lime-accent">#<?= $rank++ ?></td>
                                <td class="font-bold"><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['power']) ?></td>
                                <td class="text-2xl font-bold text-deep-green"><?= $product['total_sold'] ?></td>
                                <td><?= $product['order_count'] ?></td>
                                <td class="font-bold">৳<?= number_format($product['revenue'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
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
            backgroundColor: 'rgba(132, 204, 22, 0.1)',
            borderWidth: 4,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>