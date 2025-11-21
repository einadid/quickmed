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
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<section class="bg-gray-50 min-h-screen pb-20">
    
    <!-- HERO HEADER -->
    <div class="bg-deep-green text-white pt-24 pb-32 relative overflow-hidden rounded-b-[3rem] shadow-xl">
        <!-- Decorative Blobs -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-lime-accent opacity-10 rounded-full blur-3xl transform translate-x-10 -translate-y-10"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full blur-3xl"></div>

        <div class="container mx-auto px-6 relative z-10 flex flex-col md:flex-row justify-between items-center">
            <div data-aos="fade-right">
                <div class="inline-block bg-white/10 backdrop-blur-md border border-white/20 px-3 py-1 rounded-full text-xs font-mono mb-3">
                    Member Since <?= date('Y', strtotime($user['created_at'])) ?>
                </div>
                <h1 class="text-3xl md:text-5xl font-bold mb-2">
                    <span id="greeting">Hello</span>, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!
                </h1>
                <p class="text-gray-200 text-lg opacity-90">Welcome to your health hub.</p>
            </div>
            
            <!-- Digital Loyalty Card -->
            <div class="mt-8 md:mt-0 w-full md:w-auto" data-aos="fade-left">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-900 p-6 rounded-2xl shadow-2xl border border-white/10 relative overflow-hidden w-full md:w-96 h-52 flex flex-col justify-between group hover:scale-[1.02] transition-transform duration-500">
                    
                    <!-- Card Pattern -->
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
                    
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-lime-accent text-xs font-bold uppercase tracking-widest">QuickMed Gold</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-4xl font-bold text-white"><?= number_format($user['points']) ?></span>
                                <span class="text-sm bg-white/20 px-2 py-0.5 rounded text-white">PTS</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-300">Cash Value</p>
                            <p class="text-xl font-bold text-lime-accent">৳<?= floor($user['points'] / 100) * 10 ?></p>
                        </div>
                    </div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-end mb-2">
                            <p class="font-mono text-gray-300 tracking-widest text-sm"><?= $user['member_id'] ?? '#### #### ####' ?></p>
                            <div class="w-8 h-8 bg-yellow-400/80 rounded-full opacity-80"></div>
                        </div>
                        <div class="w-full bg-black/20 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-lime-accent h-full" style="width: <?= ($user['points'] % 1000) / 10 ?>%"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 text-right">Next Reward in <?= 1000 - ($user['points'] % 1000) ?> pts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="container mx-auto px-6 -mt-20 relative z-20">
        
        <!-- 1. QUICK ACTIONS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <a href="<?= SITE_URL ?>/shop.php" class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all group text-center border border-gray-100">
                <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">🛍️</div>
                <h3 class="font-bold text-deep-green">Shop Medicine</h3>
                <p class="text-xs text-gray-500">Browse Products</p>
            </a>

            <a href="<?= SITE_URL ?>/prescription-upload.php" class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all group text-center border border-gray-100">
                <div class="text-4xl mb-3 group-hover:rotate-12 transition-transform">📋</div>
                <h3 class="font-bold text-deep-green">Upload Rx</h3>
                <p class="text-xs text-gray-500">Order by Photo</p>
            </a>

            <a href="<?= SITE_URL ?>/my-orders.php" class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all group text-center border border-gray-100">
                <div class="text-4xl mb-3 group-hover:translate-x-2 transition-transform">📦</div>
                <h3 class="font-bold text-deep-green">Track Order</h3>
                <p class="text-xs text-gray-500">Check Status</p>
            </a>

            <a href="<?= SITE_URL ?>/cart.php" class="bg-lime-accent p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all group text-center relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-6xl opacity-20 text-white">🛒</div>
                <div class="text-4xl mb-3 relative z-10">🛒</div>
                <h3 class="font-bold text-deep-green relative z-10">View Cart</h3>
                <p class="text-xs text-deep-green opacity-80 relative z-10">Checkout Now</p>
            </a>
        </div>

        <!-- 2. STATS & CHART ROW -->
        <div class="grid lg:grid-cols-3 gap-8 mb-10">
            
            <!-- Stats -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-deep-green flex items-center justify-between" data-aos="fade-up">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Spent</p>
                        <h3 class="text-3xl font-bold text-deep-green">৳<?= number_format($stats['total_spent'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-green-50 p-3 rounded-full text-2xl">💰</div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-blue-500 flex items-center justify-between" data-aos="fade-up" data-aos-delay="100">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Orders</p>
                        <h3 class="text-3xl font-bold text-blue-600"><?= $stats['total_orders'] ?></h3>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-full text-2xl">📦</div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-purple-500 flex items-center justify-between" data-aos="fade-up" data-aos-delay="200">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Prescriptions</p>
                        <h3 class="text-3xl font-bold text-purple-600"><?= $stats['prescriptions_uploaded'] ?></h3>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-full text-2xl">📄</div>
                </div>
            </div>

            <!-- Spending Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100" data-aos="zoom-in">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-deep-green">📊 Spending Overview</h3>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">Last 6 Months</span>
                </div>
                <div class="relative h-64 w-full">
                    <?php if(empty($spending)): ?>
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
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100" data-aos="fade-up">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-deep-green">📋 Recent Orders</h3>
                <a href="my-orders.php" class="text-sm text-lime-600 font-bold hover:underline">View All</a>
            </div>

            <?php if ($recentOrders->num_rows === 0): ?>
                <div class="text-center py-12">
                    <div class="text-6xl mb-4 opacity-20">🛒</div>
                    <p class="text-gray-500 font-medium">You haven't placed any orders yet.</p>
                    <a href="<?= SITE_URL ?>/shop.php" class="mt-4 inline-block btn btn-sm btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Order #</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-mono text-sm font-bold text-deep-green">
                                        #<?= htmlspecialchars($order['order_number']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-800">
                                        ৳<?= number_format($order['total_amount'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        // Use parcel status if available, else 'Pending'
                                        $status = $order['parcel_status'] ?: 'pending';
                                        $colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'processing' => 'bg-blue-100 text-blue-700',
                                            'delivered' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            'returned' => 'bg-gray-100 text-gray-700'
                                        ];
                                        $badgeClass = $colors[$status] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="<?= SITE_URL ?>/my-orders.php" class="text-sm text-deep-green font-bold hover:underline">
                                            Details &rarr;
                                        </a>
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