<?php
/**
 * Admin - Manage Shops
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Shops - Admin';

// Handle Add/Edit
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
        }
    } else {
        // Insert
        $query = "INSERT INTO shops (name, location, city, phone, email, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $name, $location, $city, $phone, $email, $isActive);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Shop added successfully';
        }
    }
    
    redirect('shops.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $deleteQuery = "DELETE FROM shops WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Shop deleted successfully';
    }
    
    redirect('shops.php');
}

// Get all shops with stats (FIXED LEFT JOIN LOGIC)
$shopsQuery = "SELECT s.*,
    (SELECT COUNT(*) FROM shop_medicines WHERE shop_id = s.id) as products_count,
    (SELECT COUNT(*) FROM users WHERE shop_id = s.id AND is_active = 1) as staff_count,
    (SELECT COUNT(*) FROM parcels WHERE shop_id = s.id) as total_orders,
    (SELECT COALESCE(SUM(subtotal), 0) FROM parcels WHERE shop_id = s.id) as total_revenue
    FROM shops s
    ORDER BY s.created_at DESC";
$shops = $conn->query($shopsQuery);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">🏪 Manage Shops</h1>
            <div class="flex gap-4">
                <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary">+ Add Shop</button>
            </div>
        </div>

        <!-- Shops Grid -->
        <div class="grid md:grid-cols-2 gap-6">
            <?php while ($shop = $shops->fetch_assoc()): ?>
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-deep-green mb-2">
                                <?= htmlspecialchars($shop['name']) ?>
                            </h3>
                            <p class="text-gray-600">📍 <?= htmlspecialchars($shop['location']) ?></p>
                            <p class="text-gray-600">🏙️ <?= htmlspecialchars($shop['city']) ?></p>
                        </div>
                        <?php if ($shop['is_active']): ?>
                            <span class="badge badge-success text-lg">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger text-lg">Inactive</span>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 border-t-2 border-gray-200 pt-4">
                        <div>
                            <p class="text-sm text-gray-600">📞 Phone</p>
                            <p class="font-bold"><?= htmlspecialchars($shop['phone']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">📧 Email</p>
                            <p class="font-bold text-sm"><?= htmlspecialchars($shop['email']) ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-4 mb-4 bg-off-white p-4 border-2 border-gray-200">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-deep-green"><?= $shop['products_count'] ?></p>
                            <p class="text-xs text-gray-600">Products</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-deep-green"><?= $shop['staff_count'] ?></p>
                            <p class="text-xs text-gray-600">Staff</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-deep-green"><?= $shop['total_orders'] ?></p>
                            <p class="text-xs text-gray-600">Orders</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xl font-bold text-lime-accent">৳<?= number_format($shop['total_revenue'] ?? 0, 0) ?></p>
                            <p class="text-xs text-gray-600">Revenue</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button onclick='editShop(<?= json_encode($shop) ?>)' class="btn btn-outline flex-1">
                            ✏️ Edit
                        </button>
                        <a href="<?= SITE_URL ?>/views/admin/shop-inventory.php?shop=<?= $shop['id'] ?>" class="btn btn-outline flex-1">
                            📦 Inventory
                        </a>
                        <button onclick="deleteShop(<?= $shop['id'] ?>)" class="btn btn-outline border-red-500 text-red-600">
                            🗑️
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Add/Edit Modal -->
<!-- Add/Edit Modal - FIXED CLOSE -->
<div id="shopModal" class="modal-overlay hidden" onclick="closeModalOnOutside(event)">
    <div class="modal max-w-3xl" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="text-2xl font-bold" id="modalTitle">Add Shop</h3>
            <button type="button" onclick="closeModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" onsubmit="return validateShopForm()">
                <input type="hidden" name="id" id="shopId">
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block font-bold mb-2 text-deep-green text-lg">Shop Name *</label>
                        <input type="text" name="name" id="shopName" class="input border-4 border-deep-green" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold mb-2 text-deep-green text-lg">Location Address *</label>
                        <input type="text" name="location" id="shopLocation" class="input border-4 border-deep-green" required placeholder="Area, Street, etc.">
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green text-lg">City *</label>
                        <select name="city" id="shopCity" class="input border-4 border-deep-green" required>
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

                    <div>
                        <label class="block font-bold mb-2 text-deep-green text-lg">Phone *</label>
                        <input type="tel" name="phone" id="shopPhone" class="input border-4 border-deep-green" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold mb-2 text-deep-green text-lg">Email</label>
                        <input type="email" name="email" id="shopEmail" class="input border-4 border-deep-green">
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" id="shopActive" class="w-6 h-6" checked>
                            <span class="font-bold text-deep-green">Active Shop</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-4 mt-6">
                    <button type="submit" name="save_shop" class="btn btn-primary flex-1 text-xl py-4">
                        💾 Save Shop
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-outline flex-1 text-xl py-4">
                        ✕ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('shopModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Shop';
    document.querySelector('#shopModal form').reset();
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

function closeModalOnOutside(event) {
    if (event.target.id === 'shopModal') {
        closeModal();
    }
}

function validateShopForm() {
    const name = document.getElementById('shopName').value.trim();
    const city = document.getElementById('shopCity').value;
    const phone = document.getElementById('shopPhone').value.trim();
    
    if (!name || !city || !phone) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please fill all required fields',
            confirmButtonColor: '#065f46'
        });
        return false;
    }
    return true;
}

async function deleteShop(id) {
    const result = await Swal.fire({
        title: 'Delete Shop?',
        text: 'This will affect all related data. Are you sure?',
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

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<script>
function openAddModal() {
    document.getElementById('shopModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Shop';
    document.querySelector('form').reset();
    document.getElementById('shopId').value = '';
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
        text: 'This will affect all related data. Are you sure?',
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
</script>

<?php include __DIR__ . '/../../includes/header.php'; ?>