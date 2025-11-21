<?php
/**
 * Doctor - Review Prescriptions
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$pageTitle = 'Review Prescriptions';

// Handle Review
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['action'] === 'approve' ? 'reviewed' : 'rejected';
    $doctorId = $_SESSION['user_id'];
    
    $conn->query("UPDATE prescriptions SET status='$status', reviewed_by=$doctorId, reviewed_at=NOW() WHERE id=$id");
    $_SESSION['success'] = "Prescription " . ucfirst($status);
    redirect('views/doctor/prescriptions.php');
}

// Get Pending
$pending = $conn->query("SELECT p.*, u.full_name, u.phone FROM prescriptions p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at ASC");

// Get Recent History
$history = $conn->query("SELECT p.*, u.full_name FROM prescriptions p JOIN users u ON p.user_id = u.id WHERE p.reviewed_by = {$_SESSION['user_id']} ORDER BY p.reviewed_at DESC LIMIT 10");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <h1 class="text-3xl font-bold text-deep-green mb-8">🩺 Prescription Review Panel</h1>

    <!-- Pending Section -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-yellow-600 mb-6 border-b-4 border-yellow-500 pb-2 inline-block">
            ⏳ Pending Review (<?= $pending->num_rows ?>)
        </h2>

        <?php if ($pending->num_rows === 0): ?>
            <div class="bg-green-50 p-8 text-center rounded border border-green-200 text-green-800">
                ✅ All caught up! No pending prescriptions.
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 gap-6">
                <?php while ($row = $pending->fetch_assoc()): ?>
                    <div class="card bg-white border-4 border-deep-green p-0 overflow-hidden shadow-lg">
                        <!-- Image Zoom -->
                        <div class="h-64 overflow-hidden cursor-zoom-in border-b-4 border-deep-green relative group" onclick="window.open('<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>', '_blank')">
                            <img src="<?= SITE_URL ?>/uploads/prescriptions/<?= $row['image_path'] ?>" class="w-full h-full object-contain bg-gray-100 transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="bg-white text-deep-green px-4 py-2 rounded font-bold shadow">🔍 View Full Size</span>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-xl"><?= htmlspecialchars($row['full_name']) ?></h3>
                                    <p class="text-gray-600">📞 <?= htmlspecialchars($row['phone']) ?></p>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-xs font-bold">PENDING</span>
                            </div>

                            <?php if($row['notes']): ?>
                                <div class="bg-gray-50 p-3 border rounded text-sm italic mb-6">
                                    "<?= htmlspecialchars($row['notes']) ?>"
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex gap-4">
                                <a href="?action=approve&id=<?= $row['id'] ?>" class="btn btn-success flex-1 text-center font-bold shadow hover:shadow-lg transform hover:-translate-y-1 transition">
                                    ✅ Approve
                                </a>
                                <a href="?action=reject&id=<?= $row['id'] ?>" class="btn btn-outline border-red-500 text-red-600 flex-1 text-center shadow hover:bg-red-50" onclick="return confirm('Reject this prescription?')">
                                    ❌ Reject
                                </a>
                            </div>
                            
                            <p class="text-xs text-gray-400 mt-4 text-center">Uploaded: <?= timeAgo($row['created_at']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Section -->
    <div>
        <h2 class="text-2xl font-bold text-gray-600 mb-6 border-b-4 border-gray-300 pb-2 inline-block">
            📜 Recent History
        </h2>
        <div class="overflow-x-auto bg-white border rounded shadow-sm">
            <table class="table w-full text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4">Patient</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Review Date</th>
                        <th class="p-4">View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hist = $history->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 font-bold"><?= htmlspecialchars($hist['full_name']) ?></td>
                            <td class="p-4">
                                <span class="badge <?= $hist['status'] == 'reviewed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $hist['status'] == 'reviewed' ? 'Approved' : 'Rejected' ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-500"><?= date('d M, h:i A', strtotime($hist['reviewed_at'])) ?></td>
                            <td class="p-4">
                                <a href="<?= SITE_URL ?>/uploads/prescriptions/<?= $hist['image_path'] ?>" target="_blank" class="text-blue-600 hover:underline text-sm">Image ↗</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>