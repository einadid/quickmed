<?php
/**
 * Doctor - Create Health Post (FIXED)
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$pageTitle = 'Create New Post';
$user = getCurrentUser();

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish_post'])) {
    $title = clean($_POST['title']);
    $category = clean($_POST['category']);
    
    // FIX: Don't use clean() for content to preserve new lines, just escape
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    
    $image = null;
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['post_image'], NEWS_DIR);
        if ($uploadRes['success']) $image = $uploadRes['filename'];
    }
    
    $stmt = $conn->prepare("INSERT INTO health_posts (title, category, content, image, author_id, is_published, published_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    $stmt->bind_param("ssssi", $title, $category, $content, $image, $user['id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Post published successfully!';
        redirect('views/doctor/my-posts.php');
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-4xl mx-auto card bg-white border-4 border-deep-green p-8">
        <h1 class="text-3xl font-bold text-deep-green mb-8 border-b-4 border-lime-accent pb-2 inline-block">✍️ Write New Article</h1>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-bold text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" class="input w-full border-2 border-deep-green" required placeholder="e.g. Benefits of Vitamin C">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-2">Category</label>
                    <select name="category" class="input w-full border-2 border-deep-green">
                        <option>General Health</option>
                        <option>Diet & Nutrition</option>
                        <option>Fitness</option>
                        <option>Mental Health</option>
                        <option>Disease Prevention</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Cover Image</label>
                <input type="file" name="post_image" class="input w-full border-2 border-deep-green bg-gray-50" accept="image/*" required>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Content</label>
                <textarea name="content" rows="10" class="input w-full border-2 border-deep-green font-sans text-lg leading-relaxed p-4" placeholder="Write your article here... (New lines are preserved)" required></textarea>
            </div>

            <button type="submit" name="publish_post" class="btn btn-primary w-full py-4 text-xl font-bold shadow-lg transform hover:scale-105 transition">
                🚀 Publish Article
            </button>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>