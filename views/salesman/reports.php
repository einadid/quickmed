<?php
/**
 * Salesman Reports - Advanced Filtering
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$pageTitle = 'Sales Reports - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

// Filters
$startDate = clean($_GET['start_date'] ?? date('Y-m-01'));
$endDate = clean($_GET['end_date'] ?? date('Y-m-d'));
$filterType = clean($_GET['type'] ?? 'all');

// Base Query
$whereClause = "p.shop_id = $shopId AND DATE(p.created_at) BETWEEN '$startDate' AND '$endDate'";

if ($filterType === 'pos') {
    $whereClause .= " AND o.delivery_type = 'pickup'";
} elseif ($filterType === 'online') {
    $whereClause .= " AND o.delivery_type = 'home'";
} elseif ($filterType === 'returned') {
    $whereClause .= " AND p.status = 'returned'";
}

// Fetch Data
$query = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.delivery_type
          FROM parcels p
          JOIN orders o ON p.order_id = o.id
          WHERE $whereClause
          ORDER BY p.created_at DESC";
$reports = $conn->query($query);

// Summary Stats
$totalSales = 0;
$totalOrders = 0;
$returnedCount = 0;

// Temp loop for stats calculation
$data = [];
while ($row = $reports->fetch_assoc()) {
    if ($row['status'] !== 'returned') {
        $totalSales += $row['subtotal'];
    } else {
        $returnedCount++;
    }
    $totalOrders++;
    $data[] = $row;
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">📊 Sales Reports</h1>
                <p class="text-gray-600">Track your performance</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Filters -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <form method="GET" class="grid md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block font-bold mb-2 text-sm text-gray-600">Start Date</label>
                    <input type="date" name="start_date" value="<?= $startDate ?>" class="input border-2 border-deep-green w-full">
                </div>
                <div>
                    <label class="block font-bold mb-2 text-sm text-gray-600">End Date</label>
                    <input type="date" name="end_date" value="<?= $endDate ?>" class="input border-2 border-deep-green w-full">
                </div>
                <div>
                    <label class="block font-bold mb-2 text-sm text-gray-600">Filter By</label>
                    <select name="type" class="input border-2 border-deep-green w-full">
                        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Orders</option>
                        <option value="pos" <?= $filterType === 'pos' ? 'selected' : '' ?>>POS Sales (Offline)</option>
                        <option value="online" <?= $filterType === 'online' ? 'selected' : '' ?>>Online Orders</option>
                        <option value="returned" <?= $filterType === 'returned' ? 'selected' : '' ?>>Returned Items</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full h-[46px]">🔍 Filter Report</button>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-deep-green text-white p-6 rounded-xl shadow-lg">
                <p class="text-xs uppercase font-bold opacity-75">Total Revenue</p>
                <p class="text-3xl font-bold mt-1">৳<?= number_format($totalSales, 2) ?></p>
            </div>
            <div class="bg-white border-2 border-deep-green p-6 rounded-xl shadow-sm">
                <p class="text-xs uppercase font-bold text-gray-500">Total Orders</p>
                <p class="text-3xl font-bold text-deep-green mt-1"><?= $totalOrders ?></p>
            </div>
            <div class="bg-red-50 border-2 border-red-500 p-6 rounded-xl shadow-sm">
                <p class="text-xs uppercase font-bold text-red-600">Returned Orders</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= $returnedCount ?></p>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-deep-green text-white">
                            <th>Date</th>
                            <th>Order #</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">No records found for selected period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="text-sm"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td class="font-mono font-bold">#<?= $row['order_number'] ?></td>
                                    <td>
                                        <?php if ($row['delivery_type'] === 'pickup'): ?>
                                            <span class="badge bg-blue-100 text-blue-800 border border-blue-200">POS / Offline</span>
                                        <?php else: ?>
                                            <span class="badge bg-purple-100 text-purple-800 border border-purple-200">Online</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <p class="font-bold text-sm"><?= htmlspecialchars($row['customer_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($row['customer_phone']) ?></p>
                                    </td>
                                    <td class="font-bold text-deep-green">৳<?= number_format($row['subtotal'], 2) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'returned'): ?>
                                            <span class="badge badge-danger">Returned</span>
                                        <?php elseif ($row['status'] === 'delivered'): ?>
                                            <span class="badge badge-success">Sold / Delivered</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><?= ucfirst($row['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="parcel-details.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>