<?php
/**
 * Salesman - View Customer Prescriptions
 */
require_once __DIR__ . '/../../config.php';
requireLogin();

// Allow Salesman & Shop Manager
if (!hasRole('salesman') && !hasRole('shop_manager')) {
    redirect('../../index.php');
}

$pageTitle = 'Customer Prescriptions';

// Get pending prescriptions
$query = "SELECT p.*, u.full_name, u.phone 
          FROM prescriptions p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'pending' 
          ORDER BY p.created_at DESC";
$prescriptions = $conn->query($query);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-deep-green mb-8">📋 Pending Prescriptions</h1>

    <div class="grid md:grid-cols-2 gap-6">
        <?php while ($presc = $prescriptions->fetch_assoc()): ?>
            <div class="card bg-white border-4 border-deep-green">
                <div class="flex gap-4 mb-4">
                    <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $presc['image_path'] ?>" 
                         class="w-32 h-32 object-cover border-2 border-deep-green cursor-pointer"
                         onclick="window.open(this.src)">
                    <div>
                        <h3 class="font-bold"><?= htmlspecialchars($presc['full_name']) ?></h3>
                        <p><?= htmlspecialchars($presc['phone']) ?></p>
                        <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($presc['notes']) ?></p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <a href="<?= SITE_URL ?>/views/salesman/pos.php?prescription_id=<?= $presc['id'] ?>" 
                       class="btn btn-primary flex-1">
                        🛒 Create POS Order
                    </a>
                    <button onclick="rejectPrescription(<?= $presc['id'] ?>)" class="btn btn-outline border-red-500 text-red-600">
                        ❌ Reject
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>