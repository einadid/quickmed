<?php
/**
 * Order Details - View Items & Address
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
// Allow both roles
if (!hasRole('shop_manager') && !hasRole('salesman')) {
    redirect('../../index.php');
}

$parcelId = intval($_GET['id'] ?? 0);
$user = getCurrentUser();
$shopId = $user['shop_id'];

// Get parcel details
$parcelQuery = "SELECT p.*, o.order_number, o.customer_name, o.customer_phone, o.customer_address, o.notes
                FROM parcels p
                JOIN orders o ON p.order_id = o.id
                WHERE p.id = ? AND p.shop_id = ?";
$stmt = $conn->prepare($parcelQuery);
$stmt->bind_param("ii", $parcelId, $shopId);
$stmt->execute();
$parcel = $stmt->get_result()->fetch_assoc();

if (!$parcel) {
    $_SESSION['error'] = 'Order not found';
    redirect('online-orders.php');
}

// Get items
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
            <h1 class="text-3xl font-bold text-deep-green uppercase">📋 Order Details</h1>
            <a href="online-orders.php" class="btn btn-outline">← Back</a>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Customer Info -->
            <div class="card bg-white border-4 border-deep-green">
                <h2 class="text-xl font-bold mb-4 border-b-2 pb-2">👤 Customer Information</h2>
                <table class="w-full">
                    <tr>
                        <td class="font-bold py-2">Name:</td>
                        <td><?= htmlspecialchars($parcel['customer_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold py-2">Phone:</td>
                        <td><a href="tel:<?= htmlspecialchars($parcel['customer_phone']) ?>" class="text-blue-600"><?= htmlspecialchars($parcel['customer_phone']) ?></a></td>
                    </tr>
                    <tr>
                        <td class="font-bold py-2 align-top">Address:</td>
                        <td class="bg-off-white p-2 border border-gray-300 text-sm">
                            <?= nl2br(htmlspecialchars($parcel['customer_address'])) ?>
                        </td>
                    </tr>
                    <?php if($parcel['notes']): ?>
                    <tr>
                        <td class="font-bold py-2 align-top text-red-600">Notes:</td>
                        <td class="text-red-600 bg-red-50 p-2 border border-red-200">
                            <?= htmlspecialchars($parcel['notes']) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Order Summary -->
            <div class="card bg-lime-accent border-4 border-deep-green">
                <h2 class="text-xl font-bold mb-4 border-b-2 border-deep-green pb-2 text-deep-green">💰 Order Summary</h2>
                <div class="flex justify-between text-lg mb-2">
                    <span>Order #:</span>
                    <span class="font-mono font-bold"><?= $parcel['order_number'] ?></span>
                </div>
                <div class="flex justify-between text-lg mb-2">
                    <span>Status:</span>
                    <span class="badge badge-info"><?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?></span>
                </div>
                <div class="flex justify-between text-2xl font-bold mt-4 pt-4 border-t-2 border-deep-green">
                    <span>Total Amount:</span>
                    <span>৳<?= number_format($parcel['subtotal'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Medicine List -->
        <div class="card bg-white border-4 border-deep-green mt-8">
            <h2 class="text-xl font-bold mb-4">💊 Medicines Ordered</h2>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Medicine</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" class="w-12 h-12 object-contain border border-gray-300">
                                </td>
                                <td class="font-bold"><?= htmlspecialchars($item['medicine_name']) ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-right">৳<?= number_format($item['price'], 2) ?></td>
                                <td class="text-right font-bold">৳<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

<!-- Action Buttons -->
<div class="flex flex-wrap gap-4 mt-8 p-4 bg-gray-50 border-t-4 border-deep-green">
    <button onclick="window.print()" class="btn btn-outline flex-1">🖨️ Print Invoice</button>
    
    <?php if ($parcel['status'] == 'processing'): ?>
        <button onclick="updateStatus(<?= $parcel['id'] ?>, 'packed')" class="btn btn-warning flex-1 text-black font-bold border-4 border-yellow-600 shadow-lg transform hover:scale-105 transition-all">
            📦 Mark as Packed
        </button>
        
    <?php elseif ($parcel['status'] == 'packed'): ?>
        <button onclick="updateStatus(<?= $parcel['id'] ?>, 'out_for_delivery')" class="btn btn-info flex-1 text-white font-bold border-4 border-blue-600 shadow-lg transform hover:scale-105 transition-all">
            🚚 Out for Delivery
        </button>
        
    <?php elseif ($parcel['status'] == 'out_for_delivery'): ?>
        <button onclick="updateStatus(<?= $parcel['id'] ?>, 'delivered')" class="btn btn-success flex-1 text-white font-bold border-4 border-green-600 shadow-lg transform hover:scale-105 transition-all">
            ✅ Mark Delivered
        </button>
        
    <?php elseif ($parcel['status'] == 'delivered'): ?>
        <button disabled class="btn bg-gray-300 text-gray-600 flex-1 border-2 border-gray-400 cursor-not-allowed">
            ✅ Already Delivered
        </button>
    <?php endif; ?>
</div>
    </div>
</section>

<script>
async function updateStatus(id, status) {
    // Confirmation alert
    const result = await Swal.fire({
        title: 'Update Status?',
        text: `Change status to "${status.toUpperCase()}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Update!'
    });

    if (result.isConfirmed) {
        try {
            // Show loading
            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('parcel_id', id);
            formData.append('status', status);
            
            // Site URL logic
            const siteUrl = window.location.origin + '/quickmed'; // Ensure this matches your folder structure
            
            const response = await fetch(`${siteUrl}/ajax/update_parcel_status.php`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: data.message,
                    confirmButtonColor: '#065f46',
                    timer: 1500
                });
                // Reload page to reflect changes
                window.location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to update status',
                confirmButtonColor: '#065f46'
            });
        }
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>