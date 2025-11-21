<?php
/**
 * Homepage - Health Blog Section (Dynamic)
 */
$blogs = $conn->query("SELECT hp.*, u.full_name, u.profile_image 
                       FROM health_posts hp 
                       JOIN users u ON hp.author_id = u.id 
                       WHERE hp.is_published = 1 
                       ORDER BY hp.created_at DESC 
                       LIMIT 3");
?>

<section class="py-20 bg-gray-50 relative overflow-hidden">
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl font-bold text-deep-green mb-2 font-mono uppercase">📚 Health Insights</h2>
            <p class="text-gray-600">Expert advice from our certified doctors</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php while ($blog = $blogs->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:-translate-y-2 transition-all duration-300 border border-gray-200" data-aos="fade-up">
                    
                    <!-- Image -->
                    <div class="h-56 overflow-hidden relative group">
                        <img src="<?= SITE_URL ?>/uploads/news/<?= $blog['image'] ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                        <div class="absolute top-4 left-4 bg-deep-green text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                            <?= $blog['category'] ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-3 hover:text-deep-green transition">
                            <a href="blog-details.php?id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a>
                        </h3>
                        <p class="text-gray-500 text-sm line-clamp-3 mb-4">
    <?= substr(strip_tags(str_replace(["\r", "\n"], ' ', $blog['content'])), 0, 120) ?>...
</p>

                        <!-- Author Info -->
                        <div class="flex items-center gap-3 border-t pt-4">
                            <img src="<?= $blog['profile_image'] ? SITE_URL.'/uploads/profiles/'.$blog['profile_image'] : 'https://ui-avatars.com/api/?name='.urlencode($blog['full_name']) ?>" class="w-10 h-10 rounded-full border-2 border-lime-accent">
                            <div>
                                <p class="text-sm font-bold text-deep-green">Dr. <?= htmlspecialchars($blog['full_name']) ?></p>
                                <p class="text-xs text-gray-400"><?= date('M d, Y', strtotime($blog['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="blog.php" class="btn btn-outline border-deep-green text-deep-green hover:bg-deep-green hover:text-white px-8 py-3 rounded-full font-bold transition">
                View All Articles
            </a>
        </div>
    </div>
</section>