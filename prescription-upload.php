<?php
/**
 * Prescription Upload & Status Tracking
 */
require_once 'config.php';
requireLogin();

$pageTitle = 'Upload Prescription';
$user = getCurrentUser();

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prescription'])) {
    $name = clean($_POST['name']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    $notes = clean($_POST['notes']);
    
    if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
        // Check if file is actually uploaded before processing
        if (!empty($_FILES['prescription_image']['name'])) {
            $uploadRes = uploadFile($_FILES['prescription_image'], PRESCRIPTION_DIR);
            if ($uploadRes['success']) {
                $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, customer_name, customer_phone, customer_address, image_path, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("isssss", $user['id'], $name, $phone, $address, $uploadRes['filename'], $notes);
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Prescription uploaded! Waiting for Doctor Review.';
                    redirect('prescription-upload.php');
                }
            } else {
                $_SESSION['error'] = $uploadRes['message'];
            }
        } else {
            $_SESSION['error'] = 'Please select an image.';
        }
    }
}

// Fetch My Prescriptions with Order Info
$query = "SELECT p.*, o.id as order_id, o.order_number, o.total_amount 
          FROM prescriptions p 
          LEFT JOIN orders o ON p.order_id = o.id 
          WHERE p.user_id = {$user['id']} 
          ORDER BY p.created_at DESC";
$history = $conn->query($query);

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="grid lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="card bg-white border-4 border-deep-green p-6 sticky top-24">
                <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-2">📤 New Upload</h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    
                    <div class="border-4 border-dashed border-gray-300 p-8 text-center rounded-lg cursor-pointer hover:border-lime-accent transition-all bg-gray-50 relative group" onclick="document.getElementById('file').click()">
                        
                        <input type="file" name="prescription_image" id="file" class="hidden" accept="image/*" onchange="preview(this)" required>
                        
                        <div id="placeholder" class="group-hover:scale-105 transition-transform duration-300">
                            <span class="text-4xl block mb-2">📸</span>
                            <p class="text-gray-500 font-bold">Click to Upload</p>
                            <p class="text-xs text-gray-400 mt-1">JEPG, PNG, JPG supported</p>
                        </div>

                        <div id="previewContainer" class="hidden mt-2">
                            <p class="text-sm text-green-600 font-bold mb-2">Selected Image:</p>
                            <div class="relative inline-block">
                                <img id="preview" class="max-h-40 mx-auto rounded-lg border-4 border-white shadow-lg object-cover">
                                <button type="button" onclick="event.stopPropagation(); removeImage()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow-md" title="Remove Image">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="text" name="name" value="<?= $user['full_name'] ?>" class="input border-2 border-deep-green w-full" placeholder="Patient Name" required>
                    <input type="tel" name="phone" value="<?= $user['phone'] ?>" class="input border-2 border-deep-green w-full" placeholder="Phone Number" required>
                    <textarea name="address" rows="2" class="input border-2 border-deep-green w-full" placeholder="Delivery Address" required><?= $user['address'] ?></textarea>
                    <textarea name="notes" rows="2" class="input border-2 border-deep-green w-full" placeholder="Medicine Details / Notes (Optional)"></textarea>

                    <button type="submit" name="submit_prescription" class="btn btn-primary w-full py-3 text-lg font-bold shadow-lg transform hover:scale-105 transition-transform">
                        Submit Request
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <h2 class="text-3xl font-bold text-deep-green mb-6 uppercase border-b-4 border-lime-accent pb-2 inline-block">
                📋 My Prescriptions
            </h2>

            <?php if ($history->num_rows === 0): ?>
                <div class="text-center py-20 bg-white rounded-xl border-2 border-dashed border-gray-300">
                    <div class="text-6xl mb-4 opacity-50">📭</div>
                    <p class="text-xl text-gray-500">No prescriptions uploaded yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php while ($row = $history->fetch_assoc()): ?>
                        <div class="card bg-white border-l-8 p-6 flex flex-col md:flex-row gap-6 transition hover:shadow-lg
                            <?= $row['status'] == 'pending' ? 'border-yellow-400' : 
                               ($row['status'] == 'rejected' ? 'border-red-500' : 
                               ($row['order_id'] ? 'border-deep-green' : 'border-blue-500')) ?>">
                            
                            <div class="w-full md:w-32 h-32 flex-shrink-0 cursor-pointer overflow-hidden rounded border-2 border-gray-200 group" onclick="window.open('<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>')">
                                <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            </div>

                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs text-gray-500 font-bold uppercase mb-1">
                                            <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                                        </p>
                                        <h3 class="text-lg font-bold text-gray-800">Rx Request #<?= $row['id'] ?></h3>
                                        <?php if ($row['notes']): ?>
                                            <p class="text-sm text-gray-600 mt-1 bg-gray-50 p-2 rounded italic border border-gray-100">
                                                "<?= htmlspecialchars($row['notes']) ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="text-right">
                                        <?php if ($row['order_id']): ?>
                                            <span class="badge bg-deep-green text-white px-3 py-1 rounded-full shadow-md animate-pulse">
                                                ✅ Order Placed
                                            </span>
                                        <?php elseif ($row['status'] == 'rejected'): ?>
                                            <span class="badge bg-red-100 text-red-600 border border-red-200 px-3 py-1 rounded-full">
                                                ❌ Rejected
                                            </span>
                                        <?php elseif ($row['status'] == 'reviewed'): ?>
                                            <span class="badge bg-blue-100 text-blue-600 border border-blue-200 px-3 py-1 rounded-full">
                                                👨‍⚕️ Doctor Approved
                                            </span>
                                            <p class="text-xs text-blue-500 mt-1 font-bold">Processing Quote...</p>
                                        <?php else: ?>
                                            <span class="badge bg-yellow-100 text-yellow-700 border border-yellow-200 px-3 py-1 rounded-full">
                                                ⏳ In Review
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                    <?php if ($row['order_id']): ?>
                                        <div class="flex items-center gap-2 text-deep-green font-bold bg-green-50 px-3 py-1 rounded border border-green-100">
                                            <span>🛍️ Order #<?= $row['order_number'] ?></span>
                                            <span class="text-gray-300">|</span>
                                            <span>৳<?= number_format($row['total_amount']) ?></span>
                                        </div>
                                        <a href="my-orders.php" class="btn btn-outline btn-sm border-deep-green text-deep-green hover:bg-deep-green hover:text-white transition-colors">
                                            Track Order →
                                        </a>
                                    <?php elseif ($row['status'] == 'rejected'): ?>
                                        <p class="text-sm text-red-500 font-bold flex items-center gap-1">
                                            ⚠️ Please re-upload a clearer image.
                                        </p>
                                    <?php else: ?>
                                        <p class="text-xs text-gray-400 italic">You will be notified once processed.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Updated Preview Function
function preview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('previewContainer').classList.remove('hidden'); // Show preview
            document.getElementById('placeholder').classList.add('hidden'); // Hide placeholder
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Updated Remove Function
function removeImage() {
    const fileInput = document.getElementById('file');
    fileInput.value = ''; // Clear input
    
    document.getElementById('preview').src = '';
    document.getElementById('previewContainer').classList.add('hidden'); // Hide preview
    document.getElementById('placeholder').classList.remove('hidden'); // Show placeholder
}
</script>

<?php include 'includes/footer.php'; ?>