<?php
/**
 * Shop Manager - Inventory Management (FIXED FULL CODE)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('shop_manager');

$pageTitle = 'Inventory Management - QuickMed';
$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    $_SESSION['error'] = 'No shop assigned';
    redirect('dashboard.php');
}

// =============================================
// HANDLE FORM SUBMISSIONS
// =============================================

// Handle Add Medicine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medicine'])) {
    $medicineId = intval($_POST['medicine_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $reorder = intval($_POST['reorder_level']);
    $purchasePrice = floatval($_POST['purchase_price']);
    $expiryDate = clean($_POST['expiry_date']);
    
    // Check if exists
    $checkQuery = "SELECT id FROM shop_medicines WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $shopId, $medicineId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Medicine already exists in inventory';
    } else {
        $insertQuery = "INSERT INTO shop_medicines (shop_id, medicine_id, price, stock_quantity, reorder_level, purchase_price, expiry_date, last_restocked) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iididds", $shopId, $medicineId, $price, $stock, $reorder, $purchasePrice, $expiryDate);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Medicine added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add medicine';
        }
    }
    redirect('inventory.php');
}

// Handle Update Stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = intval($_POST['medicine_id']); // actually shop_medicine ID or medicine ID depending on your logic
    // In this context, let's use medicine_id to match the update query
    $newStock = intval($_POST['new_stock']);
    
    $updateQuery = "UPDATE shop_medicines SET stock_quantity = ?, last_restocked = NOW() 
                    WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("iii", $newStock, $shopId, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Stock updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update stock';
    }
    redirect('inventory.php');
}

// =============================================
// DATA FETCHING
// =============================================

// Get medicines for dropdown (Not in shop)
$allMedicinesQuery = "SELECT id, name, power, form FROM medicines 
                      WHERE id NOT IN (SELECT medicine_id FROM shop_medicines WHERE shop_id = ?)
                      ORDER BY name ASC";
$stmt = $conn->prepare($allMedicinesQuery);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$availableMedicines = $stmt->get_result();

// Get Inventory List with Filters
$search = clean($_GET['search'] ?? '');
$lowStock = isset($_GET['low_stock']);

$whereConditions = ["sm.shop_id = ?"];
$params = [$shopId];
$types = "i";

if ($search) {
    $whereConditions[] = "(m.name LIKE ? OR m.generic_name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($lowStock) {
    $whereConditions[] = "sm.stock_quantity <= sm.reorder_level";
}

$whereClause = implode(" AND ", $whereConditions);

$query = "SELECT m.id as med_id, m.name, m.power, m.image, m.generic_name,
          sm.price, sm.stock_quantity, sm.reorder_level, sm.expiry_date, sm.purchase_price
          FROM shop_medicines sm
          JOIN medicines m ON sm.medicine_id = m.id
          WHERE $whereClause
          ORDER BY m.name ASC";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$inventory = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">📦 Inventory Management</h1>
            <div class="flex gap-4">
                <a href="<?= SITE_URL ?>/views/shop_manager/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary text-white font-bold shadow-lg hover:scale-105 transform transition-all">
                    + Add New Medicine
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <input 
                        type="text" 
                        name="search" 
                        class="input border-4 border-deep-green" 
                        placeholder="Search medicine..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>
                
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="low_stock" <?= $lowStock ? 'checked' : '' ?> class="w-5 h-5">
                        <span class="font-bold">Low Stock Only</span>
                    </label>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">🔍 Filter</button>
                    <a href="<?= SITE_URL ?>/views/shop_manager/inventory.php" class="btn btn-outline">Clear</a>
                </div>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Medicine</th>
                            <th>Power</th>
                            <th>Stock</th>
                            <th>Alert Level</th>
                            <th>Price</th>
                            <th>Expiry</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($inventory->num_rows === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-xl text-gray-500">
                                    📭 No medicines found in inventory. Click "+ Add New Medicine" to start.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($item = $inventory->fetch_assoc()): ?>
                                <tr class="<?= $item['stock_quantity'] <= $item['reorder_level'] ? 'bg-red-50' : '' ?>">
                                    <td>
                                        <img 
                                            src="<?= SITE_URL ?>/uploads/medicines/<?= $item['image'] ?? 'placeholder.png' ?>" 
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                            class="w-16 h-16 object-contain border-2 border-deep-green bg-white"
                                            onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                                        >
                                    </td>
                                    <td>
                                        <div class="font-bold text-deep-green"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($item['generic_name']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($item['power']) ?></td>
                                    <td>
                                        <span class="text-2xl font-bold <?= $item['stock_quantity'] <= $item['reorder_level'] ? 'text-red-600' : 'text-green-600' ?>">
                                            <?= $item['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td><?= $item['reorder_level'] ?></td>
                                    <td class="font-bold">৳<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <?php 
                                        if ($item['expiry_date']) {
                                            $expiry = strtotime($item['expiry_date']);
                                            $isExpired = $expiry < time();
                                            echo $isExpired 
                                                ? '<span class="text-red-600 font-bold">Expired</span>' 
                                                : date('M Y', $expiry);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button onclick="updateStock(<?= $item['med_id'] ?>, '<?= htmlspecialchars($item['name']) ?>', <?= $item['stock_quantity'] ?>)" class="btn btn-outline btn-sm border-lime-accent text-deep-green hover:bg-lime-accent">
                                            ✏️ Update
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Update Stock Modal -->
<div id="stockModal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header bg-lime-accent">
            <h3 class="text-2xl font-bold text-deep-green">Update Stock</h3>
            <button onclick="closeStockModal()" class="text-3xl">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="medicine_id" id="updateMedicineId">
                
                <p class="text-lg font-bold mb-4 text-center border-b-2 pb-2" id="medicineName"></p>
                
                <div class="mb-6 text-center">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Current Stock</label>
                    <p class="text-5xl font-bold text-deep-green" id="currentStock"></p>
                </div>
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">New Stock Quantity *</label>
                    <input 
                        type="number" 
                        name="new_stock" 
                        id="newStock"
                        class="input border-4 border-deep-green text-2xl text-center font-bold" 
                        required
                        min="0"
                    >
                </div>
                
                <button type="submit" name="update_stock" class="btn btn-primary w-full text-xl py-4 neon-border">
                    💾 Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Add Medicine Modal -->
<div id="addMedicineModal" class="modal-overlay hidden">
    <div class="modal max-w-3xl">
        <div class="modal-header bg-deep-green text-white">
            <h3 class="text-2xl font-bold">Add Medicine to Inventory</h3>
            <button onclick="closeAddModal()" class="text-3xl text-white">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                
                <!-- Medicine Selection -->
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Select Medicine *</label>
                    <select name="medicine_id" class="input border-4 border-deep-green select2" required style="width: 100%;">
                        <option value="">-- Choose Medicine --</option>
                        <?php 
                        // Reset pointer if reused
                        if ($availableMedicines->num_rows > 0) {
                            $availableMedicines->data_seek(0);
                            while ($med = $availableMedicines->fetch_assoc()): 
                        ?>
                            <option value="<?= $med['id'] ?>">
                                <?= htmlspecialchars($med['name']) ?> (<?= htmlspecialchars($med['power']) ?>) - <?= htmlspecialchars($med['form']) ?>
                            </option>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        Only medicines NOT currently in your inventory are shown.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Selling Price -->
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Selling Price (BDT) *</label>
                        <input type="number" name="price" step="0.01" class="input border-4 border-deep-green" required placeholder="0.00">
                    </div>

                    <!-- Purchase Price -->
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Purchase Price (BDT) *</label>
                        <input type="number" name="purchase_price" step="0.01" class="input border-4 border-deep-green" required placeholder="0.00">
                    </div>

                    <!-- Initial Stock -->
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Initial Stock *</label>
                        <input type="number" name="stock" class="input border-4 border-deep-green" required min="1" placeholder="Quantity">
                    </div>

                    <!-- Reorder Level -->
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Alert Level *</label>
                        <input type="number" name="reorder_level" class="input border-4 border-deep-green" value="10" required>
                    </div>

                    <!-- Expiry Date -->
                    <div class="md:col-span-2">
                        <label class="block font-bold mb-2 text-deep-green">Expiry Date *</label>
                        <input type="date" name="expiry_date" class="input border-4 border-deep-green" required>
                    </div>
                </div>

                <button type="submit" name="add_medicine" class="btn btn-primary w-full mt-6 text-xl py-4 neon-border">
                    📥 Add to Inventory
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Select2 JS (Ensure jQuery is loaded first) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* Fix Select2 Search Focus Issue in Modal */
.select2-container {
    z-index: 999999 !important;
    width: 100% !important;
}

.select2-dropdown {
    z-index: 999999 !important;
    border: 4px solid #065f46 !important;
}

.select2-search__field {
    border: 2px solid #065f46 !important;
    padding: 8px !important;
    font-size: 16px !important;
}

/* Ensure Modal allows focus */
.modal {
    overflow: visible !important; 
}
</style>

<script>
$(document).ready(function() {
    // Initialize Select2 when modal opens
    function initSelect2() {
        $('.select2').select2({
            placeholder: "Search for a medicine...",
            allowClear: true,
            dropdownParent: $('#addMedicineModal'), // Crucial Fix for Modal
            width: '100%'
        });
    }

    // Bind to button click to ensure it initializes correctly
    window.openAddModal = function() {
        document.getElementById('addMedicineModal').classList.remove('hidden');
        initSelect2(); // Re-init or init on open
    };

    window.closeAddModal = function() {
        document.getElementById('addMedicineModal').classList.add('hidden');
        // Optional: destroy to reset
        $('.select2').select2('destroy'); 
    };

    // Update Stock Modal Functions
    window.updateStock = function(id, name, current) {
        document.getElementById('updateMedicineId').value = id;
        document.getElementById('medicineName').textContent = name;
        document.getElementById('currentStock').textContent = current;
        document.getElementById('newStock').value = current;
        document.getElementById('stockModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('newStock').focus(), 100);
    };

    window.closeStockModal = function() {
        document.getElementById('stockModal').classList.add('hidden');
    };

    // Close on outside click
    window.onclick = function(event) {
        const addModal = document.getElementById('addMedicineModal');
        const stockModal = document.getElementById('stockModal');
        if (event.target == addModal) {
            closeAddModal();
        }
        if (event.target == stockModal) {
            closeStockModal();
        }
    };
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>