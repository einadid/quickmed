<?php
/**
 * Salesman Dashboard - POS & Orders
 * Updated: Partial Return Support
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$pageTitle = 'Salesman Dashboard - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned to your account';
    redirect('../../index.php');
}

// Get shop info
$shopQuery = "SELECT * FROM shops WHERE id = ?";
$shopStmt = $conn->prepare($shopQuery);
$shopStmt->bind_param("i", $shopId);
$shopStmt->execute();
$shop = $shopStmt->get_result()->fetch_assoc();

// Today's stats
$today = date('Y-m-d');
$statsQuery = "SELECT 
    COUNT(DISTINCT p.id) as today_orders,
    SUM(p.subtotal) as today_sales,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_today
    FROM parcels p
    WHERE p.shop_id = ? AND DATE(p.created_at) = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("is", $shopId, $today);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// Recent parcels
$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone,
                 COUNT(oi.id) as items_count
                 FROM parcels p
                 JOIN orders o ON p.order_id = o.id
                 LEFT JOIN order_items oi ON p.id = oi.parcel_id
                 WHERE p.shop_id = ?
                 GROUP BY p.id
                 ORDER BY p.created_at DESC
                 LIMIT 10";
$parcelsStmt = $conn->prepare($parcelsQuery);
$parcelsStmt->bind_param("i", $shopId);
$parcelsStmt->execute();
$parcels = $parcelsStmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* Simple Modal Styles ensuring visibility */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 50;
    }
    .modal-overlay.hidden {
        display: none;
    }
    /* Custom scrollbar for item list */
    #returnItemsList::-webkit-scrollbar {
        width: 6px;
    }
    #returnItemsList::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 3px;
    }
</style>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-12" data-aos="fade-down">
            <div>
                <h1 class="text-5xl font-bold text-deep-green mb-2 font-mono uppercase">
                    👨‍💼 Salesman Dashboard
                </h1>
                <p class="text-xl text-gray-600">
                    🏪 <?= htmlspecialchars($shop['name']) ?> - <?= htmlspecialchars($shop['city']) ?>
                </p>
            </div>
            <a href="<?= SITE_URL ?>/views/salesman/pos.php" class="btn btn-primary btn-lg neon-border">
                🧾 Open POS System
            </a>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <div class="card bg-lime-accent border-4 border-deep-green" data-aos="fade-up" data-aos-delay="0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">TODAY'S ORDERS</p>
                        <p class="text-5xl font-bold text-deep-green"><?= $stats['today_orders'] ?? 0 ?></p>
                    </div>
                    <div class="text-6xl">📦</div>
                </div>
            </div>
            
            
            <div class="card bg-white border-4 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">TODAY'S SALES</p>
                        <p class="text-4xl font-bold text-deep-green">৳<?= number_format($stats['today_sales'] ?? 0, 2) ?></p>
                    </div>
                    <div class="text-6xl">💰</div>
                </div>
            </div>
            
            <div class="card bg-white border-4 border-lime-accent" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-deep-green mb-2">DELIVERED TODAY</p>
                        <p class="text-5xl font-bold text-lime-accent"><?= $stats['delivered_today'] ?? 0 ?></p>
                    </div>
                    <div class="text-6xl">✅</div>
                </div>
            </div>
           
        </div>
        
        <div>
            <a href="online-orders.php" class="btn btn-outline btn-lg border-4 border-lime-accent text-deep-green hover:bg-lime-accent w-full py-6 flex flex-col items-center justify-center gap-2 transform hover:scale-105 transition-all shadow-lg mb-12">
                <span class="text-4xl">🌐</span>
                <span class="font-bold text-xl">View Online Orders</span>
                <span class="text-sm">Check delivery address & items</span>
            </a>
        </div>
         <div>
                <a href="reports.php" class="btn btn-outline btn-lg border-4 border-lime-accent text-deep-green hover:bg-lime-accent w-full py-6 flex flex-col items-center justify-center gap-2 transform hover:scale-105 transition-all shadow-lg">
    <span class="text-4xl">📈</span>
    <span class="font-bold text-xl">Sales Reports</span>
    <span class="text-sm">Filter by Date & Type</span>
</a>
            </div>

        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📋 Recent Sales
            </h2>

            <?php if ($parcels->num_rows === 0): ?>
                <div class="text-center py-12">
                    <div class="text-8xl mb-4">📦</div>
                    <p class="text-xl text-gray-500">No orders yet</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Parcel #</th>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($parcel = $parcels->fetch_assoc()): ?>
                                <tr>
                                    <td class="font-mono font-bold"><?= htmlspecialchars($parcel['parcel_number']) ?></td>
                                    <td class="font-mono"><?= htmlspecialchars($parcel['order_number']) ?></td>
                                    <td>
                                        <div class="font-bold"><?= htmlspecialchars($parcel['customer_name']) ?></div>
                                        <div class="text-sm text-gray-600"><?= htmlspecialchars($parcel['customer_phone']) ?></div>
                                    </td>
                                    <td><?= $parcel['items_count'] ?></td>
                                    <td class="font-bold">৳<?= number_format($parcel['subtotal'], 2) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'processing' => 'badge-info',
                                            'packed' => 'badge-warning',
                                            'ready' => 'badge-warning',
                                            'out_for_delivery' => 'badge-info',
                                            'delivered' => 'badge-success',
                                            'returned' => 'badge-error',
                                            'cancelled' => 'badge-error'
                                        ];
                                        $status = $parcel['status'];
                                        $color = $statusColors[$status] ?? 'badge-ghost';
                                        ?>
                                        <span class="badge <?= $color ?>">
                                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, h:i A', strtotime($parcel['created_at'])) ?></td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="<?= SITE_URL ?>/views/salesman/parcel-details.php?id=<?= $parcel['id'] ?>" class="btn btn-outline btn-sm">
                                                View
                                            </a>
                                            
                                            <?php if ($parcel['status'] === 'delivered'): ?>
                                                <button onclick="openReturnModal(<?= $parcel['id'] ?>)" class="btn btn-sm border-red-500 text-red-600 hover:bg-red-50 hover:border-red-600">
                                                    ↩ Return
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<div id="returnModal" class="modal-overlay hidden">
    <div class="modal bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all p-0 overflow-hidden">
        <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold">↩ Process Return</h3>
            <button onclick="closeReturnModal()" class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
        </div>
        
        <div class="p-6">
            <form method="POST" action="process_return.php" id="returnForm">
                <input type="hidden" name="parcel_id" id="returnParcelId">
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-gray-700">Select Items & Quantity to Return:</label>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 grid grid-cols-12 text-sm font-bold text-gray-600">
                            <div class="col-span-6">Item Name</div>
                            <div class="col-span-2 text-center">Price</div>
                            <div class="col-span-2 text-center">Sold Qty</div>
                            <div class="col-span-2 text-center">Return</div>
                        </div>
                        <div id="returnItemsList" class="max-h-60 overflow-y-auto bg-white">
                            <p class="text-center py-8 text-gray-500">Loading items...</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">* Set quantity to 0 to exclude item from return.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-gray-700">Reason for Return</label>
                    <textarea name="return_reason" rows="2" class="input w-full border-2 border-red-300 focus:border-red-600 rounded-lg p-2" required placeholder="Damaged, Wrong Item, Expired..."></textarea>
                </div>
                
                <div class="bg-red-50 p-4 text-sm text-red-800 mb-6 border border-red-200 rounded-lg flex gap-2 items-start">
                    <span class="text-lg">⚠️</span>
                    <div>
                        <p class="font-bold">Warning:</p>
                        <p>Stock will be restored. <strong>Penalty:</strong> 120 Points deducted per 1000 BDT refunded.</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeReturnModal()" class="btn w-1/2 btn-outline border-gray-300 text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="btn w-1/2 bg-red-600 text-white hover:bg-red-700 border-none">
                        Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function openReturnModal(id) {
    document.getElementById('returnParcelId').value = id;
    document.getElementById('returnModal').classList.remove('hidden');
    
    const list = document.getElementById('returnItemsList');
    list.innerHTML = `
        <div class="flex justify-center items-center py-8 text-gray-500">
            <span class="loading loading-spinner loading-md mr-2"></span> Loading items...
        </div>
    `;
    
    try {
        // Important: Ensure this path points to your AJAX handler
        const response = await fetch(`../../ajax/get_parcel_items.php?id=${id}`);
        const items = await response.json();
        
        if(items.error) {
            list.innerHTML = `<p class="text-center py-4 text-red-500">${items.error}</p>`;
            return;
        }
        
        if (items.length === 0) {
            list.innerHTML = `<p class="text-center py-4 text-gray-500">No items found for this parcel.</p>`;
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="grid grid-cols-12 gap-2 px-4 py-3 border-b border-gray-100 items-center hover:bg-gray-50 text-sm">
                    <div class="col-span-6 font-medium text-gray-800">
                        ${item.name}
                    </div>
                    <div class="col-span-2 text-center text-gray-600">
                        ৳${parseFloat(item.price).toFixed(2)}
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="badge badge-ghost">${item.quantity}</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <input type="number" 
                               name="return_qty[${item.medicine_id}]"
                               max="${item.quantity}" 
                               min="0" 
                               value="0"
                               class="w-full border border-gray-300 rounded p-1 text-center focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors"
                        >
                    </div>
                </div>
            `;
        });
        
        list.innerHTML = html;
        
    } catch (e) {
        console.error(e);
        list.innerHTML = '<p class="text-red-500 text-center py-4">Error loading items. Please try again.</p>';
    }
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
}

// Close modal if clicked outside
document.getElementById('returnModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnModal();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>