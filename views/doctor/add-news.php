<?php
/**
 * Doctor - Add Medical News (With Link)
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$pageTitle = 'Publish Medical News';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish_news'])) {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $link = clean($_POST['source_link']); // Get Link
    
    $image = null;
    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['news_image'], NEWS_DIR);
        if ($uploadRes['success']) $image = $uploadRes['filename'];
    }
    
    // Insert with Link
    $stmt = $conn->prepare("INSERT INTO news (title, content, source_link, image, author_id, is_published, published_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    $stmt->bind_param("ssssi", $title, $content, $link, $image, $user['id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'News published successfully!';
        redirect('views/doctor/dashboard.php');
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto card bg-white border-4 border-deep-green p-8">
        <div class="flex justify-between items-center mb-8 border-b-4 border-lime-accent pb-4">
            <h1 class="text-3xl font-bold text-deep-green uppercase">📰 Publish News</h1>
            <a href="dashboard.php" class="btn btn-outline">← Back</a>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block font-bold text-gray-700 mb-2">News Headline</label>
                <input type="text" name="title" class="input w-full border-2 border-deep-green" required placeholder="e.g. New Vaccine Launched">
            </div>

            <!-- NEW LINK FIELD -->
            <div>
                <label class="block font-bold text-gray-700 mb-2">Source Link / Read More URL</label>
                <input type="url" name="source_link" class="input w-full border-2 border-deep-green" placeholder="https://example.com/news-article">
                <p class="text-xs text-gray-500 mt-1">Users will be redirected here when clicking the news.</p>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Cover Image</label>
                <input type="file" name="news_image" class="input w-full border-2 border-deep-green bg-gray-50" accept="image/*">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Short Description</label>
                <textarea name="content" rows="5" class="input w-full border-2 border-deep-green" placeholder="Brief summary of the news..." required></textarea>
            </div>

            <button type="submit" name="publish_news" class="btn btn-primary w-full py-4 text-xl font-bold shadow-lg transform hover:scale-105 transition">
                📢 Publish News
            </button>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>