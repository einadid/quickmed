<?php
/**
 * Blog Details Page - View Single Article
 */
require_once 'config.php';

$postId = intval($_GET['id'] ?? 0);

// Fetch Post with Author Info
$query = "SELECT hp.*, u.full_name, u.profile_image, u.member_id 
          FROM health_posts hp 
          JOIN users u ON hp.author_id = u.id 
          WHERE hp.id = ? AND hp.is_published = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $postId);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    redirect('404.php'); // Redirect if not found
}

// Increment Views
$conn->query("UPDATE health_posts SET views = views + 1 WHERE id = $postId");

// Fetch Related Posts
$related = $conn->query("SELECT * FROM health_posts WHERE category = '{$post['category']}' AND id != $postId LIMIT 3");

$pageTitle = $post['title'];
include 'includes/header.php';
?>

<!-- Hero Image -->
<div class="relative h-[60vh] w-full overflow-hidden">
    <div class="absolute inset-0 bg-black/50 z-10"></div>
    <img src="<?= SITE_URL ?>/uploads/news/<?= $post['image'] ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($post['title']) ?>">
    
    <div class="absolute inset-0 z-20 flex flex-col justify-center items-center text-center text-white px-4" data-aos="fade-up">
        <span class="bg-lime-accent text-deep-green px-4 py-1 rounded-full font-bold text-sm mb-4 shadow-lg uppercase tracking-widest">
            <?= $post['category'] ?>
        </span>
        <h1 class="text-4xl md:text-6xl font-bold font-mono leading-tight max-w-4xl drop-shadow-lg">
            <?= htmlspecialchars($post['title']) ?>
        </h1>
        
        <div class="flex items-center gap-6 mt-6 text-sm md:text-base">
            <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                <img src="<?= $post['profile_image'] ? SITE_URL.'/uploads/profiles/'.$post['profile_image'] : 'https://ui-avatars.com/api/?name='.urlencode($post['full_name']) ?>" class="w-8 h-8 rounded-full border-2 border-white">
                <span class="font-bold">Dr. <?= htmlspecialchars($post['full_name']) ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span>📅 <?= date('d M Y', strtotime($post['created_at'])) ?></span>
                <span>•</span>
                <span>👁️ <?= number_format($post['views']) ?> Views</span>
            </div>
        </div>
    </div>
</div>

<section class="container mx-auto px-4 py-16 grid lg:grid-cols-12 gap-12">
    
    <!-- Main Content -->
    <div class="lg:col-span-8">
        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl border-t-4 border-deep-green prose max-w-none">
            <?= nl2br($post['content']) ?> <!-- You can use html_entity_decode if storing HTML -->
        </div>

        <!-- Author Box -->
        <div class="mt-12 bg-off-white p-8 rounded-xl border-l-8 border-lime-accent flex items-center gap-6 shadow-sm">
            <img src="<?= $post['profile_image'] ? SITE_URL.'/uploads/profiles/'.$post['profile_image'] : 'https://ui-avatars.com/api/?name='.urlencode($post['full_name']) ?>" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Written By</p>
                <h3 class="text-2xl font-bold text-deep-green">Dr. <?= htmlspecialchars($post['full_name']) ?></h3>
                <p class="text-gray-600 text-sm mt-1">Certified Medical Professional at QuickMed</p>
                <div class="mt-3 flex gap-3">
                    <span class="bg-deep-green text-white px-3 py-1 rounded text-xs font-bold">ID: <?= $post['member_id'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-4 space-y-8">
        
        <!-- Share -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="font-bold text-lg mb-4 border-b pb-2">Share this Article</h3>
            <div class="flex gap-2">
                <button class="flex-1 bg-blue-600 text-white py-2 rounded font-bold hover:opacity-90">FB</button>
                <button class="flex-1 bg-sky-500 text-white py-2 rounded font-bold hover:opacity-90">TW</button>
                <button class="flex-1 bg-green-500 text-white py-2 rounded font-bold hover:opacity-90">WA</button>
            </div>
        </div>

        <!-- Related Posts -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="font-bold text-lg mb-6 border-b pb-2 text-deep-green">Related Articles</h3>
            <div class="space-y-6">
                <?php while ($rel = $related->fetch_assoc()): ?>
                    <a href="blog-details.php?id=<?= $rel['id'] ?>" class="flex gap-4 group">
                        <div class="w-20 h-20 flex-shrink-0 overflow-hidden rounded-lg">
                            <img src="<?= SITE_URL ?>/uploads/news/<?= $rel['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-lime-600 uppercase"><?= $rel['category'] ?></span>
                            <h4 class="font-bold text-gray-800 group-hover:text-deep-green leading-tight transition">
                                <?= htmlspecialchars($rel['title']) ?>
                            </h4>
                            <p class="text-xs text-gray-400 mt-1"><?= date('d M Y', strtotime($rel['created_at'])) ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>