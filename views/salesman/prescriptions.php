<?php
/**
 * Salesman - Prescription Queue (Reviewed Orders Only)
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('salesman');

$pageTitle = 'Approved Prescription Orders';

// Update: Only show prescriptions approved by doctor ('reviewed')
// Joined with users table to get customer details
$query = "SELECT p.*, u.full_name, u.phone, u.email 
          FROM prescriptions p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'reviewed' 
          ORDER BY p.created_at DESC";

$list = $conn->query($query);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-deep-green mb-8">📋 Reviewed Prescriptions Queue</h1>

    <?php if ($list->num_rows > 0): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = $list->fetch_assoc()): ?>
                <div class="card bg-white border-4 border-deep-green p-0 overflow-hidden">
                    <div class="h-48 overflow-hidden cursor-pointer border-b-4 border-deep-green group" onclick="window.open('<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>', '_blank')">
                        <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    </div>

                    <div class="p-6">
                        <h3 class="font-bold text-lg"><?= htmlspecialchars($row['full_name']) ?></h3>
                        <p class="text-sm text-gray-600">📞 <?= htmlspecialchars($row['phone']) ?></p>
                        
                        <?php if(isset($row['email'])): ?>
                            <p class="text-xs text-gray-500">✉️ <?= htmlspecialchars($row['email']) ?></p>
                        <?php endif; ?>

                        <p class="text-sm text-gray-500 mt-2 bg-gray-100 p-2 rounded border">
                            <span class="font-semibold">Note:</span> <?= nl2br(htmlspecialchars($row['notes'] ?? 'No notes provided.')) ?>
                        </p>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                            <a href="pos.php?prescription_id=<?= $row['id'] ?>"
                               class="btn btn-primary w-full flex items-center justify-center gap-2 shadow-lg transform hover:scale-105 transition-all">
                                <span>🛒</span> Process Order
                            </a>

                            <button onclick="rejectPrescription(<?= $row['id'] ?>)" class="btn btn-outline border-red-500 text-red-600 px-3" title="Reject Order">
                                ❌
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <p class="text-xl text-gray-500">No reviewed prescriptions available for processing.</p>
        </div>
    <?php endif; ?>
</section>

<script>
function rejectPrescription(id) {
    if(confirm('Are you sure you want to reject this prescription order?')) {
        // Redirect to reject logic
        window.location.href = `reject_prescription.php?id=${id}`;
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>