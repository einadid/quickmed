<?php
/**
 * Admin - Profit Report
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

// Calculate Total Profit
$profitQuery = "SELECT 
    m.name, 
    SUM(oi.quantity) as total_sold,
    SUM(oi.quantity * sm.purchase_price) as total_cost,
    SUM(oi.subtotal) as total_revenue,
    SUM(oi.subtotal - (oi.quantity * sm.purchase_price)) as total_profit
    FROM order_items oi
    JOIN shop_medicines sm ON oi.medicine_id = sm.medicine_id AND oi.shop_id = sm.shop_id
    JOIN medicines m ON oi.medicine_id = m.id
    GROUP BY m.id
    ORDER BY total_profit DESC";

$profitData = $conn->query($profitQuery);

$totalRevenue = 0;
$totalProfit = 0;

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold text-deep-green mb-8">💰 Profit Report</h1>

    <div class="card bg-white border-4 border-deep-green">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Total Sold</th>
                    <th>Total Revenue</th>
                    <th>Total Cost</th>
                    <th>Net Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $profitData->fetch_assoc()): 
                    $totalRevenue += $row['total_revenue'];
                    $totalProfit += $row['total_profit'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['total_sold'] ?></td>
                        <td>৳<?= number_format($row['total_revenue'], 2) ?></td>
                        <td>৳<?= number_format($row['total_cost'], 2) ?></td>
                        <td class="font-bold text-green-600">৳<?= number_format($row['total_profit'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="bg-deep-green text-white font-bold text-lg">
                    <td colspan="2" class="text-right">GRAND TOTAL:</td>
                    <td>৳<?= number_format($totalRevenue, 2) ?></td>
                    <td>-</td>
                    <td>৳<?= number_format($totalProfit, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>