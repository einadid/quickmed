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
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 3px; }
    
    /* Modal Overlay */
    .modal-overlay {
        position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.6);
        display: flex; justify-content: center; align-items: center; z-index: 9999;
        backdrop-filter: blur(4px);
    }
    .modal-overlay.hidden { display: none; }
</style>

<section class="bg-gray-50 min-h-screen pb-20">
    
    <div class="bg-gradient-to-br from-deep-green to-green-900 text-white pt-24 pb-32 relative overflow-hidden rounded-b-[3rem] shadow-xl">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <div class="absolute top-10 right-20 text-9xl transform rotate-12">🛒</div>
            <div class="absolute bottom-10 left-20 text-9xl transform -rotate-12">🧾</div>
        </div>

        <div class="container mx-auto px-6 relative z-10 flex flex-col md:flex-row justify-between items-center">
            <div data-aos="fade-right">
                <div class="flex items-center gap-3 mb-3">
                    <span class="bg-lime-accent text-deep-green text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-md">Salesman Panel</span>
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lime-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-lime-500"></span>
                    </span>
                    <span class="text-lime-accent text-sm font-mono">Online</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-2 tracking-tight">
                    Welcome Back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>! 👋
                </h1>
                <p class="text-gray-200 text-lg flex items-center gap-2 opacity-90">
                    🏪 <?= htmlspecialchars($shop['name']) ?> <span class="text-lime-accent">•</span> <?= htmlspecialchars($shop['city']) ?>
                </p>
            </div>
            
            <div class="text-right mt-8 md:mt-0 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 shadow-lg" data-aos="fade-left">
                <div class="text-5xl font-mono font-bold text-lime-accent drop-shadow-md" id="liveClock">00:00:00</div>
                <div class="text-gray-200 text-lg font-medium"><?= date('l, d F Y') ?></div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-24 relative z-20">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <a href="pos.php" class="bg-lime-accent p-6 rounded-2xl shadow-lg border-4 border-white transform hover:-translate-y-2 hover:scale-[1.02] transition-all duration-300 group flex flex-col justify-between h-full relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-8xl opacity-20 text-deep-green group-hover:scale-110 transition-transform">🧾</div>
                <div>
                    <h3 class="text-2xl font-bold text-deep-green mb-1">Open POS</h3>
                    <p class="text-deep-green opacity-80 text-sm font-medium">Start New Sale</p>
                </div>
                <div class="mt-4 bg-white/30 w-12 h-12 rounded-full flex items-center justify-center text-2xl group-hover:rotate-90 transition-transform">➜</div>
            </a>

            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-deep-green" data-aos="fade-up" data-aos-delay="100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Orders Today</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-1"><?= $stats['today_orders'] ?? 0 ?></h3>
                    </div>
                    <div class="bg-green-50 p-3 rounded-full text-2xl">📦</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-blue-500" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Sales Today</p>
                        <h3 class="text-4xl font-bold text-deep-green mt-1">৳<?= number_format($stats['today_sales'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-full text-2xl">💰</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-purple-500" data-aos="fade-up" data-aos-delay="300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Delivered</p>
                        <h3 class="text-4xl font-bold text-purple-600 mt-1"><?= $stats['delivered_today'] ?? 0 ?></h3>
                        <p class="text-xs text-gray-400 mt-1">Returns: <span class="text-red-500 font-bold"><?= $stats['returned_today'] ?? 0 ?></span></p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-full text-2xl">✅</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 mb-10">
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100" data-aos="zoom-in">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-deep-green flex items-center gap-2">
                        📊 Weekly Sales Trend
                    </h3>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 flex flex-col" data-aos="fade-left">
                <h3 class="text-xl font-bold text-deep-green mb-6">⚡ Quick Shortcuts</h3>
                
                <div class="grid gap-4 flex-1">
                    <a href="prescriptions.php" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-green-50 border border-transparent hover:border-green-200 transition group">
                        <div class="bg-white p-3 rounded-full shadow-sm text-2xl group-hover:scale-110 transition-transform">📋</div>
                        <div>
                            <h4 class="font-bold text-gray-800">Prescriptions</h4>
                            <p class="text-xs text-gray-500">Process Pending Requests</p>
                        </div>
                    </a>

                    <a href="online-orders.php" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition group">
                        <div class="bg-white p-3 rounded-full shadow-sm text-2xl group-hover:scale-110 transition-transform">🌐</div>
                        <div>
                            <h4 class="font-bold text-gray-800">Online Orders</h4>
                            <p class="text-xs text-gray-500">Check Web Orders</p>
                        </div>
                    </a>

                    <a href="reports.php" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-purple-50 border border-transparent hover:border-purple-200 transition group">
                        <div class="bg-white p-3 rounded-full shadow-sm text-2xl group-hover:scale-110 transition-transform">📑</div>
                        <div>
                            <h4 class="font-bold text-gray-800">Sales Reports</h4>
                            <p class="text-xs text-gray-500">View History</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100" data-aos="fade-up">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-deep-green flex items-center gap-2">
                    📦 Recent Transactions
                </h3>
            </div>
            
            <?php if ($parcels->num_rows === 0): ?>
                <div class="text-center py-16">
                    <div class="text-6xl mb-4 opacity-20">📭</div>
                    <p class="text-gray-500 font-medium">No sales records found today.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Parcel #</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4 text-center">Items</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($parcel = $parcels->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition duration-200 group">
                                    <td class="px-6 py-4 font-mono text-sm font-bold text-deep-green">
                                        <?= htmlspecialchars($parcel['parcel_number']) ?>
                                        <br><span class="text-xs text-gray-400 font-normal"><?= date('h:i A', strtotime($parcel['created_at'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($parcel['customer_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($parcel['customer_phone']) ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold"><?= $parcel['items_count'] ?></span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-800">৳<?= number_format($parcel['subtotal'], 2) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $statusColors = [
                                            'delivered' => 'bg-green-100 text-green-700',
                                            'returned' => 'bg-red-100 text-red-700',
                                            'processing' => 'bg-blue-100 text-blue-700',
                                            'packed' => 'bg-yellow-100 text-yellow-700',
                                            'cancelled' => 'bg-gray-100 text-gray-600'
                                        ];
                                        $statusClass = $statusColors[$parcel['status']] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $statusClass ?>">
                                            <?= ucfirst($parcel['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="parcel-details.php?id=<?= $parcel['id'] ?>" class="p-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition" title="View Details">
                                                👁️
                                            </a>
                                            <?php if ($parcel['status'] === 'delivered'): ?>
                                                <button onclick="openReturnModal(<?= $parcel['id'] ?>)" class="p-2 bg-red-50 text-red-600 rounded hover:bg-red-100 transition" title="Return Items">
                                                    ↩
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
    document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
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