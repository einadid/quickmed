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

<section class="bg-deep-green text-white py-20 text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none text-9xl flex justify-between px-20 items-center">
        <span>💊</span><span>🧬</span>
    </div>
    <h1 class="text-5xl font-bold font-mono mb-4 relative z-10">📚 Health Library</h1>
    <p class="text-xl text-lime-accent relative z-10">Expert advice, tips, and medical insights.</p>
</section>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="grid md:grid-cols-3 gap-8">
        <?php while ($blog = $blogs->fetch_assoc()): ?>
            <div class="card bg-white border-4 border-gray-100 hover:border-deep-green transition-all duration-300 group hover:-translate-y-2 overflow-hidden">
                <div class="h-48 overflow-hidden relative">
                    <img src="<?= SITE_URL ?>/uploads/news/<?= $blog['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <span class="absolute top-4 left-4 bg-lime-accent text-deep-green text-xs font-bold px-3 py-1 rounded-full shadow">
                        <?= $blog['category'] ?>
                    </span>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-deep-green mb-2 line-clamp-2">
                        <a href="blog-details.php?id=<?= $blog['id'] ?>" class="hover:underline">
                            <?= htmlspecialchars($blog['title']) ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-3">
                        <?= substr(strip_tags($blog['content']), 0, 100) ?>...
                    </p>
                    <div class="flex justify-between items-center border-t pt-4 text-xs text-gray-400">
                        <span>Dr. <?= htmlspecialchars($blog['full_name']) ?></span>
                        <span><?= date('d M Y', strtotime($blog['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center gap-2 mt-12">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="px-4 py-2 border-2 border-deep-green font-bold <?= $i === $page ? 'bg-deep-green text-white' : 'text-deep-green hover:bg-lime-accent' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>