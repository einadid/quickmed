<?php
/**
 * Shop Manager - Manage Parcels, Search & Returns
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
// Allow Shop Manager (and Admin if needed)
$user = getCurrentUser();
if (!in_array($user['role_name'], ['shop_manager', 'salesman', 'admin'])) {
    redirect('../../index.php');
}

$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned to your account.';
    redirect('dashboard.php');
}

// ==========================================
// 1. HANDLE RETURN REQUEST (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_order'])) {
    $parcelId = intval($_POST['parcel_id']);
    $reason = clean($_POST['return_reason']);
    
    $conn->begin_transaction();
    try {
        // Verify parcel belongs to shop and is delivered
        $checkQuery = "SELECT status FROM parcels WHERE id = ? AND shop_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $parcelId, $shopId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 0) {
            throw new Exception("Parcel not found or access denied.");
        }
        $parcelData = $res->fetch_assoc();
        
        if ($parcelData['status'] !== 'delivered') {
            throw new Exception("Only delivered parcels can be returned.");
        }

        // Update status to returned
        $updateQuery = "UPDATE parcels SET status = 'returned', updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $parcelId);
        $stmt->execute();
        
        // Log return in parcel_status_logs
        $logQuery = "INSERT INTO parcel_status_logs (parcel_id, status, remarks, updated_by, created_at) VALUES (?, 'returned', ?, ?, NOW())";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param("isi", $parcelId, $reason, $user['id']);
        $stmt->execute();
        
        // RESTORE STOCK
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
    
    // Redirect to prevent form resubmission
    header("Location: parcels.php");
    exit();
}

// ==========================================
// 2. HANDLE SEARCH & FILTER (GET)
// ==========================================

$status = clean($_GET['status'] ?? 'all');
$search = clean($_GET['search'] ?? '');

// Base Query Conditions
$whereConditions = ["p.shop_id = ?"];
$params = [$shopId];
$types = "i";

// Add Status Filter
if ($status !== 'all') {
    $whereConditions[] = "p.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Add Search Filter (Invoice or Phone)
if (!empty($search)) {
    $whereConditions[] = "(p.parcel_number LIKE ? OR o.order_number LIKE ? OR o.customer_phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

$whereClause = implode(" AND ", $whereConditions);

// Main Query
$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.customer_address, o.delivery_type,
                 COUNT(oi.id) as items_count
                 FROM parcels p
                 JOIN orders o ON p.order_id = o.id
                 LEFT JOIN order_items oi ON p.id = oi.parcel_id
                 WHERE $whereClause
                 GROUP BY p.id
                 ORDER BY p.created_at DESC";

$stmt = $conn->prepare($parcelsQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$parcels = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4" data-aos="fade-down">
            <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">📦 Manage Parcels</h1>
            
            <form method="GET" class="flex gap-2 w-full md:w-auto">
                <?php if($status !== 'all'): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                <?php endif; ?>
                
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       class="input border-4 border-deep-green w-full md:w-64" 
                       placeholder="Invoice / Phone...">
                
                <button type="submit" class="btn btn-primary">🔍</button>
                
                <?php if($search): ?>
                    <a href="parcels.php" class="btn btn-outline text-red-500 border-red-500 hover:bg-red-50">✕</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="flex flex-wrap gap-2 mb-8 overflow-x-auto pb-2" data-aos="fade-up">
            <?php 
            $tabs = [
                'all' => 'All',
                'processing' => 'Processing',
                'packed' => 'Packed',
                'ready' => 'Ready',
                'out_for_delivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'returned' => 'Returned',
                'cancelled' => 'Cancelled'
            ];
            foreach ($tabs as $key => $label): 
                $activeClass = ($status === $key) ? 'btn-primary' : 'btn-outline border-gray-300 text-gray-600 hover:border-deep-green hover:text-deep-green';
            ?>
                <a href="?status=<?= $key ?><?= $search ? '&search='.urlencode($search) : '' ?>" 
                   class="btn btn-sm whitespace-nowrap <?= $activeClass ?>">
                   <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="space-y-6">
            <?php if ($parcels->num_rows === 0): ?>
                <div class="card bg-white text-center py-20 border-2 border-dashed border-gray-300">
                    <div class="text-6xl mb-4 opacity-50">📦</div>
                    <p class="text-xl text-gray-500 font-bold">No parcels found</p>
                    <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <?php while ($parcel = $parcels->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green hover:shadow-lg transition-shadow" data-aos="fade-up">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                            
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-2xl font-bold text-deep-green">
                                        #<?= htmlspecialchars($parcel['parcel_number']) ?>
                                    </h3>
                                    <?php
                                    $statusColors = [
                                        'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'packed' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'ready' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'out_for_delivery' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'delivered' => 'bg-green-100 text-green-800 border-green-200',
                                        'returned' => 'bg-red-100 text-red-800 border-red-200',
                                        'cancelled' => 'bg-gray-200 text-gray-800 border-gray-300'
                                    ];
                                    $sClass = $statusColors[$parcel['status']] ?? 'bg-gray-100';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $sClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                                    </span>
                                </div>
                                
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p><strong>Customer:</strong> <?= htmlspecialchars($parcel['customer_name']) ?> (<a href="tel:<?= htmlspecialchars($parcel['customer_phone']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($parcel['customer_phone']) ?></a>)</p>
                                    <p><strong>Order Ref:</strong> <?= htmlspecialchars($parcel['order_number']) ?></p>
                                    <p><strong>Items:</strong> <?= $parcel['items_count'] ?> | <strong>Date:</strong> <?= date('M d, h:i A', strtotime($parcel['created_at'])) ?></p>
                                </div>
                            </div>
                            
                            <div class="text-right w-full md:w-auto flex flex-col items-end justify-between h-full">
                                <div class="mb-4">
                                    <p class="text-3xl font-bold text-deep-green">৳<?= number_format($parcel['subtotal'], 2) ?></p>
                                </div>
                                
                                <div class="flex gap-2 w-full md:w-auto">
                                    <a href="order-details.php?id=<?= $parcel['id'] ?>" class="btn btn-sm btn-outline flex-1 md:flex-none">
                                        👁️ View
                                    </a>

                                    <?php if ($parcel['status'] === 'delivered'): ?>
                                        <button onclick="openReturnModal(<?= $parcel['id'] ?>)" 
                                                class="btn btn-sm bg-white border-2 border-red-500 text-red-600 hover:bg-red-50 font-bold flex-1 md:flex-none flex items-center justify-center gap-1">
                                            ↩ Return
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<div id="returnModal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
        <div class="bg-red-600 text-white p-4 flex justify-between items-center">
            <h3 class="text-xl font-bold flex items-center gap-2">
                ↩ Process Return
            </h3>
            <button onclick="closeReturnModal()" class="text-2xl hover:text-red-200 font-bold">&times;</button>
        </div>
        
        <div class="p-6">
            <form method="POST">
                <input type="hidden" name="return_order" value="1">
                <input type="hidden" name="parcel_id" id="returnParcelId">
                
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <p class="text-red-700 text-sm font-bold">
                        ⚠️ Warning: This action is irreversible.
                    </p>
                    <ul class="text-red-600 text-xs list-disc list-inside mt-1">
                        <li>Status will change to "Returned"</li>
                        <li>Stock will be automatically restored</li>
                    </ul>
                </div>
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-gray-700">Reason for Return *</label>
                    <textarea name="return_reason" rows="3" 
                              class="w-full border-2 border-gray-300 p-3 rounded focus:outline-none focus:border-red-500 transition-colors" 
                              required 
                              placeholder="e.g., Damaged product, Wrong item sent..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeReturnModal()" class="flex-1 btn bg-gray-200 text-gray-700 hover:bg-gray-300 border-0">Cancel</button>
                    <button type="submit" class="flex-1 btn bg-red-600 text-white hover:bg-red-700 border-red-800">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReturnModal(id) {
    document.getElementById('returnParcelId').value = id;
    document.getElementById('returnModal').classList.remove('hidden');
    // Prevent background scrolling
    document.body.style.overflow = 'hidden';
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close on outside click
document.getElementById('returnModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnModal();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>