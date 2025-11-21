<?php
/**
 * Doctor - Edit News
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$newsId = intval($_GET['id'] ?? 0);
$user = getCurrentUser();

// Fetch News & Verify Ownership
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ? AND author_id = ?");
$stmt->bind_param("ii", $newsId, $user['id']);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();

if (!$news) {
    $_SESSION['error'] = 'News not found or access denied';
    redirect('views/doctor/dashboard.php'); // Redirect to list if you have one
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_news'])) {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $link = clean($_POST['source_link']);
    
    $image = $news['image'];
    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['news_image'], NEWS_DIR);
        if ($uploadRes['success']) $image = $uploadRes['filename'];
    }
    
    $updateStmt = $conn->prepare("UPDATE news SET title=?, content=?, source_link=?, image=? WHERE id=?");
    $updateStmt->bind_param("ssssi", $title, $content, $link, $image, $newsId);
    
    if ($updateStmt->execute()) {
        $_SESSION['success'] = 'News updated successfully!';
        redirect('views/doctor/dashboard.php');
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto card bg-white border-4 border-deep-green p-8">
        <h1 class="text-3xl font-bold text-deep-green mb-8 uppercase border-b-4 border-lime-accent pb-2 inline-block">✏️ Edit News</h1>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Headline</label>
                <input type="text" name="title" class="input w-full border-2 border-deep-green" value="<?= htmlspecialchars($news['title']) ?>" required>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Source Link</label>
                <input type="url" name="source_link" class="input w-full border-2 border-deep-green" value="<?= htmlspecialchars($news['source_link']) ?>">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Cover Image</label>
                <input type="file" name="news_image" class="input w-full border-2 border-deep-green bg-gray-50" accept="image/*">
                <?php if($news['image']): ?>
                    <img src="<?= SITE_URL ?>/uploads/news/<?= $news['image'] ?>" class="h-20 mt-2 rounded border">
                <?php endif; ?>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Content</label>
                <textarea name="content" rows="8" class="input w-full border-2 border-deep-green" required><?= htmlspecialchars($news['content']) ?></textarea>
            </div>

            <button type="submit" name="update_news" class="btn btn-primary w-full py-4 text-xl font-bold shadow-lg">
                💾 Update News
            </button>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>