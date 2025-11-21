<?php
/**
 * Order Details - View Items & Address (FIXED PERMISSIONS)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();

// ✅ Allow BOTH Salesman AND Shop Manager
$user = getCurrentUser();
$allowedRoles = ['salesman', 'shop_manager'];

if (!isset($user['role_name']) || !in_array($user['role_name'], $allowedRoles)) {
    $_SESSION['error'] = 'Access denied. Insufficient permissions.';
    redirect('../../index.php');
}

$parcelId = intval($_GET['id'] ?? 0);
$shopId = $user['shop_id'];

// Get parcel details with strict shop validation
$parcelQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.customer_address, o.notes
                FROM parcels p
                JOIN orders o ON p.order_id = o.id
                WHERE p.id = ? AND p.shop_id = ?";
$stmt = $conn->prepare($parcelQuery);
$stmt->bind_param("ii", $parcelId, $shopId);
$stmt->execute();
$parcel = $stmt->get_result()->fetch_assoc();

if (!$parcel) {
    $_SESSION['error'] = 'Order not found or access denied for this shop.';
    redirect('online-orders.php');
}

// Get items in the parcel
$itemsQuery = "SELECT oi.*, m.image 
               FROM order_items oi
               JOIN medicines m ON oi.medicine_id = m.id
               WHERE oi.parcel_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $parcelId);
$stmt->execute();
$items = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-5xl mx-auto">
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-deep-green uppercase flex items-center gap-2">
                📋 Order Details <span class="text-sm bg-gray-200 text-gray-700 px-2 py-1 rounded ml-2">#<?= htmlspecialchars($parcel['order_number']) ?></span>
            </h1>
            <a href="online-orders.php" class="btn btn-outline flex items-center gap-2">
                ← Back to Orders
            </a>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="card bg-white border-4 border-deep-green shadow-lg">
                <h2 class="text-xl font-bold mb-4 border-b-2 pb-2 flex items-center gap-2">
                    👤 Customer Information
                </h2>
                <table class="w-full text-sm">
                    <tr>
                        <td class="font-bold py-2 w-24">Name:</td>
                        <td><?= htmlspecialchars($parcel['customer_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold py-2">Phone:</td>
                        <td>
                            <a href="tel:<?= htmlspecialchars($parcel['customer_phone']) ?>" class="text-blue-600 font-bold hover:underline">
                                <?= htmlspecialchars($parcel['customer_phone']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold py-2 align-top">Address:</td>
                        <td class="bg-gray-50 p-2 border border-gray-200 rounded">
                            <?= nl2br(htmlspecialchars($parcel['customer_address'])) ?>
                        </td>
                    </tr>
                    <?php if($parcel['notes']): ?>
                    <tr>
                        <td class="font-bold py-2 align-top text-red-600">Notes:</td>
                        <td class="text-red-600 bg-red-50 p-2 border border-red-200 rounded font-bold">
                            <?= htmlspecialchars($parcel['notes']) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="card bg-lime-accent border-4 border-deep-green shadow-lg">
                <h2 class="text-xl font-bold mb-4 border-b-2 border-deep-green pb-2 text-deep-green flex items-center gap-2">
                    💰 Parcel Summary
                </h2>
                
                <div class="flex justify-between text-lg mb-3">
                    <span class="font-bold text-deep-green">Current Status:</span>
                    <?php
                        $statusColors = [
                            'processing' => 'bg-blue-100 text-blue-800 border-blue-300',
                            'packed' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'out_for_delivery' => 'bg-purple-100 text-purple-800 border-purple-300',
                            'delivered' => 'bg-green-100 text-green-800 border-green-300',
                            'cancelled' => 'bg-red-100 text-red-800 border-red-300'
                        ];
                        $statusClass = $statusColors[$parcel['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="px-3 py-1 rounded-full text-sm font-bold border <?= $statusClass ?>">
                        <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                    </span>
                </div>

                <div class="flex justify-between text-lg mb-2">
                    <span>Parcel ID:</span>
                    <span class="font-mono font-bold"><?= $parcel['parcel_number'] ?></span>
                </div>
                
                <div class="flex justify-between text-2xl font-bold mt-6 pt-4 border-t-2 border-deep-green">
                    <span>Total Amount:</span>
                    <span>৳<?= number_format($parcel['subtotal'], 2) ?></span>
                </div>
            </div>
        </div>

        <div class="card bg-white border-4 border-deep-green mt-8 shadow-lg">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                💊 Medicines in Parcel
            </h2>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-gray-100 text-deep-green">
                        <tr>
                            <th class="p-3 text-left">Image</th>
                            <th class="p-3 text-left">Medicine Name</th>
                            <th class="p-3 text-center">Quantity</th>
                            <th class="p-3 text-right">Unit Price</th>
                            <th class="p-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3">
                                    <img src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" 
                                         class="w-12 h-12 object-contain border border-gray-300 bg-white rounded">
                                </td>
                                <td class="p-3 font-bold text-gray-700">
                                    <?= htmlspecialchars($item['medicine_name']) ?>
                                </td>
                                <td class="p-3 text-center font-mono">
                                    <?= $item['quantity'] ?>
                                </td>
                                <td class="p-3 text-right font-mono text-gray-600">
                                    ৳<?= number_format($item['price'], 2) ?>
                                </td>
                                <td class="p-3 text-right font-bold font-mono text-deep-green">
                                    ৳<?= number_format($item['subtotal'], 2) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 mt-8 p-4 bg-gray-100 border-2 border-gray-300 rounded-lg">
            <button onclick="window.print()" class="btn bg-gray-600 hover:bg-gray-700 text-white flex-1 py-3 font-bold flex items-center justify-center gap-2">
                🖨️ Print Invoice
            </button>
            
            <?php if ($parcel['status'] == 'processing'): ?>
                <button onclick="updateParcelStatus(<?= $parcel['id'] ?>, 'packed')" class="btn bg-yellow-500 hover:bg-yellow-600 text-black flex-1 py-3 font-bold border-2 border-yellow-700">
                    📦 Mark as Packed
                </button>
            <?php elseif ($parcel['status'] == 'packed'): ?>
                <button onclick="updateParcelStatus(<?= $parcel['id'] ?>, 'out_for_delivery')" class="btn bg-blue-500 hover:bg-blue-600 text-white flex-1 py-3 font-bold border-2 border-blue-700">
                    🚚 Out for Delivery
                </button>
            <?php elseif ($parcel['status'] == 'out_for_delivery'): ?>
                <button onclick="updateParcelStatus(<?= $parcel['id'] ?>, 'delivered')" class="btn bg-green-600 hover:bg-green-700 text-white flex-1 py-3 font-bold border-2 border-green-800">
                    ✅ Mark Delivered
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function updateParcelStatus(parcelId, status) {
    if(!confirm('Are you sure you want to change status to ' + status.replace(/_/g, ' ').toUpperCase() + '?')) {
        return;
    }

    const formData = new FormData();
    formData.append('parcel_id', parcelId);
    formData.append('status', status);

    // Using the AJAX endpoint we fixed earlier
    fetch('../../ajax/update_parcel_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Success: ' + data.message);
            location.reload(); // Refresh to see changes
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Connection failed. Check console.');
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>