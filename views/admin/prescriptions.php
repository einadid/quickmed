<?php
/**
 * Admin - Manage Prescriptions
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Prescriptions - Admin';

// Handle Review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_prescription'])) {
    $prescId = intval($_POST['prescription_id']);
    $status = clean($_POST['status']); // 'approved' or 'rejected'
    $notes = clean($_POST['review_notes']);
    $reviewerId = $_SESSION['user_id'];
    
    $query = "UPDATE prescriptions SET status=?, reviewed_by=?, review_notes=?, reviewed_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisi", $status, $reviewerId, $notes, $prescId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Prescription updated successfully';
    } else {
        $_SESSION['error'] = 'Update failed: ' . $stmt->error;
    }
    
    header("Location: prescriptions.php");
    exit();
}

// Get all prescriptions
$statusFilter = clean($_GET['status'] ?? 'all');

$whereClause = "1=1";
if ($statusFilter !== 'all') {
    $whereClause = "p.status = '$statusFilter'";
}

$prescQuery = "SELECT p.*, u.full_name as customer_name, u.email, u.phone,
               r.full_name as reviewer_name
               FROM prescriptions p
               JOIN users u ON p.user_id = u.id
               LEFT JOIN users r ON p.reviewed_by = r.id
               WHERE $whereClause
               ORDER BY p.created_at DESC";
$prescriptions = $conn->query($prescQuery);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">📋 Manage Prescriptions</h1>
            <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Status Filter -->
        <div class="flex gap-4 mb-8" data-aos="fade-up">
            <a href="?status=all" class="btn <?= $statusFilter === 'all' ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="?status=pending" class="btn <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
            <a href="?status=reviewed" class="btn <?= $statusFilter === 'reviewed' ? 'btn-primary' : 'btn-outline' ?>">Reviewed</a>
            <a href="?status=approved" class="btn <?= $statusFilter === 'approved' ? 'btn-primary' : 'btn-outline' ?>">Approved</a>
            <a href="?status=rejected" class="btn <?= $statusFilter === 'rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejected</a>
        </div>

        <!-- Prescriptions Grid -->
        <div class="grid md:grid-cols-2 gap-6">
            <?php while ($presc = $prescriptions->fetch_assoc()): ?>
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Prescription #<?= $presc['id'] ?></p>
                            <h3 class="text-xl font-bold text-deep-green"><?= htmlspecialchars($presc['customer_name']) ?></h3>
                            <p class="text-sm text-gray-600">📧 <?= htmlspecialchars($presc['email']) ?></p>
                            <p class="text-sm text-gray-600">📱 <?= htmlspecialchars($presc['phone']) ?></p>
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

                    <!-- Prescription Image -->
                    <div class="mb-4 border-4 border-gray-200 cursor-pointer" onclick="viewImage('<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>')">
                        <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>" alt="Prescription" class="w-full h-64 object-cover">
                    </div>

                    <?php if ($presc['notes']): ?>
                        <div class="mb-4 bg-gray-50 p-3 border-2 border-gray-300">
                            <p class="text-sm font-bold text-gray-600">Customer Notes:</p>
                            <p class="text-gray-700"><?= htmlspecialchars($presc['notes']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($presc['reviewed_by']): ?>
                        <div class="mb-4 bg-lime-accent p-3 border-2 border-deep-green">
                            <p class="text-sm font-bold">Reviewed by: <?= htmlspecialchars($presc['reviewer_name']) ?></p>
                            <p class="text-sm">Date: <?= date('M d, Y h:i A', strtotime($presc['reviewed_at'])) ?></p>
                            <?php if ($presc['review_notes']): ?>
                                <p class="text-sm mt-2"><strong>Notes:</strong> <?= htmlspecialchars($presc['review_notes']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex gap-2">
                        <button onclick="reviewPrescription(<?= $presc['id'] ?>, 'approved')" class="btn btn-primary flex-1">
                            ✅ Approve
                        </button>
                        <button onclick="reviewPrescription(<?= $presc['id'] ?>, 'rejected')" class="btn btn-outline flex-1 border-red-500 text-red-600">
                            ❌ Reject
                        </button>
                    </div>

                    <p class="text-xs text-gray-500 mt-3 text-center">
                        Uploaded: <?= timeAgo($presc['created_at']) ?>
                    </p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Review Modal -->
<div id="reviewModal" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h3 class="text-2xl font-bold">Review Prescription</h3>
            <button onclick="closeReviewModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="prescription_id" id="prescId">
                <input type="hidden" name="status" id="prescStatus">
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Review Notes</label>
                    <textarea name="review_notes" rows="4" class="input border-4 border-deep-green" placeholder="Add your review comments..."></textarea>
                </div>
                
                <button type="submit" name="review_prescription" class="btn btn-primary w-full text-xl py-4">
                    💾 Submit Review
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div id="imageModal" class="modal-overlay hidden">
    <div class="modal max-w-5xl">
        <div class="modal-header">
            <h3 class="text-2xl font-bold">View Prescription</h3>
            <button onclick="closeImageModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="" alt="Prescription" class="w-full border-4 border-deep-green">
        </div>
    </div>
</div>

<script>
function reviewPrescription(id, status) {
    document.getElementById('prescId').value = id;
    document.getElementById('prescStatus').value = status;
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

function viewImage(url) {
    document.getElementById('modalImage').src = url;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>