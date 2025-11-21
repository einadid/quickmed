<?php
/**
 * Shop Manager - Inventory Management (No Price Control)
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
// 1. HANDLE ADD MEDICINE (Prices set to 0)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_medicine') {
    $medicineId = intval($_POST['medicine_id']);
    
    // If medicine_id is 0 (from the hidden input when empty), stop processing
    if ($medicineId === 0) {
        $_SESSION['error'] = 'Invalid medicine selection.';
        header("Location: inventory.php");
        exit();
    }

    $stock = intval($_POST['stock']);
    $reorder = intval($_POST['reorder_level']);
    $expiryDate = clean($_POST['expiry_date']);
    
    // Prices are hardcoded to 0.00. Admin must update them later.
    $price = 0.00;
    $purchasePrice = 0.00;
    
    // Check Duplicate
    $checkQuery = "SELECT id FROM shop_medicines WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $shopId, $medicineId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Medicine already exists in inventory';
    } else {
        // Insert
        $insertQuery = "INSERT INTO shop_medicines (shop_id, medicine_id, price, stock_quantity, reorder_level, purchase_price, expiry_date, last_restocked) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iididds", $shopId, $medicineId, $price, $stock, $reorder, $purchasePrice, $expiryDate);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Medicine added! Please ask Admin to set the price.';
        } else {
            $_SESSION['error'] = 'Failed to add: ' . $stmt->error;
        }
    }
    header("Location: inventory.php");
    exit();
}

// =============================================
// 2. HANDLE UPDATE STOCK (No Price Update)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $medicineId = intval($_POST['medicine_id']);
    $newStock = intval($_POST['new_stock']);
    $newExpiry = clean($_POST['new_expiry']);
    
    // Update query excludes price columns
    $updateQuery = "UPDATE shop_medicines 
                    SET stock_quantity = ?, expiry_date = ?, last_restocked = NOW() 
                    WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("isii", $newStock, $newExpiry, $shopId, $medicineId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Stock updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update: ' . $stmt->error;
    }
    header("Location: inventory.php");
    exit();
}

// =============================================
// 3. DATA FETCHING
// =============================================

// Get medicines available in GLOBAL list but NOT in THIS SHOP
// Using LEFT JOIN instead of NOT IN for better performance & reliability
$availableMedicinesQuery = "SELECT m.id, m.name, m.power, m.form 
                            FROM medicines m 
                            LEFT JOIN shop_medicines sm ON m.id = sm.medicine_id AND sm.shop_id = ?
                            WHERE sm.medicine_id IS NULL
                            ORDER BY m.name ASC";
$stmt = $conn->prepare($availableMedicinesQuery);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$availableMedicines = $stmt->get_result();

// Debug: If no medicines found, check if global medicines exist
if ($availableMedicines->num_rows === 0) {
    // Check if any medicines exist globally
    $globalCheck = $conn->query("SELECT COUNT(*) as total FROM medicines");
    $totalGlobal = $globalCheck->fetch_assoc()['total'];
    
    if ($totalGlobal == 0) {
        $medicineStatus = "No medicines in global database. Ask Admin to add medicines.";
    } else {
        $medicineStatus = "All global medicines are already added to your shop.";
    }
}

// Fetch Inventory List with Filters
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

$inventoryQuery = "SELECT m.id as medicine_id, m.name, m.power, m.image, m.generic_name,
                   sm.price, sm.stock_quantity, sm.reorder_level, sm.expiry_date
                   FROM shop_medicines sm
                   JOIN medicines m ON sm.medicine_id = m.id
                   WHERE $whereClause
                   ORDER BY m.name ASC";

$stmt = $conn->prepare($inventoryQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$inventory = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container { z-index: 999999 !important; width: 100% !important; }
    .select2-dropdown { z-index: 999999 !important; }
    .select2-selection--single { height: 50px !important; display: flex; align-items: center; border: 2px solid #065f46 !important; border-radius: 0.5rem !important; }
    .select2-selection__arrow { height: 48px !important; }
</style>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <div>
                <h1 class="text-4xl font-bold text-deep-green">📦 Inventory Management</h1>
                <p class="text-gray-600">Manage stock levels. Prices are controlled by Admin.</p>
            </div>
            <button onclick="openAddModal()" class="btn btn-primary shadow-lg hover:scale-105 transition-transform">
                + Add Medicine
            </button>
        </div>

        <div class="card bg-white border-4 border-deep-green mb-8 p-4" data-aos="fade-up">
            <form method="GET" class="flex gap-4">
                <input type="text" name="search" class="input flex-grow" placeholder="Search medicine..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">🔍 Search</button>
                <a href="inventory.php" class="btn btn-outline">Reset</a>
            </form>
        </div>

        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-deep-green text-white">
                            <th>Name</th>
                            <th>Power</th>
                            <th>Stock</th>
                            <th>Price (BDT)</th>
                            <th>Expiry</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $inventory->fetch_assoc()): ?>
                            <tr class="<?= $item['stock_quantity'] <= $item['reorder_level'] ? 'bg-red-50' : '' ?>">
                                <td class="font-bold"><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['power']) ?></td>
                                <td>
                                    <span class="<?= $item['stock_quantity'] <= $item['reorder_level'] ? 'text-red-600 font-bold' : 'text-green-600' ?>">
                                        <?= $item['stock_quantity'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($item['price'] > 0): ?>
                                        ৳<?= number_format($item['price'], 2) ?>
                                    <?php else: ?>
                                        <span class="bg-yellow-200 text-yellow-800 text-xs px-2 py-1 rounded-full font-bold">
                                            ⚠️ Pending Admin
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $item['expiry_date'] ? date('M d, Y', strtotime($item['expiry_date'])) : '-' ?>
                                </td>
                                <td>
                                    <button onclick="openUpdateModal(
                                        <?= $item['medicine_id'] ?>, 
                                        '<?= htmlspecialchars($item['name']) ?>', 
                                        <?= $item['stock_quantity'] ?>, 
                                        '<?= $item['expiry_date'] ?>'
                                    )" class="btn btn-outline btn-sm hover:bg-deep-green hover:text-white">
                                        ✏️ Update Stock
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($inventory->num_rows === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500">No medicines found in inventory.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div id="addMedicineModal" class="modal-overlay hidden">
    <div class="modal max-w-2xl w-full">
        <div class="modal-header bg-deep-green text-white">
            <h3 class="text-2xl font-bold">Add Medicine to Branch</h3>
            <button onclick="closeAddModal()" class="text-3xl text-white">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="inventory.php">
                <input type="hidden" name="action" value="add_medicine">
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green">Select Medicine (Global List) *</label>
                    
                    <?php if ($availableMedicines->num_rows > 0): ?>
                        <select name="medicine_id" class="input border-4 border-deep-green select2" required style="width: 100%;">
                            <option value="">-- Search Medicine --</option>
                            <?php 
                            $availableMedicines->data_seek(0);
                            while ($med = $availableMedicines->fetch_assoc()): 
                            ?>
                                <option value="<?= $med['id'] ?>">
                                    <?= htmlspecialchars($med['name']) ?> (<?= htmlspecialchars($med['power']) ?>) - <?= htmlspecialchars($med['form']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select from global database to add to your shop.</p>
                    <?php else: ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 text-yellow-700 rounded-r">
                            <p class="font-bold text-lg">⚠️ <?= $medicineStatus ?? "No new medicines available." ?></p>
                            <p class="text-sm mt-1">All global medicines are already in your inventory, or the global list is empty.</p>
                        </div>
                        <input type="hidden" name="medicine_id" value="0"> <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <?php if ($availableMedicines->num_rows > 0): ?>
                        <div>
                            <label class="block font-bold mb-2 text-deep-green">Stock Qty *</label>
                            <input type="number" name="stock" class="input border-4 border-deep-green" required min="1">
                        </div>
                        <div>
                            <label class="block font-bold mb-2 text-deep-green">Reorder Level</label>
                            <input type="number" name="reorder_level" class="input border-4 border-deep-green" value="10" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold mb-2 text-deep-green">Expiry Date *</label>
                            <input type="date" name="expiry_date" class="input border-4 border-deep-green" required>
                        </div>
                        
                        <div class="col-span-2 bg-blue-50 border-l-4 border-blue-500 p-3">
                            <p class="text-sm text-blue-700 font-bold">
                                ℹ️ Price will be set by Admin (Default: 0.00)
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-6 py-3 text-lg font-bold shadow-md" <?= $availableMedicines->num_rows === 0 ? 'disabled' : '' ?>>
                    📥 Add to Inventory
                </button>
            </form>
        </div>
    </div>
</div>

<div id="updateModal" class="modal-overlay hidden">
    <div class="modal max-w-lg w-full">
        <div class="modal-header bg-lime-accent text-deep-green">
            <h3 class="text-xl font-bold">Update Stock Level</h3>
            <button onclick="closeUpdateModal()" class="text-2xl">&times;</button>
        </div>
        <div class="modal-body p-6">
            <form method="POST" action="inventory.php">
                <input type="hidden" name="action" value="update_stock">
                <input type="hidden" name="medicine_id" id="updateMedId">
                
                <h4 id="updateMedName" class="mb-6 font-bold text-2xl text-center text-deep-green border-b pb-4"></h4>
                
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-deep-green">Current Stock Quantity *</label>
                    <input type="number" name="new_stock" id="updateStock" class="input border-2 border-gray-300 text-xl font-bold text-center" required min="0">
                </div>
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green">Expiry Date *</label>
                    <input type="date" name="new_expiry" id="updateExpiry" class="input border-2 border-gray-300 w-full" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-full py-3 text-lg font-bold">Update Stock</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 inside Modal
    function initSelect2() {
        $('.select2').select2({
            placeholder: "Search medicine name...",
            dropdownParent: $('#addMedicineModal'),
            width: '100%'
        });
    }

    // Open Add Modal
    window.openAddModal = function() {
        document.getElementById('addMedicineModal').classList.remove('hidden');
        initSelect2();
    };

    // Close Add Modal
    window.closeAddModal = function() {
        document.getElementById('addMedicineModal').classList.add('hidden');
    };

    // Open Update Modal
    window.openUpdateModal = function(id, name, stock, expiry) {
        document.getElementById('updateMedId').value = id;
        document.getElementById('updateMedName').textContent = name;
        document.getElementById('updateStock').value = stock;
        document.getElementById('updateExpiry').value = expiry;
        document.getElementById('updateModal').classList.remove('hidden');
    };

    // Close Update Modal
    window.closeUpdateModal = function() {
        document.getElementById('updateModal').classList.add('hidden');
    };

    // Close modals when clicking outside
    window.onclick = function(event) {
        let addModal = document.getElementById('addMedicineModal');
        let updateModal = document.getElementById('updateModal');
        if (event.target == addModal) {
            closeAddModal();
        }
        if (event.target == updateModal) {
            closeUpdateModal();
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>