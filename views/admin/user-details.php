<?php
/**
 * Admin - User Details & Reports
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$userId = intval($_GET['id'] ?? 0);

if (!$userId) {
    redirect('views/admin/users.php');
}

// Get User Info
// Added r.name as role_slug to ensure we check the correct system name for logic
$userQuery = "SELECT u.*, r.display_name as role_name, r.name as role_slug, s.name as shop_name 
              FROM users u 
              JOIN roles r ON u.role_id = r.id 
              LEFT JOIN shops s ON u.shop_id = s.id 
              WHERE u.id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

// Get Order History (General - applies if they bought items personally)
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC");

// Get Prescriptions (General - applies if they are a customer/patient)
$prescriptions = $conn->query("SELECT * FROM prescriptions WHERE user_id = $userId ORDER BY created_at DESC");

// Get Points Log
$pointsLog = $conn->query("SELECT * FROM points_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 20");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">👤 User Profile</h1>
                <p class="text-gray-600">ID: #<?= $user['id'] ?> • <?= htmlspecialchars($user['username']) ?></p>
            </div>
            <a href="users.php" class="btn btn-outline">← Back to Users</a>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1 space-y-6">
                <div class="card bg-white border-4 border-deep-green p-6 text-center" data-aos="fade-right">
                    <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl font-bold text-deep-green border-4 border-lime-accent">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <h2 class="text-2xl font-bold text-deep-green"><?= htmlspecialchars($user['full_name']) ?></h2>
                    <span class="badge badge-info mt-2"><?= htmlspecialchars($user['role_name']) ?></span>
                    
                    <?php if ($user['is_banned']): ?>
                        <span class="badge badge-danger mt-2">🚫 Banned</span>
                    <?php else: ?>
                        <span class="badge badge-success mt-2">✅ Active</span>
                    <?php endif; ?>

                    <div class="mt-6 text-left space-y-3 border-t pt-4">
                        <p><strong>📧 Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>📱 Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                        <?php if ($user['address']): ?>
                            <p><strong>📍 Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
                        <?php endif; ?>
                        <?php if ($user['shop_name']): ?>
                            <p><strong>🏪 Shop:</strong> <?= htmlspecialchars($user['shop_name']) ?></p>
                        <?php endif; ?>
                        <p><strong>📅 Joined:</strong> <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                    </div>

                    <div class="mt-6">
                        <a href="users.php?toggle_ban=<?= $user['id'] ?>" class="btn w-full <?= $user['is_banned'] ? 'btn-success' : 'btn-danger' ?>">
                            <?= $user['is_banned'] ? '✅ Unban User' : '🚫 Ban User' ?>
                        </a>
                    </div>
                </div>

                <?php if ($user['role_slug'] === 'customer'): ?>
                <div class="card bg-lime-accent border-4 border-deep-green p-6 text-center" data-aos="fade-up">
                    <p class="text-sm font-bold text-deep-green mb-2 uppercase">Loyalty Points</p>
                    <h3 class="text-4xl font-bold text-deep-green">⭐ <?= number_format($user['points']) ?></h3>
                    
                    <div class="mt-4 bg-white/50 p-3 rounded max-h-40 overflow-y-auto text-left text-xs">
                        <?php while ($log = $pointsLog->fetch_assoc()): ?>
                            <div class="flex justify-between border-b border-deep-green/20 py-1">
                                <span><?= htmlspecialchars($log['description']) ?></span>
                                <span class="font-bold <?= $log['points'] > 0 ? 'text-green-700' : 'text-red-700' ?>">
                                    <?= $log['points'] > 0 ? '+' : '' ?><?= $log['points'] ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-2 space-y-8">
                
                <?php if ($user['role_slug'] === 'shop_manager' || $user['role_name'] === 'shop_manager'): 
                    // Fetch shop stats if shop_id exists
                    if($user['shop_id']) {
                        $shopStats = $conn->query("SELECT COUNT(*) as total_sales, SUM(subtotal) as revenue FROM parcels WHERE shop_id = {$user['shop_id']}")->fetch_assoc();
                ?>
                <div class="card bg-white border-4 border-purple-500 p-6" data-aos="fade-left">
                    <h3 class="text-xl font-bold text-purple-700 mb-4 border-b-2 border-purple-200 pb-2">🏪 Shop Performance Report</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-purple-50 p-4 rounded">
                            <p class="text-sm font-bold text-purple-600">Total Sales Processed</p>
                            <p class="text-3xl font-bold"><?= $shopStats['total_sales'] ?? 0 ?></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded">
                            <p class="text-sm font-bold text-purple-600">Revenue Generated</p>
                            <p class="text-3xl font-bold">৳<?= number_format($shopStats['revenue'] ?? 0) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <a href="../shop_manager/reports.php?shop_id=<?= $user['shop_id'] ?>" class="btn btn-outline btn-sm border-purple-500 text-purple-700 hover:bg-purple-500 hover:text-white">View Full Report</a>
                    </div>
                </div>
                <?php } else { ?>
                    <div class="alert alert-warning">This manager is not assigned to a shop yet.</div>
                <?php } endif; ?>

                <?php if ($user['role_slug'] === 'salesman' || $user['role_name'] === 'salesman'): 
                    // Assuming sales tracking is based on user_id in orders (or a specific salesman_id column if you have one)
                    $salesStats = $conn->query("SELECT COUNT(*) as sales_count, SUM(total_amount) as sales_amount FROM orders WHERE user_id = {$user['id']}")->fetch_assoc();
                ?>
                <div class="card bg-white border-4 border-blue-500 p-6" data-aos="fade-left">
                    <h3 class="text-xl font-bold text-blue-700 mb-4 border-b-2 border-blue-200 pb-2">👨‍💼 Sales Performance</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-blue-50 p-4 rounded">
                            <p class="text-sm font-bold text-blue-600">Orders Created</p>
                            <p class="text-3xl font-bold"><?= $salesStats['sales_count'] ?? 0 ?></p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded">
                            <p class="text-sm font-bold text-blue-600">Total Sales Value</p>
                            <p class="text-3xl font-bold">৳<?= number_format($salesStats['sales_amount'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($user['role_slug'] === 'doctor' || $user['role_name'] === 'doctor'): 
                    $docStats = $conn->query("SELECT 
                        (SELECT COUNT(*) FROM prescriptions WHERE reviewed_by = {$user['id']}) as rx_reviewed,
                        (SELECT COUNT(*) FROM health_posts WHERE author_id = {$user['id']}) as posts_created"
                    )->fetch_assoc();
                ?>
                <div class="card bg-white border-4 border-red-500 p-6" data-aos="fade-left">
                    <h3 class="text-xl font-bold text-red-700 mb-4 border-b-2 border-red-200 pb-2">👨‍⚕️ Doctor Activity</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-red-50 p-4 rounded">
                            <p class="text-sm font-bold text-red-600">Prescriptions Reviewed</p>
                            <p class="text-3xl font-bold"><?= $docStats['rx_reviewed'] ?? 0 ?></p>
                        </div>
                        <div class="bg-red-50 p-4 rounded">
                            <p class="text-sm font-bold text-red-600">Articles Published</p>
                            <p class="text-3xl font-bold"><?= $docStats['posts_created'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                    <h3 class="text-xl font-bold text-deep-green mb-4 border-b-2 border-gray-200 pb-2">📦 Order History</h3>
                    
                    <?php if ($orders->num_rows === 0): ?>
                        <p class="text-gray-500 text-center py-4">No orders found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto max-h-60 overflow-y-auto custom-scroll">
                            <table class="table w-full text-sm">
                                <thead class="sticky top-0 bg-gray-100">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Points</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td class="font-mono font-bold">#<?= $order['order_number'] ?></td>
                                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                            <td class="font-bold">৳<?= number_format($order['total_amount']) ?></td>
                                            <td class="text-green-600">+<?= $order['points_earned'] ?></td>
                                            <td><span class="badge badge-success">Paid</span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left" data-aos-delay="100">
                    <h3 class="text-xl font-bold text-deep-green mb-4 border-b-2 border-gray-200 pb-2">📋 Prescriptions</h3>
                    
                    <?php if ($prescriptions->num_rows === 0): ?>
                        <p class="text-gray-500 text-center py-4">No prescriptions uploaded.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-4">
                            <?php while ($presc = $prescriptions->fetch_assoc()): ?>
                                <div class="border p-2 rounded hover:shadow-md transition cursor-pointer" onclick="window.open('<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>', '_blank')">
                                    <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>" class="h-24 w-full object-cover rounded mb-2">
                                    <div class="flex justify-between text-xs">
                                        <span><?= date('d M Y', strtotime($presc['created_at'])) ?></span>
                                        <span class="font-bold text-<?= $presc['status'] == 'approved' ? 'green' : 'yellow' ?>-600">
                                            <?= ucfirst($presc['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>