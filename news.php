<?php
/**
 * Latest News & Updates (FIXED REDIRECT)
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
        <h2 class="text-4xl font-bold text-deep-green mb-4 uppercase">
            📰 Latest News
        </h2>
        <div class="bg-lime-accent inline-block px-6 py-2 border-4 border-deep-green transform -rotate-1 shadow-[4px_4px_0px_#065f46]">
            <p class="text-deep-green font-bold text-lg">Stay Updated with Medical World</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php 
        $delay = 0;
        while ($news = $newsResult->fetch_assoc()): 
            // Determine Link URL & Target
            $linkUrl = !empty($news['source_link']) ? $news['source_link'] : SITE_URL . '/news.php?id=' . $news['id'];
            $target = !empty($news['source_link']) ? '_blank' : '_self';
            $icon = !empty($news['source_link']) ? '↗' : '→';
        ?>
            <div 
                class="card bg-white border-4 border-gray-100 hover:border-deep-green transition-all duration-300 group hover:-translate-y-2 flex flex-col h-full"
                data-aos="fade-up"
                data-aos-delay="<?= $delay ?>"
            >
                <!-- Image -->
                <a href="<?= $linkUrl ?>" target="<?= $target ?>" class="h-48 overflow-hidden border-b-4 border-gray-100 group-hover:border-deep-green relative block">
                    <?php if ($news['image']): ?>
                        <img src="<?= SITE_URL ?>/uploads/news/<?= $news['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-4xl text-gray-300">📰</div>
                    <?php endif; ?>
                    
                    <?php if (!empty($news['source_link'])): ?>
                        <span class="absolute top-3 right-3 bg-white/90 backdrop-blur text-xs font-bold px-2 py-1 rounded shadow text-deep-green">
                            External Link ↗
                        </span>
                    <?php endif; ?>
                </a>
                
                <div class="p-6 flex flex-col flex-1">
                    <!-- Title -->
                    <h3 class="text-xl font-bold text-deep-green mb-3 line-clamp-2 group-hover:text-lime-600 transition leading-tight">
                        <a href="<?= $linkUrl ?>" target="<?= $target ?>">
                            <?= htmlspecialchars($news['title']) ?>
                        </a>
                    </h3>
                    
                    <!-- Excerpt -->
                    <p class="text-gray-600 mb-4 text-sm line-clamp-3 flex-1 leading-relaxed">
                        <?= substr(strip_tags($news['content']), 0, 120) ?>...
                    </p>
                    
                    <!-- Footer -->
                    <div class="flex justify-between items-center border-t border-dashed border-gray-300 pt-4 mt-auto">
                        <span class="text-xs text-gray-400 font-bold flex items-center gap-1">
                            📅 <?= date('d M Y', strtotime($news['published_at'])) ?>
                        </span>
                        
                        <a href="<?= $linkUrl ?>" target="<?= $target ?>" class="text-sm font-bold text-white bg-deep-green px-4 py-2 rounded hover:bg-lime-accent hover:text-deep-green transition-all shadow-sm flex items-center gap-2">
                            Read More <span><?= $icon ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php 
            $delay += 100;
        endwhile; 
        ?>
    </div>
    
    <div class="text-center mt-12">
        <a href="<?= SITE_URL ?>/news-archive.php" class="btn btn-outline border-deep-green text-deep-green hover:bg-deep-green hover:text-white px-8 py-3 rounded font-bold transition">
            View All News
        </a>
    </div>
</section>

<?php endif; ?>