<?php
/**
 * Admin - Manage Shops (FIXED & OPTIMIZED)
 */

// Enable Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Shops - Admin';

// =============================================
// HANDLE ADD / EDIT SHOP
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_shop'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = clean($_POST['name']);
    $location = clean($_POST['location']);
    $city = clean($_POST['city']);
    $phone = clean($_POST['phone']);
    $email = clean($_POST['email']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id > 0) {
        // Update
        $query = "UPDATE shops SET name=?, location=?, city=?, phone=?, email=?, is_active=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssii", $name, $location, $city, $phone, $email, $isActive, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Shop updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update shop: ' . $stmt->error;
        }
    } else {
        // Insert
        $query = "INSERT INTO shops (name, location, city, phone, email, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $name, $location, $city, $phone, $email, $isActive);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Shop added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add shop: ' . $stmt->error;
        }
    }
    redirect('views/admin/shops.php');
}

// =============================================
// HANDLE DELETE SHOP
// =============================================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Safety Check: Prevent deletion if orders exist
    $checkOrders = $conn->query("SELECT id FROM parcels WHERE shop_id = $id LIMIT 1");
    if ($checkOrders->num_rows > 0) {
        $_SESSION['error'] = 'Cannot delete shop with existing orders. Deactivate instead.';
    } else {
        $deleteQuery = "DELETE FROM shops WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Shop deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete shop';
        }
    }
    redirect('views/admin/shops.php');
}

// =============================================
// FETCH SHOPS WITH STATS (FIXED QUERY)
// =============================================
$shopsQuery = "SELECT s.*,
    (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = s.id) as products_count,
    (SELECT COUNT(*) FROM users WHERE shop_id = s.id AND is_active = 1) as staff_count,
    (SELECT COUNT(*) FROM parcels WHERE shop_id = s.id) as total_orders,
    (SELECT COALESCE(SUM(subtotal), 0) FROM parcels WHERE shop_id = s.id) as total_revenue
    FROM shops s
    ORDER BY s.created_at DESC";

$shops = $conn->query($shopsQuery);

if (!$shops) {
    die("Database Error: " . $conn->error); // Show error if query fails
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">🏪 Manage Shops</h1>
            <div class="flex gap-4">
                <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary">+ Add Shop</button>
            </div>
        </div>

        <!-- Shops Grid -->
        <div class="grid md:grid-cols-2 gap-6">
            <?php if ($shops->num_rows === 0): ?>
                <div class="col-span-2 text-center py-20 text-gray-500">
                    <div class="text-6xl mb-4">🏪</div>
                    <p class="text-xl">No shops found. Add your first shop!</p>
                </div>
            <?php else: ?>
                <?php while ($shop = $shops->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green hover:shadow-xl transition-all" data-aos="fade-up">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-deep-green mb-1">
                                    <?= htmlspecialchars($shop['name']) ?>
                                </h3>
                                <p class="text-gray-600 text-sm flex items-center gap-1">
                                    <span>📍</span> <?= htmlspecialchars($shop['location']) ?>, <?= htmlspecialchars($shop['city']) ?>
                                </p>
                            </div>
                            <?php if ($shop['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4 border-t-2 border-gray-100 pt-4">
                            <div>
                                <p class="text-xs text-gray-500 font-bold uppercase">Phone</p>
                                <p class="font-mono"><?= htmlspecialchars($shop['phone']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-bold uppercase">Email</p>
                                <p class="truncate text-sm"><?= htmlspecialchars($shop['email']) ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 mb-4 bg-gray-50 p-3 rounded border border-gray-200 text-center">
                            <div>
                                <p class="text-xl font-bold text-deep-green"><?= $shop['products_count'] ?></p>
                                <p class="text-[10px] text-gray-500 uppercase">Items</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-deep-green"><?= $shop['staff_count'] ?></p>
                                <p class="text-[10px] text-gray-500 uppercase">Staff</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-deep-green"><?= $shop['total_orders'] ?></p>
                                <p class="text-[10px] text-gray-500 uppercase">Orders</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-lime-600">৳<?= number_format_short($shop['total_revenue']) ?></p>
                                <p class="text-[10px] text-gray-500 uppercase">Sales</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button onclick='editShop(<?= json_encode($shop) ?>)' class="btn btn-outline btn-sm flex-1">
                                ✏️ Edit
                            </button>
                            <a href="<?= SITE_URL ?>/views/admin/shop-inventory.php?shop_id=<?= $shop['id'] ?>" class="btn btn-primary btn-sm flex-1 text-center">
                                📦 Inventory
                            </a>
                            <?php if ($shop['total_orders'] == 0): ?>
                                <button onclick="deleteShop(<?= $shop['id'] ?>)" class="btn btn-outline btn-sm border-red-500 text-red-600 px-3">
                                    🗑️
                                </button>
                            <?php else: ?>
                                <button disabled class="btn btn-sm bg-gray-100 text-gray-400 cursor-not-allowed px-3" title="Cannot delete shop with orders">
                                    🔒
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Add/Edit Modal -->
<div id="shopModal" class="modal-overlay hidden">
    <div class="modal max-w-2xl w-full">
        <div class="modal-header bg-deep-green text-white">
            <h3 class="text-2xl font-bold" id="modalTitle">Add Shop</h3>
            <button onclick="closeModal()" class="text-3xl">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="id" id="shopId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block font-bold mb-1 text-deep-green">Shop Name *</label>
                        <input type="text" name="name" id="shopName" class="input w-full border-2 border-deep-green" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold mb-1 text-deep-green">Location *</label>
                            <input type="text" name="location" id="shopLocation" class="input w-full border-2 border-deep-green" required>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-deep-green">City *</label>
                            <select name="city" id="shopCity" class="input w-full border-2 border-deep-green" required>
                                <option value="">Select City</option>
                                <option value="Dhaka">Dhaka</option>
                                <option value="Chittagong">Chittagong</option>
                                <option value="Sylhet">Sylhet</option>
                                <option value="Rajshahi">Rajshahi</option>
                                <option value="Barishal">Barishal</option>
                                <option value="Khulna">Khulna</option>
                                <option value="Rangpur">Rangpur</option>
                                <option value="Mymensingh">Mymensingh</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold mb-1 text-deep-green">Phone *</label>
                            <input type="tel" name="phone" id="shopPhone" class="input w-full border-2 border-deep-green" required>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-deep-green">Email</label>
                            <input type="email" name="email" id="shopEmail" class="input w-full border-2 border-deep-green">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" name="is_active" id="shopActive" class="w-5 h-5 accent-deep-green" checked>
                        <label for="shopActive" class="font-bold text-deep-green cursor-pointer">Active Shop</label>
                    </div>
                </div>

                <button type="submit" name="save_shop" class="btn btn-primary w-full mt-6 py-3 text-lg font-bold shadow-lg">
                    💾 Save Shop
                </button>
            </form>
        </div>
    </div>
</div>

<?php
// Helper for short number format (1K, 1M)
function number_format_short($n) {
    if ($n >= 0 && $n < 1000) {
        return number_format($n);
    }
    $k = $n / 1000;
    if ($k < 1000) {
        return number_format($k, 1) . 'K';
    }
    $m = $k / 1000;
    return number_format($m, 1) . 'M';
}
?>

<script>
function openAddModal() {
    document.getElementById('shopModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Shop';
    document.querySelector('form').reset();
    document.getElementById('shopId').value = '';
    document.getElementById('shopActive').checked = true;
}

function editShop(shop) {
    document.getElementById('shopModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Shop';
    document.getElementById('shopId').value = shop.id;
    document.getElementById('shopName').value = shop.name;
    document.getElementById('shopLocation').value = shop.location;
    document.getElementById('shopCity').value = shop.city;
    document.getElementById('shopPhone').value = shop.phone;
    document.getElementById('shopEmail').value = shop.email || '';
    document.getElementById('shopActive').checked = shop.is_active == 1;
}

function closeModal() {
    document.getElementById('shopModal').classList.add('hidden');
}

async function deleteShop(id) {
    const result = await Swal.fire({
        title: 'Delete Shop?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        window.location.href = '?delete=' + id;
    }
}

// Close on outside click
window.onclick = function(event) {
    const modal = document.getElementById('shopModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>