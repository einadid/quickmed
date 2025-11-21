<?php
/**
 * Admin - Manage Medicines (FIXED)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Medicines - Admin';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = clean($_POST['name']);
    $genericName = clean($_POST['generic_name']);
    $brand = clean($_POST['brand']);
    $category = clean($_POST['category']);
    $power = clean($_POST['power']);
    $form = clean($_POST['form']);
    $description = clean($_POST['description']);
    $manufacturer = clean($_POST['manufacturer']);
    $requiresPrescription = isset($_POST['requires_prescription']) ? 1 : 0;
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['image'], MEDICINE_DIR);
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        }
    }
    
    if ($id > 0) {
        // Update existing medicine
        $query = "UPDATE medicines SET name=?, generic_name=?, brand=?, category=?, power=?, 
                  form=?, description=?, manufacturer=?, requires_prescription=?";
        $types = "ssssssssi";
        $params = [$name, $genericName, $brand, $category, $power, $form, $description, $manufacturer, $requiresPrescription];
        
        if ($image) {
            $query .= ", image=?";
            $types .= "s";
            $params[] = $image;
        }
        
        $query .= " WHERE id=?";
        $types .= "i";
        $params[] = $id;
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Medicine updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update medicine: ' . $stmt->error;
        }
    } else {
        // Add new medicine
        $query = "INSERT INTO medicines (name, generic_name, brand, category, power, form, description, image, manufacturer, requires_prescription)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssssi", $name, $genericName, $brand, $category, $power, $form, $description, $image, $manufacturer, $requiresPrescription);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Medicine added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add medicine: ' . $stmt->error;
        }
    }
    
    redirect('views/admin/medicines.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $deleteQuery = "DELETE FROM medicines WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Medicine deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete medicine';
    }
    
    redirect('views/admin/medicines.php');
}

// Search Filter
$search = clean($_GET['search'] ?? '');
$whereClause = "";
$params = [];
$types = "";

if ($search) {
    $whereClause = "WHERE name LIKE ? OR generic_name LIKE ? OR brand LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = "sss";
}

// Get all medicines
$query = "SELECT m.*, 
          (SELECT COUNT(*) FROM shop_medicines WHERE medicine_id = m.id) as shop_count,
          (SELECT SUM(stock_quantity) FROM shop_medicines WHERE medicine_id = m.id) as total_stock
          FROM medicines m
          $whereClause
          ORDER BY m.name ASC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$medicines = $stmt->get_result();

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">💊 Manage Medicines</h1>
            <div class="flex gap-4">
                <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary">+ Add Medicine</button>
            </div>
        </div>

        <!-- Search -->
        <div class="card bg-white border-4 border-deep-green mb-8" data-aos="fade-up">
            <form method="GET" class="flex gap-4">
                <input 
                    type="text" 
                    name="search" 
                    class="input border-4 border-deep-green flex-1" 
                    placeholder="Search by name, generic name, or brand..."
                    value="<?= htmlspecialchars($search) ?>"
                >
                <button type="submit" class="btn btn-primary">🔍 Search</button>
                <?php if ($search): ?>
                    <a href="<?= SITE_URL ?>/views/admin/medicines.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Medicines Table -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Generic</th>
                            <th>Category</th>
                            <th>Power</th>
                            <th>Total Stock</th>
                            <th>Shops</th>
                            <th>Rx</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($medicines->num_rows === 0): ?>
                            <tr>
                                <td colspan="9" class="text-center py-8 text-xl text-gray-500">
                                    No medicines found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($med = $medicines->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img 
                                            src="<?= SITE_URL ?>/uploads/medicines/<?= $med['image'] ?? 'placeholder.png' ?>" 
                                            alt="<?= htmlspecialchars($med['name']) ?>"
                                            class="w-16 h-16 object-contain border-2 border-deep-green bg-white"
                                            onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                                        >
                                    </td>
                                    <td class="font-bold text-deep-green"><?= htmlspecialchars($med['name']) ?></td>
                                    <td><?= htmlspecialchars($med['generic_name']) ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($med['category']) ?></span></td>
                                    <td><?= htmlspecialchars($med['power']) ?></td>
                                    <td class="font-bold"><?= $med['total_stock'] ?? 0 ?></td>
                                    <td><?= $med['shop_count'] ?></td>
                                    <td>
                                        <?php if ($med['requires_prescription']): ?>
                                            <span class="badge badge-warning">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button onclick='editMedicine(<?= json_encode($med) ?>)' class="btn btn-outline btn-sm">
                                                ✏️ Edit
                                            </button>
                                            <button onclick="deleteMedicine(<?= $med['id'] ?>)" class="btn btn-outline btn-sm border-red-500 text-red-600">
                                                🗑️ Delete
                                            </button>
                                        </div>
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

<!-- Add/Edit Modal -->
<div id="medicineModal" class="modal-overlay hidden">
    <div class="modal max-w-4xl">
        <div class="modal-header bg-deep-green text-white">
            <h3 class="text-2xl font-bold" id="modalTitle">Add Medicine</h3>
            <button onclick="closeModal()" class="text-3xl text-white">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" id="medicineForm">
                <input type="hidden" name="id" id="medicineId">
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Medicine Name *</label>
                        <input type="text" name="name" id="medicineName" class="input border-4 border-deep-green" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Generic Name *</label>
                        <input type="text" name="generic_name" id="genericName" class="input border-4 border-deep-green" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Brand</label>
                        <input type="text" name="brand" id="brand" class="input border-4 border-deep-green">
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Category *</label>
                        <select name="category" id="category" class="input border-4 border-deep-green" required>
                            <option value="">Select Category</option>
                            <option value="Pain Relief">Pain Relief</option>
                            <option value="Gastric">Gastric</option>
                            <option value="Heart">Heart</option>
                            <option value="Diabetes">Diabetes</option>
                            <option value="Allergy">Allergy</option>
                            <option value="Baby Care">Baby Care</option>
                            <option value="Orthopedic">Orthopedic</option>
                            <option value="Eye & Ear">Eye & Ear</option>
                            <option value="Dental">Dental</option>
                            <option value="Skin">Skin</option>
                            <option value="Vitamins">Vitamins</option>
                            <option value="Mental Health">Mental Health</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Power/Strength *</label>
                        <input type="text" name="power" id="power" class="input border-4 border-deep-green" placeholder="e.g., 500mg" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Form *</label>
                        <select name="form" id="form" class="input border-4 border-deep-green" required>
                            <option value="">Select Form</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Syrup">Syrup</option>
                            <option value="Injection">Injection</option>
                            <option value="Gel">Gel</option>
                            <option value="Cream">Cream</option>
                            <option value="Drops">Drops</option>
                            <option value="Mouthwash">Mouthwash</option>
                            <option value="Toothpaste">Toothpaste</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Manufacturer</label>
                        <input type="text" name="manufacturer" id="manufacturer" class="input border-4 border-deep-green">
                    </div>

                    <div>
                        <label class="block font-bold mb-2 text-deep-green">Image</label>
                        <input type="file" name="image" id="image" class="input border-4 border-deep-green" accept="image/*">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold mb-2 text-deep-green">Description</label>
                        <textarea name="description" id="description" rows="3" class="input border-4 border-deep-green"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="requires_prescription" id="requiresPrescription" class="w-6 h-6">
                            <span class="font-bold text-deep-green">Requires Prescription</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-6 text-xl py-4 neon-border">
                    💾 Save Medicine
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('medicineModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Medicine';
    document.getElementById('medicineForm').reset();
    document.getElementById('medicineId').value = '';
}

function editMedicine(medicine) {
    document.getElementById('medicineModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Medicine';
    
    document.getElementById('medicineId').value = medicine.id;
    document.getElementById('medicineName').value = medicine.name;
    document.getElementById('genericName').value = medicine.generic_name;
    document.getElementById('brand').value = medicine.brand || '';
    document.getElementById('category').value = medicine.category || '';
    document.getElementById('power').value = medicine.power;
    document.getElementById('form').value = medicine.form || '';
    document.getElementById('manufacturer').value = medicine.manufacturer || '';
    document.getElementById('description').value = medicine.description || '';
    document.getElementById('requiresPrescription').checked = medicine.requires_prescription == 1;
}

function closeModal() {
    document.getElementById('medicineModal').classList.add('hidden');
}

async function deleteMedicine(id) {
    const result = await Swal.fire({
        title: 'Delete Medicine?',
        text: 'This will remove the medicine from all shops. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        window.location.href = '?delete=' + id;
    }
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('medicineModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>