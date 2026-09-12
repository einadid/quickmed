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

<?php qm_hero('My Orders', 'Track your order history and parcel status.', 'Customer Panel', '📦'); ?>

<section class="container mx-auto px-4 py-10 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <?php if ($orders->num_rows === 0): ?>
            <?php qm_empty('No Orders Yet', 'Start shopping to see your orders here.', '🛍️ Start Shopping', SITE_URL . '/shop.php'); ?>
        <?php else: ?>
            <div class="space-y-6">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="card card-pad-lg" data-aos="fade-up">
                        <div class="flex flex-wrap justify-between items-start gap-4 mb-6 pb-6 border-b border-gray-200">
                            <div>
                                <h3 class="text-2xl font-bold text-[#065f46] mb-1 font-display">
                                    #<?= htmlspecialchars($order['order_number']) ?>
                                </h3>
                                <p class="text-gray-500 text-sm">
                                    📅 <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
                                </p>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1">
                                <p class="text-3xl font-bold text-[#065f46] font-display"><?= qm_money($order['total_amount']) ?></p>
                                <p class="text-sm text-gray-500 mb-2"><?= $order['item_count'] ?> items | <?= $order['parcel_count'] ?> parcel(s)</p>

                                <?php
                                // Check if review already exists for this order
                                $reviewCheck = $conn->query("SELECT id FROM reviews WHERE order_id = " . (int)$order['id']);
                                $hasReviewed = $reviewCheck && $reviewCheck->num_rows > 0;

                                // Check delivery status (If any parcel is delivered)
                                $isDelivered = strpos((string)$order['parcel_statuses'], 'delivered') !== false;

                                if ($isDelivered && !$hasReviewed):
                                ?>
                                    <button onclick="openReviewModal(<?= (int)$order['id'] ?>)" class="btn btn-lime btn-sm">
                                        ⭐ Write Review
                                    </button>
                                <?php elseif ($hasReviewed): ?>
                                    <span class="badge badge-success">✅ Reviewed</span>
                                <?php endif; ?>
                                </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                <h4 class="font-bold text-[#065f46] mb-3">📋 Delivery Details</h4>
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

                            <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                <h4 class="font-bold text-[#065f46] mb-3">💰 Payment Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Subtotal:</span>
                                        <span class="font-bold"><?= qm_money($order['subtotal']) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Delivery Charge:</span>
                                        <span class="font-bold"><?= qm_money($order['delivery_charge']) ?></span>
                                    </div>
                                    <?php if ($order['points_used'] > 0): ?>
                                    <div class="flex justify-between text-green-700">
                                        <span>Points Discount (<?= (int)$order['points_used'] ?> pts):</span>
                                        <span class="font-bold">- <?= qm_money($order['points_discount']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex justify-between text-lg pt-2 border-t border-gray-300">
                                        <span>Total:</span>
                                        <span class="font-bold text-[#065f46]"><?= qm_money($order['total_amount']) ?></span>
                                    </div>
                                    <?php if ($order['points_earned'] > 0): ?>
                                    <div class="bg-[#ecfccb] border border-[#84cc16] rounded-lg p-2 text-center mt-2">
                                        <span class="font-bold text-sm">⭐ Earned <?= (int)$order['points_earned'] ?> Points</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

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

                        <h4 class="font-bold text-[#065f46] mb-4 text-lg border-t border-gray-200 pt-5">
                            📦 Parcel Tracking
                        </h4>

                        <div class="space-y-4">
                            <?php while ($parcel = $parcels->fetch_assoc()):
                                $statusIcons = [
                                    'processing' => '⏳',
                                    'packed' => '📦',
                                    'ready' => '✅',
                                    'out_for_delivery' => '🚚',
                                    'delivered' => '✅',
                                    'cancelled' => '❌',
                                    'returned' => '↩️'
                                ];
                                $parcelIcon = $statusIcons[$parcel['status']] ?? '📦';
                            ?>
                                <div class="border border-gray-200 rounded-xl p-4 hover:border-[#84cc16] transition-all bg-white">
                                    <div class="flex flex-wrap justify-between items-start gap-3 mb-3">
                                        <div>
                                            <p class="font-bold text-lg">🏪 <?= htmlspecialchars($parcel['shop_name']) ?></p>
                                            <p class="text-sm text-gray-500">📍 <?= htmlspecialchars($parcel['city']) ?></p>
                                            <p class="text-xs text-gray-400 font-mono">Parcel: <?= htmlspecialchars($parcel['parcel_number']) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="mr-1"><?= $parcelIcon ?></span><?= qm_badge($parcel['status']) ?>
                                            <p class="text-sm font-bold mt-2"><?= qm_money($parcel['subtotal']) ?></p>
                                        </div>
                                    </div>

                                    <?php if (!in_array($parcel['status'], ['cancelled', 'returned'])): ?>
                                    <div class="flex items-center justify-between mt-4 bg-gray-50 p-3 rounded-xl border border-gray-200">
                                        <?php
                                        $statuses = ['processing', 'packed', 'ready', 'out_for_delivery', 'delivered'];
                                        $currentIndex = array_search($parcel['status'], $statuses);
                                        if ($currentIndex === false) $currentIndex = 0;
                                        ?>
                                        <?php foreach ($statuses as $index => $status): ?>
                                            <div class="flex-1 text-center">
                                                <div class="text-2xl md:text-3xl mb-1 <?= $index <= $currentIndex ? 'opacity-100' : 'opacity-30 grayscale' ?>">
                                                    <?= $statusIcons[$status] ?>
                                                </div>
                                                <p class="text-[10px] md:text-xs font-bold <?= $index <= $currentIndex ? 'text-[#065f46]' : 'text-gray-400' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                                </p>
                                            </div>
                                            <?php if ($index < count($statuses) - 1): ?>
                                                <div class="flex-1 h-1 rounded <?= $index < $currentIndex ? 'bg-[#84cc16]' : 'bg-gray-300' ?>"></div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <button 
                                        onclick="toggleItems('items-<?= $parcel['id'] ?>')"
                                        class="btn btn-outline btn-sm mt-3 w-full"
                                    >
                                        👁️ View <?= $parcel['items_count'] ?> Items
                                    </button>

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

<div id="reviewModal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <span>⭐ Write a Review</span>
            <button onclick="closeReviewModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="submit_review.php">
                <input type="hidden" name="order_id" id="reviewOrderId">

                <div class="mb-6 text-center">
                    <label class="label text-center">Rate your experience</label>
                    <div class="flex justify-center gap-2 text-4xl cursor-pointer">
                        <span onclick="setRating(1)" class="star text-gray-300 hover:text-yellow-400 transition-colors">★</span>
                        <span onclick="setRating(2)" class="star text-gray-300 hover:text-yellow-400 transition-colors">★</span>
                        <span onclick="setRating(3)" class="star text-gray-300 hover:text-yellow-400 transition-colors">★</span>
                        <span onclick="setRating(4)" class="star text-gray-300 hover:text-yellow-400 transition-colors">★</span>
                        <span onclick="setRating(5)" class="star text-gray-300 hover:text-yellow-400 transition-colors">★</span>
                    </div>
                    <input type="hidden" name="rating" id="ratingValue" required>
                </div>

                <div class="mb-6">
                    <label class="label">Your Feedback</label>
                    <textarea name="review_text" rows="4" class="input" placeholder="Tell us about the product and delivery..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">🚀 Submit Review</button>
            </form>
        </div>
    </div>
</div>

<script>
// Existing Toggle Script
function toggleItems(id) {
    const element = document.getElementById(id);
    element.classList.toggle('hidden');
}

// [STEP 1] Review Modal Scripts
function openReviewModal(orderId) {
    document.getElementById('reviewModal').classList.remove('hidden');
    document.getElementById('reviewOrderId').value = orderId;
    // Reset form
    setRating(0); 
    document.querySelector('textarea[name="review_text"]').value = '';
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

function setRating(value) {
    document.getElementById('ratingValue').value = value;
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < value) {
            star.classList.add('text-yellow-400');
            star.classList.remove('text-gray-300');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

// Close modal on outside click
document.getElementById('reviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReviewModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>