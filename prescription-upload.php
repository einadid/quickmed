<?php
/**
 * Prescription Upload & Order Request
 */
require_once 'config.php';
requireLogin();

$pageTitle = 'Upload Prescription & Order';
$user = getCurrentUser();

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prescription'])) {
    $name = clean($_POST['name']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    $notes = clean($_POST['notes']);
    
    // Handle Image
    if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['prescription_image'], PRESCRIPTION_DIR);
        if ($uploadRes['success']) {
            $image = $uploadRes['filename'];
            
            $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, customer_name, customer_phone, customer_address, image_path, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("isssss", $user['id'], $name, $phone, $address, $image, $notes);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Prescription uploaded! Wait for salesman confirmation.';
                redirect('my-orders.php');
            }
        } else {
            $_SESSION['error'] = $uploadRes['message'];
        }
    } else {
        $_SESSION['error'] = 'Please select an image';
    }
}

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-3xl mx-auto card bg-white border-4 border-deep-green p-8">
        <h1 class="text-3xl font-bold text-deep-green mb-6 text-center uppercase">📤 Upload Prescription</h1>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Image Upload -->
            <div class="border-4 border-dashed border-gray-300 p-8 text-center rounded-lg cursor-pointer hover:border-lime-accent transition-all bg-gray-50" onclick="document.getElementById('prescImg').click()">
                <input type="file" name="prescription_image" id="prescImg" class="hidden" accept="image/*" required onchange="previewImage(this)">
                <div id="uploadPlaceholder">
                    <span class="text-4xl">📸</span>
                    <p class="text-gray-500 mt-2">Click or Drag Prescription Image Here</p>
                </div>
                <img id="imgPreview" class="hidden max-h-64 mx-auto rounded shadow-lg border-4 border-white">
            </div>

            <!-- Customer Details -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" class="input border-2 border-deep-green w-full" value="<?= $user['full_name'] ?>" required>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="phone" class="input border-2 border-deep-green w-full" value="<?= $user['phone'] ?>" required>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Delivery Address</label>
                <textarea name="address" rows="2" class="input border-2 border-deep-green w-full" required><?= $user['address'] ?></textarea>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Medicine Details / Notes</label>
                <textarea name="notes" rows="3" class="input border-2 border-deep-green w-full" placeholder="Mention medicine names or specific instructions..."></textarea>
            </div>

            <button type="submit" name="submit_prescription" class="btn btn-primary w-full py-4 text-xl font-bold shadow-lg">
                🚀 Submit Order Request
            </button>
        </form>
    </div>
</section>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreview').classList.remove('hidden');
            document.getElementById('uploadPlaceholder').classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>