<?php
/**
 * My Orders - Customer Order History
 */

require_once 'config.php';

requireLogin();
requireRole('customer');

$pageTitle = 'My Orders - QuickMed';
$userId = $_SESSION['user_id'];

// Get orders with parcel info
$ordersQuery = "SELECT o.*, 
                COUNT(DISTINCT p.id) as parcel_count,
                COUNT(DISTINCT oi.id) as item_count,
                GROUP_CONCAT(DISTINCT p.status) as parcel_statuses
                FROM orders o
                LEFT JOIN parcels p ON o.id = p.order_id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC";
$stmt = $conn->prepare($ordersQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result();

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green mb-4 font-mono uppercase">
                📦 My Orders
            </h1>
            <div class="bg-lime-accent inline-block px-6 py-3 border-4 border-deep-green">
                <p class="text-deep-green font-bold text-xl">Track Your Order History</p>
            </div>
        </div>

        <?php if ($orders->num_rows === 0): ?>
            <!-- No Orders -->
            <div class="card bg-white text-center py-20" data-aos="zoom-in">
                <div class="text-9xl mb-6">📦</div>
                <h2 class="text-3xl font-bold text-gray-600 mb-6">No Orders Yet</h2>
                <p class="text-lg text-gray-500 mb-8">Start shopping to see your orders here</p>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">
                    🛍️ Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <div class="space-y-6">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
                        <!-- Order Header -->
                        <div class="flex flex-wrap justify-between items-center mb-6 pb-6 border-b-4 border-deep-green">
                            <div>
                                <h3 class="text-2xl font-bold text-deep-green mb-2">
                                    Order #<?= htmlspecialchars($order['order_number']) ?>
                                </h3>
                                <p class="text-gray-600">
                                    📅 <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-deep-green">৳<?= number_format($order['total_amount'], 2) ?></p>
                                <p class="text-sm text-gray-600"><?= $order['item_count'] ?> items | <?= $order['parcel_count'] ?> parcels</p>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4 class="font-bold text-deep-green mb-3 text-lg">📋 Delivery Details</h4>
                                <div class="space-y-2 text-sm">
                                    <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                                    <p><strong>Address:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
                                    <p>
                                        <strong>Type:</strong> 
                                        <span class="badge <?= $order['delivery_type'] === 'home' ? 'badge-info' : 'badge-success' ?>">
                                            <?= $order['delivery_type'] === 'home' ? '🏠 Home Delivery' : '🏪 Store Pickup' ?>
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-bold text-deep-green mb-3 text-lg">💰 Payment Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Subtotal:</span>
                                        <span class="font-bold">৳<?= number_format($order['subtotal'], 2) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Delivery Charge:</span>
                                        <span class="font-bold">৳<?= number_format($order['delivery_charge'], 2) ?></span>
                                    </div>
                                    <?php if ($order['points_used'] > 0): ?>
                                    <div class="flex justify-between text-green-600">
                                        <span>Points Discount (<?= $order['points_used'] ?> pts):</span>
                                        <span class="font-bold">- ৳<?= number_format($order['points_discount'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex justify-between text-lg pt-2 border-t-2 border-gray-300">
                                        <span>Total:</span>
                                        <span class="font-bold text-deep-green">৳<?= number_format($order['total_amount'], 2) ?></span>
                                    </div>
                                    <?php if ($order['points_earned'] > 0): ?>
                                    <div class="bg-lime-accent p-2 border-2 border-deep-green text-center">
                                        <span class="font-bold">⭐ Earned <?= $order['points_earned'] ?> Points</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Parcels -->
                        <?php
                        $parcelsQuery = "SELECT p.*, s.name as shop_name, s.city,
                                        COUNT(oi.id) as items_count
                                        FROM parcels p
                                        JOIN shops s ON p.shop_id = s.id
                                        LEFT JOIN order_items oi ON p.id = oi.parcel_id
                                        WHERE p.order_id = ?
                                        GROUP BY p.id";
                        $parcelsStmt = $conn->prepare($parcelsQuery);
                        $parcelsStmt->bind_param("i", $order['id']);
                        $parcelsStmt->execute();
                        $parcels = $parcelsStmt->get_result();
                        ?>

                        <h4 class="font-bold text-deep-green mb-4 text-lg border-t-4 border-deep-green pt-4">
                            📦 Parcel Tracking
                        </h4>

                        <div class="space-y-4">
                            <?php while ($parcel = $parcels->fetch_assoc()): ?>
                                <div class="border-4 border-gray-200 p-4 hover:border-lime-accent transition-all">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="font-bold text-lg">🏪 <?= htmlspecialchars($parcel['shop_name']) ?></p>
                                            <p class="text-sm text-gray-600">📍 <?= htmlspecialchars($parcel['city']) ?></p>
                                            <p class="text-xs text-gray-500">Parcel: <?= htmlspecialchars($parcel['parcel_number']) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <?php
                                            $statusColors = [
                                                'processing' => 'badge-info',
                                                'packed' => 'badge-warning',
                                                'ready' => 'badge-warning',
                                                'out_for_delivery' => 'badge-info',
                                                'delivered' => 'badge-success',
                                                'cancelled' => 'badge-danger'
                                            ];
                                            $statusIcons = [
                                                'processing' => '⏳',
                                                'packed' => '📦',
                                                'ready' => '✅',
                                                'out_for_delivery' => '🚚',
                                                'delivered' => '✅',
                                                'cancelled' => '❌'
                                            ];
                                            ?>
                                            <span class="badge <?= $statusColors[$parcel['status']] ?> text-lg">
                                                <?= $statusIcons[$parcel['status']] ?> <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                                            </span>
                                            <p class="text-sm font-bold mt-2">৳<?= number_format($parcel['subtotal'], 2) ?></p>
                                        </div>
                                    </div>

                                    <!-- Status Timeline -->
                                    <div class="flex items-center justify-between mt-4 bg-gray-50 p-3 border-2 border-gray-200">
                                        <?php
                                        $statuses = ['processing', 'packed', 'ready', 'out_for_delivery', 'delivered'];
                                        $currentIndex = array_search($parcel['status'], $statuses);
                                        ?>
                                        <?php foreach ($statuses as $index => $status): ?>
                                            <div class="flex-1 text-center">
                                                <div class="text-3xl mb-1 <?= $index <= $currentIndex ? 'opacity-100' : 'opacity-30' ?>">
                                                    <?= $statusIcons[$status] ?>
                                                </div>
                                                <p class="text-xs font-bold <?= $index <= $currentIndex ? 'text-deep-green' : 'text-gray-400' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                </p>
                                            </div>
                                            <?php if ($index < count($statuses) - 1): ?>
                                                <div class="flex-1 h-1 <?= $index < $currentIndex ? 'bg-lime-accent' : 'bg-gray-300' ?>"></div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- View Items -->
                                    <button 
                                        onclick="toggleItems('items-<?= $parcel['id'] ?>')"
                                        class="btn btn-outline btn-sm mt-3 w-full"
                                    >
                                        👁️ View <?= $parcel['items_count'] ?> Items
                                    </button>

                                    <!-- Items List (Hidden by default) -->
                                    <div id="items-<?= $parcel['id'] ?>" class="hidden mt-3 space-y-2">
                                        <?php
                                        $itemsQuery = "SELECT oi.*, m.image 
                                                      FROM order_items oi
                                                      LEFT JOIN medicines m ON oi.medicine_id = m.id
                                                      WHERE oi.parcel_id = ?";
                                        $itemsStmt = $conn->prepare($itemsQuery);
                                        $itemsStmt->bind_param("i", $parcel['id']);
                                        $itemsStmt->execute();
                                        $items = $itemsStmt->get_result();
                                        ?>
                                        <?php while ($item = $items->fetch_assoc()): ?>
                                            <div class="flex gap-3 p-3 bg-white border-2 border-gray-200">
                                                <img 
                                                    src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" 
                                                    alt="<?= htmlspecialchars($item['medicine_name']) ?>"
                                                    class="w-16 h-16 object-contain border-2 border-deep-green"
                                                >
                                                <div class="flex-1">
                                                    <p class="font-bold"><?= htmlspecialchars($item['medicine_name']) ?></p>
                                                    <p class="text-sm text-gray-600">Qty: <?= $item['quantity'] ?> × ৳<?= number_format($item['price'], 2) ?></p>
                                                </div>
                                                <p class="font-bold text-deep-green">৳<?= number_format($item['subtotal'], 2) ?></p>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleItems(id) {
    const element = document.getElementById(id);
    element.classList.toggle('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>