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
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-4" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green mb-2 font-mono uppercase">
                    👑 Master Control
                </h1>
                <p class="text-xl text-gray-600">Admin Panel & System Overview</p>
            </div>
            <div class="text-right bg-white border-4 border-deep-green p-4 shadow-[4px_4px_0px_0px_rgba(6,95,70,1)]">
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">SYSTEM TIME</p>
                <p class="text-xl font-mono font-bold text-deep-green"><?= date('h:i A | d M Y') ?></p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-8" data-aos="fade-up">
            <div class="card bg-lime-accent border-4 border-deep-green p-6 shadow-sm relative overflow-hidden group hover:shadow-lg transition-all">
                <div class="flex items-center justify-between z-10 relative">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2 uppercase tracking-wider">Total Revenue</p>
                        <p class="text-5xl font-bold text-deep-green">৳<?= number_format($stats['total_revenue']) ?></p>
                        <p class="text-sm font-bold text-deep-green mt-2 opacity-75">Lifetime Earnings</p>
                    </div>
                    <div class="text-7xl opacity-20 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300">💰</div>
                </div>
            </div>

            <div class="card bg-white border-4 border-deep-green p-6 shadow-sm relative overflow-hidden group hover:shadow-lg transition-all">
                <div class="flex items-center justify-between z-10 relative">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2 uppercase tracking-wider">Net Profit</p>
                        <p class="text-5xl font-bold text-deep-green">৳<?= number_format($profit) ?></p>
                        <p class="text-sm font-bold text-green-600 mt-2">Pure Income</p>
                    </div>
                    <div class="text-7xl opacity-20 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300">📈</div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="card bg-white border-4 border-deep-green p-6" data-aos="fade-up" data-aos-delay="0">
                <p class="text-sm font-bold text-deep-green mb-2 uppercase">Total Orders</p>
                <p class="text-3xl font-bold text-deep-green"><?= number_format($stats['total_orders']) ?></p>
                <p class="text-sm text-gray-600 mt-1">Processed</p>
            </div>

            <div class="card bg-white border-4 border-deep-green p-6" data-aos="fade-up" data-aos-delay="100">
                <p class="text-sm font-bold text-deep-green mb-2 uppercase">Customers</p>
                <p class="text-3xl font-bold text-deep-green"><?= number_format($stats['total_customers']) ?></p>
                <p class="text-sm text-gray-600 mt-1">Registered Users</p>
            </div>

            <div class="card bg-white border-4 border-deep-green p-6" data-aos="fade-up" data-aos-delay="200">
                <p class="text-sm font-bold text-deep-green mb-2 uppercase">Active Shops</p>
                <p class="text-3xl font-bold text-deep-green"><?= $stats['active_shops'] ?></p>
                <p class="text-sm text-gray-600 mt-1">Branches</p>
            </div>

            <div class="card <?= $totalPendingTasks > 0 ? 'bg-red-50 border-red-500' : 'bg-white border-lime-accent' ?> border-4 p-6" data-aos="fade-up" data-aos-delay="300">
                <p class="text-sm font-bold <?= $totalPendingTasks > 0 ? 'text-red-600' : 'text-deep-green' ?> mb-2 uppercase">Needs Attention</p>
                <p class="text-3xl font-bold <?= $totalPendingTasks > 0 ? 'text-red-600' : 'text-deep-green' ?>">
                    <?= $totalPendingTasks ?>
                </p>
                <p class="text-sm <?= $totalPendingTasks > 0 ? 'text-red-500' : 'text-gray-600' ?> mt-1">Pending Tasks</p>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3 inline-block">
            🚀 Quick Actions
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-12" data-aos="fade-up">
            
            <a href="medicines.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">💊</span>
                <span class="font-bold uppercase tracking-wide">Medicines</span>
            </a>

            <a href="shops.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">🏪</span>
                <span class="font-bold uppercase tracking-wide">Shops</span>
            </a>

            <a href="users.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">👥</span>
                <span class="font-bold uppercase tracking-wide">Users</span>
            </a>

            <a href="codes.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">🎫</span>
                <span class="font-bold uppercase tracking-wide">Codes</span>
            </a>

            <a href="prescriptions.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group relative">
                <span class="text-3xl group-hover:scale-110 transition-transform">📋</span>
                <span class="font-bold uppercase tracking-wide">Prescriptions</span>
                <?php if ($stats['pending_rx'] > 0): ?>
                    <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                        <?= $stats['pending_rx'] ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="reports.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">📊</span>
                <span class="font-bold uppercase tracking-wide">Reports</span>
            </a>

            <a href="flash-sales.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 border-lime-accent text-deep-green hover:bg-lime-accent hover:text-deep-green transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">⚡</span>
                <span class="font-bold uppercase tracking-wide">Flash Sales</span>
            </a>

            <a href="messages.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group relative">
                <span class="text-3xl group-hover:scale-110 transition-transform">💬</span>
                <span class="font-bold uppercase tracking-wide">Messages</span>
                <?php if ($stats['total_messages'] > 0): ?>
                    <span class="absolute top-2 right-2 bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                        <?= $stats['total_messages'] ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="reviews.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group relative">
                <span class="text-3xl group-hover:scale-110 transition-transform">⭐</span>
                <span class="font-bold uppercase tracking-wide">Reviews</span>
                <?php if ($stats['pending_reviews'] > 0): ?>
                    <span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                        <?= $stats['pending_reviews'] ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="audit-logs.php" class="btn btn-outline h-auto flex flex-col gap-2 py-6 border-2 hover:bg-deep-green hover:text-white transition-all group">
                <span class="text-3xl group-hover:scale-110 transition-transform">🛡️</span>
                <span class="font-bold uppercase tracking-wide">Audit Logs</span>
            </a>
        </div>

        <div class="card bg-white border-4 border-deep-green shadow-lg" data-aos="fade-up">
            <div class="p-6 border-b-4 border-deep-green bg-gray-50 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-deep-green uppercase tracking-wide">
                    📋 Recent Transactions
                </h2>
                <a href="reports.php" class="text-xs bg-deep-green text-white px-4 py-2 rounded font-bold hover:bg-lime-accent hover:text-deep-green transition uppercase tracking-wider">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                            <th class="p-4 font-bold border-b-2 border-gray-200">Order ID</th>
                            <th class="p-4 font-bold border-b-2 border-gray-200">Customer</th>
                            <th class="p-4 font-bold border-b-2 border-gray-200">Status</th>
                            <th class="p-4 font-bold border-b-2 border-gray-200">Amount</th>
                            <th class="p-4 font-bold border-b-2 border-gray-200">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                            <tr class="hover:bg-lime-50 transition-colors duration-200">
                                <td class="p-4 font-mono font-bold text-deep-green">
                                    #<?= $order['order_number'] ?>
                                </td>
                                <td class="p-4 font-medium text-gray-700">
                                    <?= htmlspecialchars($order['full_name'] ?: $order['customer_name']) ?>
                                </td>
                                <td class="p-4">
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold border border-green-200 uppercase">
                                        Paid
                                    </span>
                                </td>
                                <td class="p-4 font-mono font-bold text-deep-green">
                                    ৳<?= number_format($order['total_amount']) ?>
                                </td>
                                <td class="p-4 text-sm text-gray-500 font-mono">
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

<style>
    /* Custom overrides to ensure the brutalist look matches Code 2 perfectly */
    .card {
        border-radius: 0.5rem; /* Standard rounded usually, but Code 2 implies sharper or slightly rounded */
    }
    .btn-outline {
        background: white;
        color: #065f46; /* Deep Green */
        border-color: #e5e7eb;
    }
    .btn-outline:hover {
        border-color: #065f46;
    }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>