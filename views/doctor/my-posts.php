<?php
/**
 * Doctor - My Posts
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$user = getCurrentUser();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM health_posts WHERE id = $id AND author_id = {$user['id']}");
    $_SESSION['success'] = 'Post deleted.';
    redirect('views/doctor/my-posts.php');
}

// Fetch Posts
$posts = $conn->query("SELECT * FROM health_posts WHERE author_id = {$user['id']} ORDER BY created_at DESC");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-deep-green">📚 My Articles</h1>
        <a href="create-post.php" class="btn btn-primary">+ Create New</a>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="card bg-white border-4 border-deep-green hover:shadow-xl transition group">
                <div class="h-48 overflow-hidden">
                    <img src="<?= SITE_URL ?>/uploads/news/<?= $post['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
                <div class="p-6">
                    <span class="text-xs bg-lime-accent px-2 py-1 rounded font-bold text-deep-green"><?= $post['category'] ?></span>
                    <h3 class="text-xl font-bold mt-2 mb-2"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="text-gray-500 text-xs mb-4">📅 <?= date('d M Y', strtotime($post['created_at'])) ?></p>
                    
                    <div class="flex gap-2">
                        <a href="<?= SITE_URL ?>/blog-details.php?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm flex-1 text-center">View</a>
                        <a href="?delete=<?= $post['id'] ?>" class="btn btn-outline border-red-500 text-red-600 btn-sm" onclick="return confirm('Delete?')">🗑️</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>