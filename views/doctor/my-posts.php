<?php
/**
 * Doctor - Manage My Posts (Articles & News)
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$user = getCurrentUser();
$pageTitle = 'My Publications';

// Handle Delete (Generic for both tables)
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = intval($_GET['delete']);
    $table = $_GET['type'] === 'news' ? 'news' : 'health_posts';
    
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $user['id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = ucfirst($_GET['type']) . ' deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete.';
    }
    redirect('views/doctor/my-posts.php');
}

// Fetch Articles
$articles = $conn->query("SELECT * FROM health_posts WHERE author_id = {$user['id']} ORDER BY created_at DESC");

// Fetch News
$news = $conn->query("SELECT * FROM news WHERE author_id = {$user['id']} ORDER BY created_at DESC");

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="flex justify-between items-center mb-8" data-aos="fade-down">
        <div>
            <h1 class="text-4xl font-bold text-deep-green font-mono uppercase">📚 My Publications</h1>
            <p class="text-gray-600">Manage your articles and news updates</p>
        </div>
        <div class="flex gap-4">
            <a href="create-post.php" class="btn btn-primary">+ Write Article</a>
            <a href="add-news.php" class="btn btn-outline border-deep-green text-deep-green hover:bg-deep-green hover:text-white">+ Publish News</a>
        </div>
    </div>

    <!-- TABS -->
    <div class="flex gap-4 mb-8 border-b-2 border-gray-200 pb-2">
        <button onclick="switchTab('articles')" id="tab-articles" class="text-lg font-bold text-deep-green border-b-4 border-deep-green pb-2 px-4 transition-all">
            📝 Articles (<?= $articles->num_rows ?>)
        </button>
        <button onclick="switchTab('news')" id="tab-news" class="text-lg font-bold text-gray-400 hover:text-deep-green pb-2 px-4 transition-all">
            📰 News (<?= $news->num_rows ?>)
        </button>
    </div>

    <!-- ARTICLES SECTION -->
    <div id="articles-section" class="grid md:grid-cols-3 gap-6 animate-fade-in">
        <?php if ($articles->num_rows === 0): ?>
            <div class="col-span-3 text-center py-12 text-gray-500 border-2 border-dashed rounded-lg">
                No articles published yet.
            </div>
        <?php else: ?>
            <?php while ($post = $articles->fetch_assoc()): ?>
                <div class="card bg-white border-4 border-deep-green hover:shadow-xl transition group flex flex-col">
                    <div class="h-48 overflow-hidden relative">
                        <img src="<?= SITE_URL ?>/uploads/news/<?= $post['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <span class="absolute top-2 right-2 bg-lime-accent text-deep-green text-xs font-bold px-2 py-1 rounded shadow">
                            <?= htmlspecialchars($post['category']) ?>
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold mb-2 line-clamp-2"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="text-gray-500 text-xs mb-4">📅 <?= date('d M Y', strtotime($post['created_at'])) ?></p>
                        
                        <div class="flex gap-2 mt-auto">
                            <a href="<?= SITE_URL ?>/blog-details.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline flex-1 text-center">👁️ View</a>
                            <a href="edit-post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-warning flex-1 text-center font-bold">✏️ Edit</a>
                            <a href="?delete=<?= $post['id'] ?>&type=article" class="btn btn-sm btn-danger px-3" onclick="return confirm('Delete this article?')">🗑️</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- NEWS SECTION (Hidden by default) -->
    <div id="news-section" class="hidden grid md:grid-cols-3 gap-6 animate-fade-in">
        <?php if ($news->num_rows === 0): ?>
            <div class="col-span-3 text-center py-12 text-gray-500 border-2 border-dashed rounded-lg">
                No news published yet.
            </div>
        <?php else: ?>
            <?php while ($item = $news->fetch_assoc()): ?>
                <div class="card bg-white border-4 border-blue-500 hover:shadow-xl transition group flex flex-col">
                    <div class="h-48 overflow-hidden relative">
                        <img src="<?= SITE_URL ?>/uploads/news/<?= $item['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php if ($item['source_link']): ?>
                            <span class="absolute top-2 right-2 bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded shadow">Linked 🔗</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold mb-2 line-clamp-2 text-blue-700"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-gray-500 text-xs mb-4">📅 <?= date('d M Y', strtotime($item['created_at'])) ?></p>
                        
                        <div class="flex gap-2 mt-auto">
                            <a href="<?= SITE_URL ?>/news.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline border-blue-500 text-blue-600 flex-1 text-center">👁️ View</a>
                            <a href="edit-news.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning flex-1 text-center font-bold">✏️ Edit</a>
                            <a href="?delete=<?= $item['id'] ?>&type=news" class="btn btn-sm btn-danger px-3" onclick="return confirm('Delete this news?')">🗑️</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</section>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.5s ease-out; }
    .btn-warning { background: #facc15; color: #000; border: none; }
    .btn-danger { background: #ef4444; color: white; border: none; }
</style>

<script>
function switchTab(tab) {
    const articles = document.getElementById('articles-section');
    const news = document.getElementById('news-section');
    const btnArticles = document.getElementById('tab-articles');
    const btnNews = document.getElementById('tab-news');

    if (tab === 'articles') {
        articles.classList.remove('hidden');
        news.classList.add('hidden');
        
        btnArticles.classList.add('border-b-4', 'border-deep-green', 'text-deep-green');
        btnArticles.classList.remove('text-gray-400');
        
        btnNews.classList.remove('border-b-4', 'border-deep-green', 'text-deep-green');
        btnNews.classList.add('text-gray-400');
    } else {
        news.classList.remove('hidden');
        articles.classList.add('hidden');
        
        btnNews.classList.add('border-b-4', 'border-deep-green', 'text-deep-green');
        btnNews.classList.remove('text-gray-400');
        
        btnArticles.classList.remove('border-b-4', 'border-deep-green', 'text-deep-green');
        btnArticles.classList.add('text-gray-400');
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>