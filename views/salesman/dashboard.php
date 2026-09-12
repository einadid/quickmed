<?php
/**
 * Salesman Dashboard - QuickMed (Redesigned UI & Live Stats)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$pageTitle = 'Dashboard - Salesman Panel';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned to your account';
    redirect('../../index.php');
}

// 1. Get Shop Info
$shopStmt = $conn->prepare("SELECT * FROM shops WHERE id = ?");
$shopStmt->bind_param("i", $shopId);
$shopStmt->execute();
$shop = $shopStmt->get_result()->fetch_assoc();

// 2. Today's Stats
$today = date('Y-m-d');
$statsQuery = "SELECT 
    COUNT(DISTINCT p.id) as today_orders,
    SUM(p.subtotal) as today_sales,
    COUNT(DISTINCT CASE WHEN p.status = 'delivered' THEN p.id END) as delivered_today,
    COUNT(DISTINCT CASE WHEN p.status = 'returned' THEN p.id END) as returned_today
    FROM parcels p
    WHERE p.shop_id = ? AND DATE(p.created_at) = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("is", $shopId, $today);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// 3. Last 7 Days Sales Chart Data
$chartQuery = "SELECT DATE(created_at) as date, SUM(subtotal) as sales 
               FROM parcels 
               WHERE shop_id = ? AND created_at >= DATE(NOW()) - INTERVAL 7 DAY 
               GROUP BY DATE(created_at) 
               ORDER BY date ASC";
$chartStmt = $conn->prepare($chartQuery);
$chartStmt->bind_param("i", $shopId);
$chartStmt->execute();
$chartResult = $chartStmt->get_result();

$chartLabels = [];
$chartData = [];
while ($row = $chartResult->fetch_assoc()) {
    $chartLabels[] = date('d M', strtotime($row['date']));
    $chartData[] = (float)$row['sales'];
}

// 4. Recent Parcels
$parcelsQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone,
                 COUNT(oi.id) as items_count
                 FROM parcels p
                 JOIN orders o ON p.order_id = o.id
                 LEFT JOIN order_items oi ON p.id = oi.parcel_id
                 WHERE p.shop_id = ?
                 GROUP BY p.id
                 ORDER BY p.created_at DESC
                 LIMIT 8";
$parcelsStmt = $conn->prepare($parcelsQuery);
$parcelsStmt->bind_param("i", $shopId);
$parcelsStmt->execute();
$parcels = $parcelsStmt->get_result();

include __DIR__ . '/../../includes/header.php';

$dashTitle = 'Sales Desk';
$dashSubtitle = '🏪 ' . $shop['name'] . ' · ' . $shop['city'] . ' · ' . date('l, d F Y');
$dashIcon = '🧾';
include __DIR__ . '/../../includes/dashnav.php';
?>

<style>
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.6); display: flex; justify-content: center; align-items: center; z-index: 9999; backdrop-filter: blur(4px); }
    .modal-overlay.hidden { display: none; }
</style>

<section class="container mx-auto px-4 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <a href="pos.php" class="card card-lime card-hover flex items-center gap-4" data-aos="fade-up">
                <span class="text-5xl">🧾</span>
                <span>
                    <span class="block text-xl font-bold text-[#065f46]">Open POS →</span>
                    <span class="block text-sm text-gray-500">Start a new sale</span>
                </span>
            </a>

            <?php qm_stat('📦', (int)($stats['today_orders'] ?? 0), "Orders Today", ''); ?>
            <?php qm_stat('💰', qm_money($stats['today_sales'] ?? 0), 'Sales Today', 'blue'); ?>
            <?php qm_stat('✅', (int)($stats['delivered_today'] ?? 0), 'Delivered · ↩ ' . (int)($stats['returned_today'] ?? 0) . ' returned', 'lime'); ?>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-10">
            <div class="card lg:col-span-2" data-aos="zoom-in">
                <div class="card-header">📊 Weekly Sales Trend</div>
                <div class="relative h-72 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="card flex flex-col" data-aos="fade-left">
                <div class="card-header">⚡ Quick Shortcuts</div>
                <div class="grid gap-3 flex-1">
                    <a href="prescriptions.php" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-green-50 border border-transparent hover:border-green-200 transition group">
                        <span class="text-3xl group-hover:scale-110 transition-transform">📋</span>
                        <span><span class="block font-bold text-gray-800">Prescriptions</span><span class="block text-xs text-gray-500">Process Pending Requests</span></span>
                    </a>
                    <a href="online-orders.php" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition group">
                        <span class="text-3xl group-hover:scale-110 transition-transform">🌐</span>
                        <span><span class="block font-bold text-gray-800">Online Orders</span><span class="block text-xs text-gray-500">Check Web Orders</span></span>
                    </a>
                    <a href="reports.php" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-purple-50 border border-transparent hover:border-purple-200 transition group">
                        <span class="text-3xl group-hover:scale-110 transition-transform">📑</span>
                        <span><span class="block font-bold text-gray-800">Sales Reports</span><span class="block text-xs text-gray-500">View History</span></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="dash-shell" data-aos="fade-up">
            <div class="dash-shell-head"><h2>📦 Recent Transactions</h2></div>

            <?php if ($parcels->num_rows === 0): ?>
                <div class="p-8 text-center">
                    <div class="text-5xl mb-4 opacity-30">📭</div>
                    <p class="text-gray-500 font-medium">No sales records found.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap" style="border:none;border-radius:0;box-shadow:none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Parcel #</th>
                                <th>Customer</th>
                                <th class="text-center">Items</th>
                                <th>Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($parcel = $parcels->fetch_assoc()): ?>
                                <tr class="group">
                                    <td class="font-display text-sm font-bold text-[#065f46]">
                                        <?= htmlspecialchars($parcel['parcel_number']) ?>
                                        <span class="text-xs text-gray-400 font-normal block"><?= date('h:i A', strtotime($parcel['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($parcel['customer_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($parcel['customer_phone']) ?></p>
                                    </td>
                                    <td class="text-center"><span class="badge badge-neutral"><?= (int)$parcel['items_count'] ?></span></td>
                                    <td class="font-bold"><?= qm_money($parcel['subtotal']) ?></td>
                                    <td class="text-center"><?= qm_badge($parcel['status']) ?></td>
                                    <td class="text-right whitespace-nowrap">
                                        <a href="parcel-details.php?id=<?= (int)$parcel['id'] ?>" class="btn btn-outline btn-sm" title="View Details">👁️</a>
                                        <?php if ($parcel['status'] === 'delivered'): ?>
                                            <button onclick="openReturnModal(<?= (int)$parcel['id'] ?>)" class="btn btn-danger btn-sm" title="Return Items">↩</button>
                                        <?php endif; ?>
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
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
        <div class="bg-red-600 text-white px-6 py-4 rounded-t-2xl flex justify-between items-center">
            <h3 class="text-xl font-bold flex items-center gap-2">↩ Process Return</h3>
            <button onclick="closeReturnModal()" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
        </div>
        
        <div class="p-6 max-h-[80vh] overflow-y-auto custom-scroll">
            <form method="POST" action="process_return.php" id="returnForm">
                <input type="hidden" name="parcel_id" id="returnParcelId">
                
                <div class="mb-6">
                    <label class="block font-bold mb-3 text-gray-700 text-sm uppercase tracking-wide">Select Items to Return</label>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 grid grid-cols-12 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <div class="col-span-6">Product</div>
                            <div class="col-span-2 text-center">Price</div>
                            <div class="col-span-2 text-center">Qty</div>
                            <div class="col-span-2 text-center">Return</div>
                        </div>
                        <div id="returnItemsList" class="bg-white divide-y divide-gray-100 max-h-60 overflow-y-auto">
                            <p class="text-center py-8 text-gray-400 animate-pulse">Loading items...</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        ℹ️ Set quantity to <span class="font-bold">0</span> to keep the item.
                    </p>
                </div>
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-gray-700 text-sm uppercase tracking-wide">Reason</label>
                    <textarea name="return_reason" rows="2" class="w-full border-2 border-gray-200 focus:border-red-500 rounded-xl p-3 text-sm outline-none transition-colors" required placeholder="E.g. Damaged, Expired, Wrong Item..."></textarea>
                </div>
                
                <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex gap-3 items-start mb-6">
                    <div class="text-xl">⚠️</div>
                    <div class="text-xs text-red-800">
                        <p class="font-bold mb-1">Important Note:</p>
                        <p>Stock will be automatically restored. <strong>120 Points</strong> will be deducted per 1000 BDT refund value.</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button type="button" onclick="closeReturnModal()" class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg hover:shadow-xl transition transform active:scale-95">
                        Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 1. LIVE CLOCK
function updateClock() {
    const now = new Date();
    var _clk = document.getElementById('liveClock'); if (_clk) _clk.textContent = now.toLocaleTimeString('en-US', { hour12: false });
}
setInterval(updateClock, 1000);
updateClock();

// 2. CHART CONFIG
const ctx = document.getElementById('salesChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(132, 204, 22, 0.4)');
gradient.addColorStop(1, 'rgba(132, 204, 22, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Daily Sales (৳)',
            data: <?= json_encode($chartData) ?>,
            borderColor: '#065f46',
            backgroundColor: gradient,
            borderWidth: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#065f46',
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});

// 3. MODAL LOGIC
async function openReturnModal(id) {
    const modal = document.getElementById('returnModal');
    const content = document.getElementById('modalContent');
    const list = document.getElementById('returnItemsList');
    
    document.getElementById('returnParcelId').value = id;
    modal.classList.remove('hidden');
    
    // Animation
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
    
    list.innerHTML = `<div class="flex justify-center py-8"><span class="loading loading-spinner text-red-500"></span></div>`;

    try {
        const res = await fetch(`../../ajax/get_parcel_items.php?id=${id}`);
        const items = await res.json();
        
        if(items.error || items.length === 0) {
            list.innerHTML = `<p class="text-center py-4 text-red-500 text-sm">Failed to load items.</p>`;
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center hover:bg-gray-50 transition text-sm">
                    <div class="col-span-6 font-medium text-gray-800 truncate pr-2" title="${item.name}">${item.name}</div>
                    <div class="col-span-2 text-center text-gray-500">৳${parseFloat(item.price).toFixed(0)}</div>
                    <div class="col-span-2 text-center"><span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-xs font-bold">${item.quantity}</span></div>
                    <div class="col-span-2">
                        <input type="number" name="return_qty[${item.medicine_id}]" max="${item.quantity}" min="0" value="0"
                               class="w-full border border-gray-300 rounded p-1 text-center text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none">
                    </div>
                </div>
            `;
        });
        list.innerHTML = html;
        
    } catch (e) {
        console.error(e);
        list.innerHTML = `<p class="text-center py-4 text-red-500 text-sm">Network Error.</p>`;
    }
}

function closeReturnModal() {
    const modal = document.getElementById('returnModal');
    const content = document.getElementById('modalContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Close on outside click
document.getElementById('returnModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('returnModal')) closeReturnModal();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>