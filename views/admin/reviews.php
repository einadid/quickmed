<?php
/**
 * Admin - Manage Customer Reviews
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage Reviews - Admin';

// Handle Approval
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE reviews SET is_approved = 1 WHERE id = $id");
    $_SESSION['success'] = 'Review approved successfully!';
    redirect('views/admin/reviews.php');
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE id = $id");
    $_SESSION['success'] = 'Review deleted successfully!';
    redirect('views/admin/reviews.php');
}

// Fetch Pending Reviews (First) then Approved Reviews
$pendingReviews = $conn->query("SELECT r.*, u.full_name, o.order_number 
                                FROM reviews r 
                                JOIN users u ON r.user_id = u.id 
                                LEFT JOIN orders o ON r.order_id = o.id 
                                WHERE r.is_approved = 0 
                                ORDER BY r.created_at DESC");

$approvedReviews = $conn->query("SELECT r.*, u.full_name, o.order_number 
                                 FROM reviews r 
                                 JOIN users u ON r.user_id = u.id 
                                 LEFT JOIN orders o ON r.order_id = o.id 
                                 WHERE r.is_approved = 1 
                                 ORDER BY r.created_at DESC LIMIT 20");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">⭐ Manage Reviews</h1>
            <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
        </div>

        <!-- Pending Reviews Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-yellow-600 mb-4 border-b-4 border-yellow-500 pb-2 inline-block">
                ⏳ Pending Approval (<?= $pendingReviews->num_rows ?>)
            </h2>

            <?php if ($pendingReviews->num_rows === 0): ?>
                <div class="bg-gray-100 p-6 text-center text-gray-500 rounded border-2 border-dashed border-gray-300">
                    No pending reviews at the moment.
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php while ($review = $pendingReviews->fetch_assoc()): ?>
                        <div class="card bg-yellow-50 border-l-8 border-yellow-500 shadow-md p-6 relative group hover:shadow-xl transition-all">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-lg text-deep-green"><?= htmlspecialchars($review['full_name']) ?></h3>
                                    <p class="text-xs text-gray-500">Order #<?= htmlspecialchars($review['order_number']) ?></p>
                                </div>
                                <div class="text-yellow-500 text-xl tracking-widest">
                                    <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                                </div>
                            </div>
                            
                            <p class="text-gray-700 italic bg-white p-3 rounded border border-yellow-200 mb-4">
                                "<?= htmlspecialchars($review['review_text']) ?>"
                            </p>
                            
                            <div class="flex gap-3 mt-auto">
                                <a href="?approve=<?= $review['id'] ?>" class="flex-1 bg-green-600 text-white text-center py-2 font-bold rounded hover:bg-green-700 transition-colors">
                                    ✅ Approve
                                </a>
                                <a href="?delete=<?= $review['id'] ?>" onclick="return confirm('Are you sure?')" class="flex-1 bg-red-500 text-white text-center py-2 font-bold rounded hover:bg-red-600 transition-colors">
                                    🗑️ Delete
                                </a>
                            </div>
                            <div class="text-xs text-gray-400 mt-2 text-right">
                                <?= date('M d, Y h:i A', strtotime($review['created_at'])) ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Approved Reviews History -->
        <div>
            <h2 class="text-2xl font-bold text-deep-green mb-4 border-b-4 border-deep-green pb-2 inline-block">
                ✅ Approved History
            </h2>
            
            <div class="overflow-x-auto bg-white border-4 border-deep-green">
                <table class="table w-full">
                    <thead class="bg-deep-green text-white">
                        <tr>
                            <th class="p-3 text-left">Customer</th>
                            <th class="p-3 text-center">Rating</th>
                            <th class="p-3 text-left">Review</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($review = $approvedReviews->fetch_assoc()): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="p-3 font-bold"><?= htmlspecialchars($review['full_name']) ?></td>
                                <td class="p-3 text-center text-yellow-500 text-lg">
                                    <?= str_repeat('★', $review['rating']) ?>
                                </td>
                                <td class="p-3 text-gray-600 italic max-w-md truncate">
                                    "<?= htmlspecialchars($review['review_text']) ?>"
                                </td>
                                <td class="p-3 text-center">
                                    <a href="?delete=<?= $review['id'] ?>" onclick="return confirm('Delete this review?')" class="text-red-500 hover:text-red-700 font-bold text-xl" title="Delete">
                                        &times;
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>