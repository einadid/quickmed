<?php
/**
 * Admin - Manage News
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Manage News - Admin';
$user = getCurrentUser();

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    $id = intval($_POST['id'] ?? 0);
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['image'], NEWS_DIR);
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        }
    }
    
    if ($id > 0) {
        // Update
        $query = "UPDATE news SET title=?, content=?, is_published=?";
        $types = "ssi";
        $params = [$title, $content, $isPublished];
        
        if ($image) {
            $query .= ", image=?";
            $types .= "s";
            $params[] = $image;
        }
        
        $query .= ", updated_at=NOW() WHERE id=?";
        $types .= "i";
        $params[] = $id;
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'News updated successfully';
        }
    } else {
        // Insert
        $publishedAt = $isPublished ? date('Y-m-d H:i:s') : null;
        $query = "INSERT INTO news (title, content, image, author_id, is_published, published_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssiss", $title, $content, $image, $user['id'], $isPublished, $publishedAt);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'News added successfully';
        }
    }
    
    redirect('news.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $deleteQuery = "DELETE FROM news WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'News deleted successfully';
    }
    
    redirect('news.php');
}

// Get all news
$newsQuery = "SELECT n.*, u.full_name as author_name 
              FROM news n 
              LEFT JOIN users u ON n.author_id = u.id 
              ORDER BY n.created_at DESC";
$news = $conn->query($newsQuery);

include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8" data-aos="fade-down">
            <h1 class="text-5xl font-bold text-deep-green font-mono uppercase">📰 Manage News</h1>
            <div class="flex gap-4">
                <a href="<?= SITE_URL ?>/views/admin/dashboard.php" class="btn btn-outline">← Dashboard</a>
                <button onclick="openAddModal()" class="btn btn-primary">+ Add News</button>
            </div>
        </div>

        <!-- News List -->
        <div class="card bg-white border-4 border-deep-green" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $news->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= SITE_URL ?>/uploads/news/<?= $item['image'] ?>" class="w-20 h-20 object-cover border-2 border-deep-green">
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-gray-200 border-2 border-gray-400 flex items-center justify-center">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td class="font-bold max-w-xs"><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars($item['author_name']) ?></td>
                                <td>
                                    <?php if ($item['is_published']): ?>
                                        <span class="badge badge-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div class="flex gap-2">
                                        <button onclick='editNews(<?= json_encode($item) ?>)' class="btn btn-outline btn-sm">✏️ Edit</button>
                                        <button onclick="deleteNews(<?= $item['id'] ?>)" class="btn btn-outline btn-sm border-red-500 text-red-600">🗑️ Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Add/Edit Modal -->
<div id="newsModal" class="modal-overlay hidden">
    <div class="modal max-w-4xl">
        <div class="modal-header">
            <h3 class="text-2xl font-bold" id="modalTitle">Add News</h3>
            <button onclick="closeModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="newsId">
                
                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Title *</label>
                    <input type="text" name="title" id="newsTitle" class="input border-4 border-deep-green" required>
                </div>

                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Content *</label>
                    <textarea name="content" id="newsContent" rows="10" class="input border-4 border-deep-green" required></textarea>
                </div>

                <div class="mb-6">
                    <label class="block font-bold mb-2 text-deep-green text-lg">Image</label>
                    <input type="file" name="image" class="input border-4 border-deep-green" accept="image/*">
                </div>

                <div class="mb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_published" id="newsPublished" class="w-6 h-6">
                        <span class="font-bold text-deep-green">Publish Immediately</span>
                    </label>
                </div>

                <button type="submit" name="save_news" class="btn btn-primary w-full text-xl py-4">
                    💾 Save News
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('newsModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add News';
    document.querySelector('form').reset();
    document.getElementById('newsId').value = '';
}

function editNews(news) {
    document.getElementById('newsModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit News';
    document.getElementById('newsId').value = news.id;
    document.getElementById('newsTitle').value = news.title;
    document.getElementById('newsContent').value = news.content;
    document.getElementById('newsPublished').checked = news.is_published == 1;
}

function closeModal() {
    document.getElementById('newsModal').classList.add('hidden');
}

async function deleteNews(id) {
    const result = await Swal.fire({
        title: 'Delete News?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        window.location.href = '?delete=' + id;
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>