<?php
/**
 * Shop Manager - Inventory Management (FINAL FIX)
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
// HANDLE ADD MEDICINE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_medicine') {
    $medicineId = intval($_POST['medicine_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $reorder = intval($_POST['reorder_level']);
    $purchasePrice = floatval($_POST['purchase_price']);
    $expiryDate = clean($_POST['expiry_date']);
    
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
            $_SESSION['success'] = 'Medicine added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add: ' . $stmt->error;
        }
    }
    header("Location: inventory.php");
    exit();
}

// =============================================
// HANDLE UPDATE STOCK
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $medicineId = intval($_POST['medicine_id']);
    $newStock = intval($_POST['new_stock']);
    $newPrice = floatval($_POST['new_price']);
    $newExpiry = clean($_POST['new_expiry']);
    
    $updateQuery = "UPDATE shop_medicines 
                    SET stock_quantity = ?, price = ?, expiry_date = ?, last_restocked = NOW() 
                    WHERE shop_id = ? AND medicine_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("idsii", $newStock, $newPrice, $newExpiry, $shopId, $medicineId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Stock updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update: ' . $stmt->error;
    }
    header("Location: inventory.php");
    exit();
}

// =============================================
// DATA FETCHING
// =============================================

// Available medicines
$availableMedicinesQuery = "SELECT id, name, power, form 
                            FROM medicines 
                            WHERE id NOT IN (SELECT medicine_id FROM shop_medicines WHERE shop_id = ?)
                            ORDER BY name ASC";
$stmt = $conn->prepare($availableMedicinesQuery);
$stmt->bind_param("i", $shopId);
$stmt->execute();
$availableMedicines = $stmt->get_result();

// Inventory list
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

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container { z-index: 999999 !important; width: 100% !important; }
    .select2-dropdown { z-index: 999999 !important; }
</style>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-deep-green">📦 Inventory</h1>
            <button onclick="openAddModal()" class="btn btn-primary">+ Add Medicine</button>
        </div>

        <div class="card bg-white border-4 border-deep-green">
            <table class="table w-full">
                <thead>
                    <tr class="bg-deep-green text-white">
                        <th>Name</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Expiry</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $inventory->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['stock_quantity'] ?></td>
                            <td>৳<?= $item['price'] ?></td>
                            <td><?= $item['expiry_date'] ?></td>
                            <td>
                                <button onclick="openUpdateModal(
                                    <?= $item['medicine_id'] ?>, 
                                    '<?= htmlspecialchars($item['name']) ?>', 
                                    <?= $item['stock_quantity'] ?>, 
                                    <?= $item['price'] ?>, 
                                    '<?= $item['expiry_date'] ?>'
                                )" class="btn btn-outline btn-sm">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Add Modal -->
<div id="addMedicineModal" class="modal-overlay hidden">
    <div class="modal max-w-lg w-full">
        <div class="modal-header bg-deep-green text-white">
            <h3>Add Medicine</h3>
            <button onclick="closeAddModal()">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="inventory.php">
                <!-- Hidden Field for Action -->
                <input type="hidden" name="action" value="add_medicine">
                
                <div class="mb-4">
                    <label>Medicine</label>
                    <select name="medicine_id" class="input select2" required>
                        <option value="">Select</option>
                        <?php 
                        if ($availableMedicines->num_rows > 0) {
                            $availableMedicines->data_seek(0);
                            while ($med = $availableMedicines->fetch_assoc()): 
                        ?>
                            <option value="<?= $med['id'] ?>">
                                <?= htmlspecialchars($med['name']) ?> (<?= htmlspecialchars($med['power']) ?>)
                            </option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" name="stock" placeholder="Stock" class="input" required>
                    <input type="number" name="reorder_level" value="10" placeholder="Alert" class="input" required>
                    <input type="number" name="price" step="0.01" placeholder="Sell Price" class="input" required>
                    <input type="number" name="purchase_price" step="0.01" placeholder="Buy Price" class="input" required>
                    <input type="date" name="expiry_date" class="input col-span-2" required>
                </div>
                <button type="submit" class="btn btn-primary w-full mt-4">Save</button>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal-overlay hidden">
    <div class="modal max-w-lg w-full">
        <div class="modal-header bg-lime-accent">
            <h3>Update Stock</h3>
            <button onclick="closeUpdateModal()">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="inventory.php">
                <!-- Hidden Field for Action -->
                <input type="hidden" name="action" value="update_stock">
                <input type="hidden" name="medicine_id" id="updateMedId">
                
                <h4 id="updateMedName" class="mb-4 font-bold text-center"></h4>
                
                <label>Stock:</label>
                <input type="number" name="new_stock" id="updateStock" class="input mb-2" required>
                
                <label>Price:</label>
                <input type="number" name="new_price" id="updatePrice" step="0.01" class="input mb-2" required>
                
                <label>Expiry:</label>
                <input type="date" name="new_expiry" id="updateExpiry" class="input mb-4" required>
                
                <button type="submit" class="btn btn-primary w-full">Update</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    function initSelect2() {
        $('.select2').select2({
            placeholder: "Search...",
            dropdownParent: $('#addMedicineModal'),
            width: '100%'
        });
    }

    window.openAddModal = function() {
        document.getElementById('addMedicineModal').classList.remove('hidden');
        initSelect2();
    };

    window.closeAddModal = function() {
        document.getElementById('addMedicineModal').classList.add('hidden');
    };

    window.openUpdateModal = function(id, name, stock, price, expiry) {
        document.getElementById('updateMedId').value = id;
        document.getElementById('updateMedName').textContent = name;
        document.getElementById('updateStock').value = stock;
        document.getElementById('updatePrice').value = price;
        document.getElementById('updateExpiry').value = expiry;
        document.getElementById('updateModal').classList.remove('hidden');
    };

    window.closeUpdateModal = function() {
        document.getElementById('updateModal').classList.add('hidden');
    };
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>