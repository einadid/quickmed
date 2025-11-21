<?php
/**
 * Admin - Analytics & Reports
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Analytics & Reports - Admin';

// Date range
$startDate = clean($_GET['start_date'] ?? date('Y-m-01'));
$endDate = clean($_GET['end_date'] ?? date('Y-m-d'));

// Sales Analytics
$salesQuery = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as order_count,
    SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC";
$salesStmt = $conn->prepare($salesQuery);
$salesStmt->bind_param("ss", $startDate, $endDate);
$salesStmt->execute();
$salesData = $salesStmt->get_result();

// Shop Performance
$shopQuery = "SELECT s.name, s.city,
    COUNT(DISTINCT p.id) as total_orders,
    SUM(p.subtotal) as total_sales,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_orders
    FROM shops s
    LEFT JOIN parcels p ON s.id = p.shop_id 
        AND DATE(p.created_at) BETWEEN ? AND ?
    GROUP BY s.id
    ORDER BY total_sales DESC";
$shopStmt = $conn->prepare($shopQuery);
$shopStmt->bind_param("ss", $startDate, $endDate);
$shopStmt->execute();
$shopPerformance = $shopStmt->get_result();

// Category Sales
$categoryQuery = "SELECT m.category,
    COUNT(oi.id) as items_sold,
    SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN medicines m ON oi.medicine_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY m.category
    ORDER BY revenue DESC
    LIMIT 10";
$categoryStmt = $conn->prepare($categoryQuery);
$categoryStmt->bind_param("ss", $startDate, $endDate);
$categoryStmt->execute();
$categoryData = $categoryStmt->get_result();

// Top Selling Products
$topProductsQuery = "SELECT m.name, m.power,
    COUNT(oi.id) as order_count,
    SUM(oi.quantity) as total_sold,
    SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN medicines m ON oi.medicine_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY m.id
    ORDER BY total_sold DESC
    LIMIT 10";
$topProductsStmt = $conn->prepare($topProductsQuery);
$topProductsStmt->bind_param("ss", $startDate, $endDate);
$topProductsStmt->execute();
$topProducts = $topProductsStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">📊 Analytics & Reports</h1>
            <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Date Filter -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <label class="block font-bold mb-2 text-deep-green">Start Date</label>
                    <input type="date" name="start_date" value="<?= $startDate ?>" class="input border-4 border-deep-green" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block font-bold mb-2 text-deep-green">End Date</label>
                    <input type="date" name="end_date" value="<?= $endDate ?>" class="input border-4 border-deep-green" required>
                </div>
                <div class="md:col-span-2 flex items-end gap-4">
                    <button type="submit" class="btn btn-primary flex-1">🔍 Generate Report</button>
                    <button type="button" onclick="exportReport()" class="btn btn-outline flex-1">📥 Export CSV</button>
                </div>
            </form>
        </div>

        <!-- Sales Chart -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📈 Sales Trend
            </h2>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Shop Performance -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    🏪 Shop Performance
                </h2>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Shop</th>
                                <th>Orders</th>
                                <th>Delivered</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($shop = $shopPerformance->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="font-bold"><?= htmlspecialchars($shop['name']) ?></div>
                                        <div class="text-sm text-gray-600"><?= htmlspecialchars($shop['city']) ?></div>
                                    </td>
                                    <td class="font-bold"><?= $shop['total_orders'] ?? 0 ?></td>
                                    <td><?= $shop['delivered_orders'] ?? 0 ?></td>
                                    <td class="font-bold text-deep-green">৳<?= number_format($shop['total_sales'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    🔥 Top Selling Products
                </h2>
                <div class="space-y-3">
                    <?php $rank = 1; while ($product = $topProducts->fetch_assoc()): ?>
                        <div class="flex items-center gap-4 p-4 border-2 border-gray-200">
                            <div class="text-3xl font-bold text-lime-accent">#<?= $rank++ ?></div>
                            <div class="flex-1">
                                <p class="font-bold text-deep-green"><?= htmlspecialchars($product['name']) ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($product['power']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-deep-green">৳<?= number_format($product['revenue'], 2) ?></p>
                                <p class="text-sm text-gray-600"><?= $product['total_sold'] ?> sold</p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="card bg-white border-4 border-deep-green mt-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📊 Category Sales
            </h2>
            <canvas id="categoryChart" height="80"></canvas>
        </div>
    </div>
</section>

<script>
// Prepare sales data
const salesDates = [];
const salesRevenue = [];
<?php 
$salesData->data_seek(0);
while ($row = $salesData->fetch_assoc()): 
?>
salesDates.push('<?= date('M d', strtotime($row['date'])) ?>');
salesRevenue.push(<?= $row['revenue'] ?>);
<?php endwhile; ?>

// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
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
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Category data
const categoryLabels = [];
const categoryRevenue = [];
<?php 
$categoryData->data_seek(0);
while ($row = $categoryData->fetch_assoc()): 
?>
categoryLabels.push('<?= htmlspecialchars($row['category']) ?>');
categoryRevenue.push(<?= $row['revenue'] ?>);
<?php endwhile; ?>

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: categoryLabels,
        datasets: [{
            label: 'Revenue (৳)',
            data: categoryRevenue,
            backgroundColor: '#84cc16',
            borderColor: '#065f46',
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function exportReport() {
    const startDate = '<?= $startDate ?>';
    const endDate = '<?= $endDate ?>';
    window.location.href = `<?= SITE_URL ?>/ajax/export_report.php?start=${startDate}&end=${endDate}`;
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>