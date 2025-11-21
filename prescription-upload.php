<?php
/**
 * Prescription Upload - Dedicated Page
 */

require_once 'config.php';

requireLogin();

$pageTitle = 'Upload Prescription - QuickMed';
$user = getCurrentUser();

// Get user's prescriptions
$prescriptionsQuery = "SELECT p.*, u.full_name as reviewer_name
                       FROM prescriptions p
                       LEFT JOIN users u ON p.reviewed_by = u.id
                       WHERE p.user_id = ?
                       ORDER BY p.created_at DESC";
$stmt = $conn->prepare($prescriptionsQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$prescriptions = $stmt->get_result();

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green mb-4 font-mono uppercase">
                📋 Upload Prescription
            </h1>
            <div class="bg-lime-accent inline-block px-6 py-3 border-4 border-deep-green">
                <p class="text-deep-green font-bold text-xl">Upload your doctor's prescription</p>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="card bg-white border-4 border-deep-green mb-12" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📤 New Prescription
            </h2>

            <form id="prescriptionUploadForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <!-- Drag & Drop Zone -->
                <div class="mb-6">
                    <label class="block font-bold mb-3 text-deep-green text-lg">📸 Prescription Image *</label>
                    <div class="border-4 border-dashed border-deep-green p-12 text-center hover:border-lime-accent transition-all cursor-pointer bg-off-white" id="dropZone">
                        <input 
                            type="file" 
                            name="prescription_image" 
                            id="prescriptionImage"
                            accept="image/*,application/pdf"
                            required
                            class="hidden"
                        >
                        <div id="dropText">
                            <div class="text-8xl mb-4">📤</div>
                            <p class="text-2xl font-bold mb-2">Click to Upload or Drag & Drop</p>
                            <p class="text-gray-600">Supported: JPG, PNG, PDF (Max 5MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Image Preview -->
                <div id="imagePreview" class="mb-6 hidden">
                    <p class="font-bold mb-2 text-deep-green">Preview:</p>
                    <img src="" alt="Preview" class="max-w-full border-4 border-deep-green">
                </div>

                <!-- Additional Notes -->
                <div class="mb-6">
                    <label class="block font-bold mb-3 text-deep-green text-lg">📝 Additional Notes (Optional)</label>
                    <textarea 
                        name="notes" 
                        rows="4" 
                        class="input border-4 border-deep-green"
                        placeholder="Any special instructions, medicine requirements, or questions for our pharmacist..."
                    ></textarea>
                </div>

                <!-- Guidelines -->
                <div class="bg-lime-accent border-4 border-deep-green p-6 mb-6">
                    <h3 class="font-bold text-deep-green text-lg mb-3">✅ Prescription Guidelines:</h3>
                    <ul class="space-y-2 text-sm">
                        <li>✓ Ensure prescription is clear and readable</li>
                        <li>✓ Include doctor's name and signature</li>
                        <li>✓ Check that all medicine names are visible</li>
                        <li>✓ Upload recent prescriptions (within 6 months)</li>
                        <li>✓ Our pharmacist will review within 24 hours</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-full text-xl py-4 neon-border">
                    📤 Upload Prescription
                </button>
            </form>
        </div>

        <!-- Previously Uploaded -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                📚 Your Prescriptions
            </h2>

            <?php if ($prescriptions->num_rows === 0): ?>
                <div class="text-center py-12 text-gray-500">
                    <div class="text-8xl mb-4">📋</div>
                    <p class="text-xl">No prescriptions uploaded yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php while ($presc = $prescriptions->fetch_assoc()): ?>
                        <div class="border-4 border-gray-200 p-6 hover:border-lime-accent transition-all">
                            <div class="flex items-start gap-6">
                                <!-- Image Thumbnail -->
                                <img 
                                    src="<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>" 
                                    alt="Prescription"
                                    class="w-32 h-32 object-cover border-4 border-deep-green cursor-pointer"
                                    onclick="viewPrescription('<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>')"
                                >

                                <!-- Details -->
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="text-sm text-gray-500">Uploaded on</p>
                                            <p class="font-bold text-lg"><?= date('M d, Y h:i A', strtotime($presc['created_at'])) ?></p>
                                        </div>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'badge-warning',
                                            'reviewed' => 'badge-info',
                                            'approved' => 'badge-success',
                                            'rejected' => 'badge-danger'
                                        ];
                                        ?>
                                        <span class="badge <?= $statusColors[$presc['status']] ?> text-lg">
                                            <?= ucfirst($presc['status']) ?>
                                        </span>
                                    </div>

                                    <?php if ($presc['notes']): ?>
                                        <div class="mb-3">
                                            <p class="text-sm font-bold text-gray-600">Your Notes:</p>
                                            <p class="text-gray-700"><?= htmlspecialchars($presc['notes']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($presc['reviewed_by']): ?>
                                        <div class="bg-gray-50 border-2 border-gray-300 p-3">
                                            <p class="text-sm font-bold text-gray-600 mb-1">Pharmacist Review:</p>
                                            <p class="text-sm"><strong>Reviewed by:</strong> <?= htmlspecialchars($presc['reviewer_name']) ?></p>
                                            <p class="text-sm"><strong>Date:</strong> <?= date('M d, Y', strtotime($presc['reviewed_at'])) ?></p>
                                            <?php if ($presc['review_notes']): ?>
                                                <p class="text-sm mt-2"><strong>Notes:</strong> <?= htmlspecialchars($presc['review_notes']) ?></p>
                                            <?php endif; ?>
                                        </div>
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

<!-- View Prescription Modal -->
<div id="viewModal" class="modal-overlay hidden">
    <div class="modal max-w-4xl">
        <div class="modal-header">
            <h3 class="text-2xl font-bold">View Prescription</h3>
            <button onclick="closeViewModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <img id="viewImage" src="" alt="Prescription" class="w-full border-4 border-deep-green">
        </div>
    </div>
</div>

<script>
// Drag & Drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('prescriptionImage');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-lime-accent', 'bg-light-green');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-lime-accent', 'bg-light-green');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-lime-accent', 'bg-light-green');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        previewImage(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', function(e) {
    if (e.target.files.length) {
        previewImage(e.target.files[0]);
    }
});

function previewImage(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('imagePreview');
        preview.querySelector('img').src = e.target.result;
        preview.classList.remove('hidden');
        document.getElementById('dropText').innerHTML = '<div class="text-green-600 text-2xl font-bold">✅ File Selected: ' + file.name + '</div>';
    }
    reader.readAsDataURL(file);
}

// Form submission
document.getElementById('prescriptionUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="animate-spin">⏳</span> Uploading...';
    
    try {
        const response = await fetch('<?= SITE_URL ?>/ajax/upload_prescription.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                confirmButtonColor: '#065f46'
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Failed to upload prescription',
            confirmButtonColor: '#065f46'
        });
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '📤 Upload Prescription';
    }
});

function viewPrescription(imageUrl) {
    document.getElementById('viewImage').src = imageUrl;
    document.getElementById('viewModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>