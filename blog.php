<?php
/**
 * All Health Blogs & Articles
 */
require_once 'config.php';

$pageTitle = 'Health Blog & Tips';

// Pagination
$page = intval($_GET['page'] ?? 1);
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Fetch Blogs
$query = "SELECT hp.*, u.full_name 
          FROM health_posts hp 
          JOIN users u ON hp.author_id = u.id 
          WHERE hp.is_published = 1 
          ORDER BY hp.created_at DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$blogs = $stmt->get_result();

// Count Total
$totalBlogs = $conn->query("SELECT COUNT(*) as total FROM health_posts WHERE is_published = 1")->fetch_assoc()['total'];
$totalPages = ceil($totalBlogs / $perPage);

include 'includes/header.php';
?>

<?php qm_hero('Health Library', 'Expert advice, tips and medical insights from our doctors.', 'QuickMed Blog', '📚'); ?>

<section class="container mx-auto px-4 py-12 min-h-screen">
    <?php if ($blogs->num_rows === 0): ?>
        <?php qm_empty('No articles yet', 'Our doctors are preparing helpful content for you.', '← Back Home', SITE_URL . '/index.php'); ?>
    <?php else: ?>
    <div class="grid md:grid-cols-3 gap-6">
        <?php while ($blog = $blogs->fetch_assoc()): ?>
            <article class="product-card card-hover">
                <a href="blog-details.php?id=<?= $blog['id'] ?>" class="relative block overflow-hidden" style="height:12rem">
                    <?php if (!empty($blog['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($blog['image']) ?>" class="w-full h-full object-cover" alt="" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-5xl">📚</div>
                    <?php endif; ?>
                    <span class="badge badge-lime absolute top-3 left-3"><?= htmlspecialchars($blog['category'] ?? 'General') ?></span>
                </a>
                <div class="p-body">
                    <h3 class="p-name line-clamp-2">
                        <a href="blog-details.php?id=<?= $blog['id'] ?>" class="hover:underline"><?= htmlspecialchars($blog['title']) ?></a>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1 mb-3 line-clamp-3"><?= htmlspecialchars(substr(strip_tags($blog['content']), 0, 110)) ?>...</p>
                    <div class="flex justify-between items-center border-t border-dashed border-gray-200 pt-3 mt-auto text-xs text-gray-400 font-bold">
                        <span>🩺 <?= htmlspecialchars($blog['full_name']) ?></span>
                        <span><?= date('d M Y', strtotime($blog['created_at'])) ?></span>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>