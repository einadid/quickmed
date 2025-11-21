<?php
/**
 * Manage Parcels & Returns (FIXED)
 */

require_once __DIR__ . '/../../config.php';
requireLogin();

$user = getCurrentUser();
$shopId = $user['shop_id'];

// Handle Return Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_order'])) {
    $parcelId = intval($_POST['parcel_id']);
    $reason = clean($_POST['return_reason']);
    
    $conn->begin_transaction();
    try {
        // Update status to returned
        $updateQuery = "UPDATE parcels SET status = 'returned' WHERE id = ? AND shop_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $parcelId, $shopId);
        $stmt->execute();
        
        // Log return
        $logQuery = "INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by) VALUES (?, 'returned', ?, ?)";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param("isi", $parcelId, $reason, $user['id']);
        $stmt->execute();
        
        // Restore stock
        $itemsQuery = "SELECT medicine_id, quantity FROM order_items WHERE parcel_id = ?";
        $stmt = $conn->prepare($itemsQuery);
        $stmt->bind_param("i", $parcelId);
        $stmt->execute();
        $items = $stmt->get_result();
        
        while ($item = $items->fetch_assoc()) {
            $stockQuery = "UPDATE shop_medicines SET stock_quantity = stock_quantity + ? WHERE shop_id = ? AND medicine_id = ?";
            $stmt = $conn->prepare($stockQuery);
            $stmt->bind_param("iii", $item['quantity'], $shopId, $item['medicine_id']);
            $stmt->execute();
        }
        
        $conn->commit();
        $_SESSION['success'] = 'Order returned successfully & stock restored!';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Return failed: ' . $e->getMessage();
    }
    header("Location: parcels.php");
    exit();
}

// Search Logic
$search = clean($_GET['search'] ?? '');
$whereClause = "p.shop_id = $shopId";

if (!empty($search)) {
    $whereClause .= " AND (p.parcel_number LIKE '%$search%' OR o.order_number LIKE '%$search%' OR o.customer_phone LIKE '%$search%')";
}

$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone 
                 FROM parcels p 
                 JOIN orders o ON p.order_id = o.id 
                 WHERE $whereClause 
                 ORDER BY p.created_at DESC LIMIT 20";
$parcels = $conn->query($parcelsQuery);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-deep-green">📦 Manage Orders</h1>
            
            <!-- Search Bar -->
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="input border-4 border-deep-green w-64" placeholder="Search Invoice / Phone...">
                <button type="submit" class="btn btn-primary">🔍</button>
                <?php if($search): ?>
                    <a href="parcels.php" class="btn btn-outline">✕</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="grid gap-6">
            <?php if ($parcels->num_rows === 0): ?>
                <div class="text-center py-12 text-gray-500">No orders found</div>
            <?php else: ?>
                <?php while ($parcel = $parcels->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green p-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-deep-green">#<?= htmlspecialchars($parcel['parcel_number']) ?></h3>
                            <p class="text-gray-600">Customer: <?= htmlspecialchars($parcel['customer_name']) ?> (<?= htmlspecialchars($parcel['customer_phone']) ?>)</p>
                            <span class="badge badge-info mt-2"><?= ucfirst($parcel['status']) ?></span>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-2xl font-bold text-deep-green">৳<?= number_format($parcel['subtotal'], 2) ?></p>
                            <div class="flex gap-2 mt-2">
                                <a href="order-details.php?id=<?= $parcel['id'] ?>" class="btn btn-sm btn-outline">View</a>
                                
                                <?php if ($parcel['status'] === 'delivered'): ?>
                                    <button onclick="openReturnModal(<?= $parcel['id'] ?>)" class="btn btn-sm btn-outline border-red-500 text-red-600 hover:bg-red-50">
                                        ↩ Return
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Return Modal -->
<div id="returnModal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header bg-red-600 text-white">
            <h3 class="text-xl font-bold">↩ Return Order</h3>
            <button onclick="closeReturnModal()" class="text-2xl">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="return_order" value="1">
                <input type="hidden" name="parcel_id" id="returnParcelId">
                
                <div class="mb-4">
                    <label class="block font-bold mb-2">Reason for Return</label>
                    <textarea name="return_reason" rows="3" class="input border-red-500" required placeholder="Why is the customer returning this?"></textarea>
                </div>
                
                <p class="text-sm text-red-600 mb-4">⚠️ This will mark the order as returned and restore stock quantity.</p>
                
                <button type="submit" class="btn w-full bg-red-600 text-white hover:bg-red-700 border-red-800">Confirm Return</button>
            </form>
        </div>
    </div>
</div>

<script>
function openReturnModal(id) {
    document.getElementById('returnParcelId').value = id;
    document.getElementById('returnModal').classList.remove('hidden');
}
function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>