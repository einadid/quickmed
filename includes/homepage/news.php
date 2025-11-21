<?php
/**
 * Latest News & Updates
 */

$newsQuery = "SELECT * FROM news 
              WHERE is_published = 1 
              ORDER BY published_at DESC 
              LIMIT 3";
$newsResult = $conn->query($newsQuery);

if ($newsResult->num_rows > 0):
?>

<section class="container mx-auto px-4 py-16">
    <div class="text-center mb-12" data-aos="fade-up">
        <h2 class="text-4xl font-bold text-green mb-4 uppercase">
            📰 <?= __('latest_news') ?> 📰
        </h2>
        <div class="bg-lime-accent inline-block px-6 py-2 border-4 border-green">
            <p class="text-green font-bold">Stay Updated with QuickMed</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php 
        $delay = 0;
        while ($news = $newsResult->fetch_assoc()): 
        ?>
            <div 
                class="card bg-white"
                data-aos="fade-up"
                data-aos-delay="<?= $delay ?>"
            >
                <!-- News Image -->
                <?php if ($news['image']): ?>
                    <div class="bg-gray-100 mb-4 border-2 border-green overflow-hidden">
                        <img 
                            src="<?= SITE_URL ?>/uploads/news/<?= $news['image'] ?>" 
                            alt="<?= htmlspecialchars($news['title']) ?>"
                            class="w-full h-48 object-cover"
                            loading="lazy"
                        >
                    </div>
                <?php endif; ?>
                
                <!-- News Title -->
                <h3 class="text-xl font-bold text-green mb-3 uppercase">
                    <?= htmlspecialchars($news['title']) ?>
                </h3>
                
                <!-- News Excerpt -->
                <p class="text-gray-700 mb-4 leading-relaxed">
                    <?= substr(strip_tags($news['content']), 0, 120) ?>...
                </p>
                
                <!-- Date & Read More -->
                <div class="flex justify-between items-center border-t-2 border-green pt-4">
                    <span class="text-sm text-gray-500">
                        📅 <?= date('M d, Y', strtotime($news['published_at'])) ?>
                    </span>
                    <a 
                        href="<?= SITE_URL ?>/news.php?id=<?= $news['id'] ?>" 
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
</section>

<?php endif; ?>