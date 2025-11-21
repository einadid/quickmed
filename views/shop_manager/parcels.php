<?php
/**
 * Shop Manager - Parcel Management (FIXED & AJAX ENABLED)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('shop_manager');

$pageTitle = 'Manage Parcels - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('dashboard.php');
}

// Get parcels filter
$status = clean($_GET['status'] ?? 'all');

$whereConditions = ["p.shop_id = ?"];
$params = [$shopId];
$types = "i";

if ($status !== 'all') {
    $whereConditions[] = "p.status = ?";
    $params[] = $status;
    $types .= "s";
}

$whereClause = implode(" AND ", $whereConditions);

$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.customer_address, o.delivery_type,
                 COUNT(oi.id) as items_count
                 FROM parcels p
                 JOIN orders o ON p.order_id = o.id
                 LEFT JOIN order_items oi ON p.id = oi.parcel_id
                 WHERE $whereClause
                 GROUP BY p.id
                 ORDER BY p.created_at DESC";

$stmt = $conn->prepare($parcelsQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$parcels = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">🚚 Manage Parcels</h1>
            <a href="<?= SITE_URL ?>/views/shop_manager/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Status Filter -->
        <div class="flex flex-wrap gap-4 mb-8" data-aos="fade-up">
            <a href="?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="?status=processing" class="btn <?= $status === 'processing' ? 'btn-primary' : 'btn-outline' ?>">Processing</a>
            <a href="?status=packed" class="btn <?= $status === 'packed' ? 'btn-primary' : 'btn-outline' ?>">Packed</a>
            <a href="?status=ready" class="btn <?= $status === 'ready' ? 'btn-primary' : 'btn-outline' ?>">Ready</a>
            <a href="?status=out_for_delivery" class="btn <?= $status === 'out_for_delivery' ? 'btn-primary' : 'btn-outline' ?>">Out for Delivery</a>
            <a href="?status=delivered" class="btn <?= $status === 'delivered' ? 'btn-primary' : 'btn-outline' ?>">Delivered</a>
        </div>

        <!-- Parcels List -->
        <div class="space-y-6">
            <?php if ($parcels->num_rows === 0): ?>
                <div class="card bg-white text-center py-20">
                    <div class="text-8xl mb-4">📦</div>
                    <p class="text-xl text-gray-500">No parcels found</p>
                </div>
            <?php else: ?>
                <?php while ($parcel = $parcels->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-deep-green mb-2">
                                    Parcel #<?= htmlspecialchars($parcel['parcel_number']) ?>
                                </h3>
                                <p class="text-gray-600">Order #<?= htmlspecialchars($parcel['order_number']) ?></p>
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
                                ?>
                                <span class="badge <?= $statusColors[$parcel['status']] ?> text-lg mb-2">
                                    <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                                </span>
                                <p class="text-2xl font-bold text-deep-green">৳<?= number_format($parcel['subtotal'], 2) ?></p>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4 class="font-bold text-deep-green mb-3">Customer Details:</h4>
                                <p><strong>Name:</strong> <?= htmlspecialchars($parcel['customer_name']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($parcel['customer_phone']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($parcel['customer_address']) ?></p>
                                <p><strong>Type:</strong> <?= ucfirst($parcel['delivery_type']) ?></p>
                            </div>

                            <div>
                                <h4 class="font-bold text-deep-green mb-3">Parcel Info:</h4>
                                <p><strong>Items:</strong> <?= $parcel['items_count'] ?></p>
                                <p><strong>Created:</strong> <?= date('M d, Y h:i A', strtotime($parcel['created_at'])) ?></p>
                                <?php if ($parcel['delivered_at']): ?>
                                    <p><strong>Delivered:</strong> <?= date('M d, Y h:i A', strtotime($parcel['delivered_at'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex gap-4 mt-4">
                            <?php if ($parcel['status'] !== 'delivered' && $parcel['status'] !== 'cancelled'): ?>
                                <button onclick="openUpdateModal(<?= $parcel['id'] ?>, '<?= $parcel['status'] ?>')" class="btn btn-primary flex-1">
                                    📝 Update Status
                                </button>
                            <?php endif; ?>
                            
                            <a href="order-details.php?id=<?= $parcel['id'] ?>" class="btn btn-outline flex-1">
                                👁️ View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Update Status Modal -->
<div id="statusModal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="text-2xl font-bold">Update Parcel Status</h3>
            <button onclick="closeStatusModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <form id="updateStatusForm">
                <input type="hidden" name="parcel_id" id="parcelId">
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">New Status *</label>
                    <select name="status" id="newStatus" class="input border-4 border-deep-green" required>
                        <option value="processing">Processing</option>
                        <option value="packed">Packed</option>
                        <option value="ready">Ready for Pickup/Delivery</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Remarks (Optional)</label>
                    <textarea name="remarks" id="remarks" rows="3" class="input border-4 border-deep-green" placeholder="Add note..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-full text-xl py-4">
                    ✅ Update Status
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openUpdateModal(id, currentStatus) {
    document.getElementById('parcelId').value = id;
    document.getElementById('newStatus').value = currentStatus;
    document.getElementById('remarks').value = '';
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// Handle AJAX Form Submit
document.getElementById('updateStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerText;
    
    submitBtn.disabled = true;
    submitBtn.innerText = 'Updating...';
    
    try {
        const siteUrl = window.location.origin + '/quickmed';
        const response = await fetch(siteUrl + '/ajax/update_parcel_status.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: result.message,
                confirmButtonColor: '#065f46',
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to update status',
            confirmButtonColor: '#065f46'
        });
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>