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

<?php qm_hero('Upload Prescription', 'Send your prescription — our doctors will review and confirm your order.', 'Prescription Service', '📋'); ?>

<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="grid lg:grid-cols-3 gap-8">

        <div class="lg:col-span-1">
            <div class="card sticky top-24">
                <div class="card-header">📤 New Upload</div>

                <form method="POST" enctype="multipart/form-data" class="space-y-4">

                    <div class="dropzone" onclick="document.getElementById('file').click()">

                        <input type="file" name="prescription_image" id="file" class="hidden" accept="image/*" onchange="preview(this)" required>

                        <div id="placeholder">
                            <span class="text-4xl block mb-2">📸</span>
                            <p class="text-gray-600 font-bold">Click to Upload</p>
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, JPG supported (max 5MB)</p>
                        </div>

                        <div id="previewContainer" class="hidden mt-2">
                            <p class="text-sm text-green-700 font-bold mb-2">Selected Image:</p>
                            <div class="relative inline-block">
                                <img id="preview" class="max-h-40 mx-auto rounded-lg border-4 border-white shadow-lg object-cover">
                                <button type="button" onclick="event.stopPropagation(); removeImage()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow-md" title="Remove Image">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="label">Patient Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['full_name']) ?>" class="input" placeholder="Patient Name" required>
                    </div>
                    <div>
                        <label class="label">Phone *</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="input" placeholder="Phone Number" required>
                    </div>
                    <div>
                        <label class="label">Delivery Address *</label>
                        <textarea name="address" rows="2" class="input" placeholder="Delivery Address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="label">Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="input" placeholder="Medicine Details / Notes"></textarea>
                    </div>

                    <button type="submit" name="submit_prescription" class="btn btn-primary btn-block">
                        📤 Submit Request
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-[#065f46] mb-6 font-display">📋 MY PRESCRIPTIONS</h2>

            <?php if ($history->num_rows === 0): ?>
                <?php qm_empty('No prescriptions yet', 'Upload your first prescription using the form.'); ?>
            <?php else: ?>
                <div class="space-y-5">
                    <?php while ($row = $history->fetch_assoc()): ?>
                        <div class="card flex flex-col md:flex-row gap-5 card-hover">
                            <div class="w-full md:w-32 h-32 flex-shrink-0 cursor-pointer overflow-hidden rounded-xl border border-gray-200 group" onclick="window.open('<?= SITE_URL ?>/uploads/prescriptions/<?= htmlspecialchars($row['image_path']) ?>')" title="Click to view full image">
                                <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= htmlspecialchars($row['image_path']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy">
                            </div>

                            <div class="flex-1">
                                <div class="flex flex-wrap justify-between items-start gap-3">
                                    <div>
                                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">
                                            <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                                        </p>
                                        <h3 class="text-lg font-bold text-gray-800">Rx Request #<?= (int)$row['id'] ?></h3>
                                        <?php if ($row['notes']): ?>
                                            <p class="text-sm text-gray-600 mt-1 bg-gray-50 p-2 rounded-lg italic border border-gray-200">
                                                "<?= htmlspecialchars($row['notes']) ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-right">
                                        <?php if ($row['order_id']): ?>
                                            <span class="badge badge-green">✅ Order Placed</span>
                                        <?php elseif ($row['status'] == 'rejected'): ?>
                                            <?= qm_badge('rejected') ?>
                                        <?php elseif ($row['status'] == 'approved' || $row['status'] == 'reviewed'): ?>
                                            <?= qm_badge('approved') ?>
                                            <p class="text-xs text-blue-600 mt-1 font-bold">Processing Quote...</p>
                                        <?php else: ?>
                                            <?= qm_badge('pending') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap justify-between items-center gap-3">
                                    <?php if ($row['order_id']): ?>
                                        <div class="flex items-center gap-2 text-[#065f46] font-bold bg-green-50 px-3 py-1.5 rounded-lg border border-green-200 text-sm">
                                            <span>🛍️ #<?= htmlspecialchars($row['order_number']) ?></span>
                                            <span class="text-gray-300">|</span>
                                            <span><?= qm_money($row['total_amount']) ?></span>
                                        </div>
                                        <a href="my-orders.php" class="btn btn-outline btn-sm">Track Order →</a>
                                    <?php elseif ($row['status'] == 'rejected'): ?>
                                        <p class="text-sm text-red-600 font-bold">⚠️ Please re-upload a clearer image.</p>
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