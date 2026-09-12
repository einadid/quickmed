<?php
/**
 * Homepage - Health Blog Section - Compact Responsive v3.0
 */
$blogs = $conn->query("SELECT hp.*, u.full_name, u.profile_image 
                       FROM health_posts hp 
                       JOIN users u ON hp.author_id = u.id 
                       WHERE hp.is_published = 1 
                       ORDER BY hp.created_at DESC 
                       LIMIT 3");
if($blogs && $blogs->num_rows > 0):
?>

<section class="py-8 sm:py-12 bg-gray-50 relative overflow-hidden">
    <div class="container mx-auto px-3 sm:px-4 relative z-10">
        <?php qm_section_head('📚 Health Insights', 'Expert advice from our certified doctors', 'Health Blog'); ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            <?php while ($blog = $blogs->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300 group" data-aos="fade-up">
                    <div class="h-36 sm:h-40 overflow-hidden relative bg-gray-100">
                        <?php if (!empty($blog['image'])): ?>
                            <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($blog['image']) ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-4xl">📚</div>
                        <?php endif; ?>
                        <div class="absolute top-2.5 left-2.5">
                            <span class="badge badge-green text-[10px] px-2 py-0.5"><?= htmlspecialchars($blog['category'] ?? 'General') ?></span>
                        </div>
                    </div>
                    <div class="p-3.5 sm:p-4">
                        <h3 class="text-[0.9rem] sm:text-[0.95rem] font-bold text-gray-800 mb-1.5 hover:text-deep-green transition line-clamp-2 leading-snug">
                            <a href="blog-details.php?id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a>
                        </h3>
                        <p class="text-gray-500 text-[0.75rem] sm:text-xs line-clamp-2 mb-3 leading-relaxed"><?= substr(strip_tags(str_replace(["\r", "\n"], ' ', $blog['content'])), 0, 90) ?>...</p>
                        <div class="flex items-center gap-2 border-t border-gray-100 pt-2.5">
                            <img src="<?= $blog['profile_image'] ? SITE_URL.'/uploads/profiles/'.$blog['profile_image'] : 'https://ui-avatars.com/api/?name='.urlencode($blog['full_name']) ?>" class="w-7 h-7 rounded-full border">
                            <div class="min-w-0">
                                <p class="text-[11px] sm:text-xs font-bold text-deep-green truncate">Dr. <?= htmlspecialchars($blog['full_name']) ?></p>
                                <p class="text-[10px] text-gray-400"><?= date('M d, Y', strtotime($blog['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-6">
            <a href="blog.php" class="btn btn-outline btn-sm text-[0.78rem] sm:text-[0.82rem] px-5 py-2 rounded-full">
                View All Articles →
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
