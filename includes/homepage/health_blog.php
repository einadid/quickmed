<?php
/**
 * Health Tips & Blog Posts
 */

$blogQuery = "SELECT * FROM health_posts 
              WHERE is_published = 1 
              ORDER BY published_at DESC 
              LIMIT 4";
$blogResult = $conn->query($blogQuery);

if ($blogResult->num_rows > 0):
?>

<section class="container mx-auto px-4 py-16">
    <div class="text-center mb-12" data-aos="fade-up">
        <h2 class="text-4xl font-bold text-green mb-4 uppercase">
            📚 <?= __('health_tips') ?> 📚
        </h2>
        <div class="bg-lime-accent inline-block px-6 py-2 border-4 border-green">
            <p class="text-green font-bold">Expert Health Advice & Wellness Tips</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php 
        $delay = 0;
        while ($post = $blogResult->fetch_assoc()): 
        ?>
            <div 
                class="card bg-white hover:transform hover:scale-105 transition-all"
                data-aos="fade-right"
                data-aos-delay="<?= $delay ?>"
            >
                <!-- Post Image -->
                <?php if ($post['image']): ?>
                    <div class="bg-gray-100 mb-4 border-2 border-green overflow-hidden">
                        <img 
                            src="<?= SITE_URL ?>/uploads/news/<?= $post['image'] ?>" 
                            alt="<?= htmlspecialchars($post['title']) ?>"
                            class="w-full h-48 object-cover"
                            loading="lazy"
                        >
                    </div>
                <?php endif; ?>
                
                <!-- Category Badge -->
                <?php if ($post['category']): ?>
                    <span class="badge badge-info mb-3">
                        <?= htmlspecialchars($post['category']) ?>
                    </span>
                <?php endif; ?>
                
                <!-- Post Title -->
                <h3 class="text-2xl font-bold text-green mb-3 uppercase">
                    <?= htmlspecialchars($post['title']) ?>
                </h3>
                
                <!-- Post Excerpt -->
                <p class="text-gray-700 mb-4 leading-relaxed">
                    <?= substr(strip_tags($post['content']), 0, 150) ?>...
                </p>
                
                <!-- Meta Info -->
                <div class="flex justify-between items-center border-t-2 border-green pt-4">
                    <span class="text-sm text-gray-500">
                        👁️ <?= number_format($post['views']) ?> views
                    </span>
                    <a 
                        href="<?= SITE_URL ?>/blog.php?id=<?= $post['id'] ?>" 
                        class="btn btn-outline btn-sm"
                    >
                        Read More →
                    </a>
                </div>
            </div>
        <?php 
            $delay += 100;
        endwhile; 
        ?>
    </div>
    
    <div class="text-center mt-8" data-aos="fade-up">
        <a href="<?= SITE_URL ?>/blog.php" class="btn btn-primary btn-lg">
            View All Articles →
        </a>
    </div>
</section>

<?php endif; ?>