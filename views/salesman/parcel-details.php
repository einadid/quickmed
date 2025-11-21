<?php
/**
 * Salesman - Parcel Details
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$pageTitle = 'Parcel Details - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

$parcelId = intval($_GET['id'] ?? 0);

if (!$parcelId) {
    $_SESSION['error'] = 'Invalid parcel';
    redirect('dashboard.php');
}

// Get parcel details
$parcelQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.customer_address, 
                o.delivery_type, o.notes as order_notes,
                s.name as shop_name, s.location, s.city as shop_city
                FROM parcels p
                JOIN orders o ON p.order_id = o.id
                JOIN shops s ON p.shop_id = s.id
                WHERE p.id = ? AND p.shop_id = ?";
$stmt = $conn->prepare($parcelQuery);
$stmt->bind_param("ii", $parcelId, $shopId);
$stmt->execute();
$parcel = $stmt->get_result()->fetch_assoc();

if (!$parcel) {
    $_SESSION['error'] = 'Parcel not found';
    redirect('dashboard.php');
}

// Get parcel items
$itemsQuery = "SELECT oi.*, m.image 
               FROM order_items oi
               LEFT JOIN medicines m ON oi.medicine_id = m.id
               WHERE oi.parcel_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $parcelId);
$itemsStmt->execute();
$items = $itemsStmt->get_result();

// Get status history
$historyQuery = "SELECT psl.*, u.full_name as updated_by_name
                 FROM parcel_status_logs psl
                 LEFT JOIN users u ON psl.updated_by = u.id
                 WHERE psl.parcel_id = ?
                 ORDER BY psl.created_at DESC";
$historyStmt = $conn->prepare($historyQuery);
$historyStmt->bind_param("i", $parcelId);
$historyStmt->execute();
$history = $historyStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">📦 Parcel Details</h1>
                <p class="text-xl text-gray-600 mt-2">#{<?= htmlspecialchars($parcel['parcel_number']) ?></p>
            </div>
            <a href="<?= SITE_URL ?>/views/salesman/dashboard.php" class="btn btn-outline">← Back</a>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Delivery Information -->
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-right">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    🚚 Delivery Information
                </h2>

                <div class="space-y-4">
                    <div class="bg-lime-accent p-4 border-2 border-deep-green">
                        <p class="text-sm font-bold text-deep-green mb-1">Customer Name</p>
                        <p class="text-2xl font-bold"><?= htmlspecialchars($parcel['customer_name']) ?></p>
                    </div>

                    <div class="bg-off-white p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-deep-green mb-1">📱 Phone Number</p>
                        <p class="text-xl font-bold">
                            <a href="tel:<?= htmlspecialchars($parcel['customer_phone']) ?>" class="text-deep-green hover:text-lime-accent">
                                <?= htmlspecialchars($parcel['customer_phone']) ?>
                            </a>
                        </p>
                    </div>

                    <div class="bg-off-white p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-deep-green mb-1">📍 Delivery Address</p>
                        <p class="text-lg"><?= nl2br(htmlspecialchars($parcel['customer_address'])) ?></p>
                    </div>

                    <div class="bg-off-white p-4 border-2 border-gray-300">
                        <p class="text-sm font-bold text-deep-green mb-1">📦 Delivery Type</p>
                        <p class="text-lg font-bold">
                            <?= $parcel['delivery_type'] === 'home' ? '🏠 Home Delivery' : '🏪 Store Pickup' ?>
                        </p>
                    </div>

                    <?php if ($parcel['order_notes']): ?>
                        <div class="bg-yellow-50 p-4 border-2 border-yellow-500">
                            <p class="text-sm font-bold text-yellow-800 mb-1">📝 Special Notes</p>
                            <p class="text-gray-700"><?= htmlspecialchars($parcel['order_notes']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Google Maps Link -->
                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($parcel['customer_address']) ?>" 
                   target="_blank" 
                   class="btn btn-primary w-full mt-6">
                    🗺️ Open in Google Maps
                </a>
            </div>

            <!-- Parcel Info & Items -->
            <div class="space-y-6">
                <!-- Parcel Status -->
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                    <h2 class="text-2xl font-bold text-deep-green mb-4 uppercase border-b-4 border-deep-green pb-3">
                        📊 Parcel Status
                    </h2>

                    <?php
                    $statusColors = [
                        'processing' => 'badge-info',
                        'packed' => 'badge-warning',
                        'ready' => 'badge-warning',
                        'out_for_delivery' => 'badge-info',
                        'delivered' => 'badge-success',
                        'cancelled' => 'badge-danger'
                    ];
                    ?>
                    <div class="text-center mb-4">
                        <span class="badge <?= $statusColors[$parcel['status']] ?> text-2xl px-6 py-3">
                            <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center bg-gray-50 p-3 border-2 border-gray-300">
                            <p class="text-sm text-gray-600">Order #</p>
                            <p class="font-bold font-mono"><?= htmlspecialchars($parcel['order_number']) ?></p>
                        </div>
                        <div class="text-center bg-gray-50 p-3 border-2 border-gray-300">
                            <p class="text-sm text-gray-600">Items</p>
                            <p class="font-bold text-2xl text-deep-green"><?= $parcel['items_count'] ?></p>
                        </div>
                    </div>

                    <div class="text-center bg-lime-accent p-4 border-2 border-deep-green">
                        <p class="text-sm font-bold text-deep-green">Total Amount</p>
                        <p class="text-4xl font-bold text-deep-green">৳<?= number_format($parcel['subtotal'], 2) ?></p>
                    </div>
                </div>

                <!-- Parcel Items -->
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left" data-aos-delay="100">
                    <h2 class="text-2xl font-bold text-deep-green mb-4 uppercase border-b-4 border-deep-green pb-3">
                        📋 Items List
                    </h2>

                    <div class="space-y-3">
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <div class="flex gap-3 p-3 bg-gray-50 border-2 border-gray-300">
                                <img 
                                    src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" 
                                    alt="<?= htmlspecialchars($item['medicine_name']) ?>"
                                    class="w-16 h-16 object-contain border-2 border-deep-green"
                                >
                                <div class="flex-1">
                                    <p class="font-bold text-deep-green"><?= htmlspecialchars($item['medicine_name']) ?></p>
                                    <p class="text-sm text-gray-600">Qty: <?= $item['quantity'] ?> × ৳<?= number_format($item['price'], 2) ?></p>
                                </div>
                                <p class="font-bold text-lg text-deep-green">৳<?= number_format($item['subtotal'], 2) ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status History -->
        <div class="card bg-white border-4 border-deep-green mt-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📜 Status History
            </h2>

            <div class="space-y-3">
                <?php while ($log = $history->fetch_assoc()): ?>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 border-2 border-gray-300">
                        <div class="text-4xl">
                            <?php
                            $icons = [
                                'processing' => '⏳',
                                'packed' => '📦',
                                'ready' => '✅',
                                'out_for_delivery' => '🚚',
                                'delivered' => '✅',
                                'cancelled' => '❌'
                            ];
                            echo $icons[$log['status']] ?? '📌';
                            ?>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg text-deep-green"><?= ucfirst(str_replace('_', ' ', $log['status'])) ?></p>
                            <?php if ($log['remarks']): ?>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($log['remarks']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-1">
                                By: <?= htmlspecialchars($log['updated_by_name']) ?> • 
                                <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 mt-8" data-aos="fade-up">
            <button onclick="window.print()" class="btn btn-primary flex-1">
                🖨️ Print Details
            </button>
            <a href="tel:<?= htmlspecialchars($parcel['customer_phone']) ?>" class="btn btn-outline flex-1">
                📞 Call Customer
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>