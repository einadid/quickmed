<?php
/**
 * Latest News & Updates - Compact Responsive v3.0
 */
$newsQuery = "SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3";
$newsResult = $conn->query($newsQuery);
if ($newsResult && $newsResult->num_rows > 0):
?>

<section class="container mx-auto px-3 sm:px-4 py-8 sm:py-12">
    <div class="text-center mb-6 sm:mb-8" data-aos="fade-up">
        <h2 class="text-[1.3rem] sm:text-xl md:text-2xl font-bold text-deep-green mb-2 uppercase">📰 Latest News</h2>
        <div class="bg-lime-accent inline-block px-3 sm:px-4 py-1 border-2 border-deep-green transform -rotate-1 shadow-[2px_2px_0px_#065f46] rounded">
            <p class="text-deep-green font-bold text-[0.75rem] sm:text-[0.82rem]">Stay Updated with Medical World</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
        <?php 
        $delay = 0;
        while ($news = $newsResult->fetch_assoc()): 
            $linkUrl = !empty($news['source_link']) ? $news['source_link'] : SITE_URL . '/news.php?id=' . $news['id'];
            $target = !empty($news['source_link']) ? '_blank' : '_self';
            $icon = !empty($news['source_link']) ? '↗' : '→';
        ?>
            <div class="card bg-white border-[1.5px] border-gray-200 hover:border-deep-green transition-all duration-300 group hover:-translate-y-1 flex flex-col h-full p-0 overflow-hidden rounded-xl" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                <a href="<?= $linkUrl ?>" target="<?= $target ?>" class="h-32 sm:h-36 overflow-hidden border-b border-gray-100 group-hover:border-deep-green relative block bg-gray-50">
                    <?php if ($news['image']): ?>
                        <img src="<?= SITE_URL ?>/uploads/news/<?= $news['image'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-3xl text-gray-300">📰</div>
                    <?php endif; ?>
                    <?php if (!empty($news['source_link'])): ?>
                        <span class="absolute top-2 right-2 bg-white/90 backdrop-blur text-[9px] font-bold px-1.5 py-0.5 rounded shadow text-deep-green">External ↗</span>
                    <?php endif; ?>
                </a>
                <div class="p-3 sm:p-3.5 flex flex-col flex-1">
                    <h3 class="text-[0.88rem] sm:text-[0.92rem] font-bold text-deep-green mb-1.5 line-clamp-2 group-hover:text-lime-600 transition leading-snug">
                        <a href="<?= $linkUrl ?>" target="<?= $target ?>"><?= htmlspecialchars($news['title']) ?></a>
                    </h3>
                    <p class="text-gray-600 mb-3 text-[0.72rem] sm:text-xs line-clamp-2 flex-1 leading-relaxed"><?= substr(strip_tags($news['content']), 0, 90) ?>...</p>
                    <div class="flex justify-between items-center border-t border-dashed border-gray-200 pt-2.5 mt-auto">
                        <span class="text-[10px] text-gray-400 font-bold flex items-center gap-1">📅 <?= date('d M Y', strtotime($news['published_at'])) ?></span>
                        <a href="<?= $linkUrl ?>" target="<?= $target ?>" class="text-[11px] font-bold text-white bg-deep-green px-2.5 py-1 rounded-lg hover:bg-lime-accent hover:text-deep-green transition-all flex items-center gap-1">Read <?= $icon ?></a>
                    </div>
                </div>
            </div>
        <?php $delay += 60; endwhile; ?>
    </div>
    
    <div class="text-center mt-6">
        <a href="<?= SITE_URL ?>/news.php" class="btn btn-outline btn-sm text-[0.78rem] px-5 py-2">View All News →</a>
    </div>
</section>

<?php endif; ?>
