<?php
/**
 * Doctor - Edit Article (FIXED)
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$postId = intval($_GET['id'] ?? 0);
$user = getCurrentUser();

// Fetch Post
$stmt = $conn->prepare("SELECT * FROM health_posts WHERE id = ? AND author_id = ?");
$stmt->bind_param("ii", $postId, $user['id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    redirect('views/doctor/my-posts.php');
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post'])) {
    $title = clean($_POST['title']);
    $category = clean($_POST['category']);
    
    // FIX: Use htmlspecialchars only, NOT clean() which might strip newlines incorrectly
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    
    $image = $post['image'];
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['post_image'], NEWS_DIR);
        if ($uploadRes['success']) $image = $uploadRes['filename'];
    }
    
    $updateStmt = $conn->prepare("UPDATE health_posts SET title=?, category=?, content=?, image=? WHERE id=?");
    $updateStmt->bind_param("ssssi", $title, $category, $content, $image, $postId);
    
    if ($updateStmt->execute()) {
        $_SESSION['success'] = 'Article updated successfully!';
        redirect('views/doctor/my-posts.php');
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto card bg-white border-4 border-deep-green p-8">
        <h1 class="text-3xl font-bold text-deep-green mb-8 border-b-4 border-lime-accent pb-2 inline-block">✏️ Edit Article</h1>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-bold text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" class="input w-full border-2 border-deep-green" value="<?= htmlspecialchars_decode($post['title']) ?>" required>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-2">Category</label>
                    <select name="category" class="input w-full border-2 border-deep-green">
                        <?php 
                        $cats = ['General Health', 'Diet & Nutrition', 'Fitness', 'Mental Health', 'Disease Prevention'];
                        foreach($cats as $c) {
                            $sel = $post['category'] == $c ? 'selected' : '';
                            echo "<option $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Cover Image</label>
                <input type="file" name="post_image" class="input w-full border-2 border-deep-green bg-gray-50" accept="image/*">
                <?php if($post['image']): ?>
                    <img src="<?= SITE_URL ?>/uploads/news/<?= $post['image'] ?>" class="h-20 mt-2 rounded border shadow">
                <?php endif; ?>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Content</label>
                <!-- FIX: Use htmlspecialchars_decode to show real text in textarea -->
                <textarea name="content" rows="15" class="input w-full border-2 border-deep-green font-sans text-lg leading-relaxed p-4" required><?= htmlspecialchars_decode($post['content']) ?></textarea>
            </div>

            <button type="submit" name="update_post" class="btn btn-primary w-full py-4 text-xl font-bold shadow-lg transform hover:scale-105 transition">
                💾 Save Changes
            </button>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>