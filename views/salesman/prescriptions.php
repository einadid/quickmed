<?php
/**
 * Salesman - Prescription Queue
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('salesman');

$pageTitle = 'Prescription Orders';

// Get pending prescriptions
$query = "SELECT * FROM prescriptions WHERE status = 'pending' ORDER BY created_at DESC";
$list = $conn->query($query);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-deep-green mb-8">📋 Prescription Orders Queue</h1>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($row = $list->fetch_assoc()): ?>
            <div class="card bg-white border-4 border-deep-green p-0 overflow-hidden">
                <div class="h-48 overflow-hidden cursor-pointer border-b-4 border-deep-green group" onclick="window.open('<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>', '_blank')">
                    <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                </div>

                <div class="p-6">
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($row['customer_name']) ?></h3>
                    <p class="text-sm text-gray-600">📞 <?= htmlspecialchars($row['customer_phone']) ?></p>
                    <p class="text-sm text-gray-500 mt-2 bg-gray-100 p-2 rounded border">
                        📝 <?= nl2br(htmlspecialchars($row['notes'])) ?>
                    </p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                        <a href="pos.php?prescription_id=<?= $row['id'] ?>"
                           class="btn btn-primary w-full flex items-center justify-center gap-2 shadow-lg transform hover:scale-105 transition-all">
                            <span>🛒</span> Process Order
                        </a>

                        <button onclick="rejectPrescription(<?= $row['id'] ?>)" class="btn btn-outline border-red-500 text-red-600 px-3">❌</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<script>
function rejectPrescription(id) {
    if(confirm('Reject this prescription?')) {
        // Add AJAX call to reject
        window.location.href = `reject_prescription.php?id=${id}`;
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>