<?php
/**
 * QuickMed — News (Archive + Single View)  (v2.0 rewrite)
 * -------------------------------------------------------
 *  news.php        → all published news (paginated)
 *  news.php?id=5   → single news article
 */

require_once 'config.php';

$newsId = intval($_GET['id'] ?? 0);

// ---------- SINGLE VIEW ----------
if ($newsId > 0) {
    $stmt = $conn->prepare("SELECT n.*, u.full_name as author_name
                            FROM news n
                            LEFT JOIN users u ON n.author_id = u.id
                            WHERE n.id = ? AND n.is_published = 1 LIMIT 1");
    $stmt->bind_param("i", $newsId);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();

    if (!$article) {
        redirect('404.php');
    }

    // If it's an external link, go straight there
    if (!empty($article['source_link'])) {
        header("Location: " . $article['source_link']);
        exit;
    }

    $pageTitle = $article['title'] . ' - QuickMed News';
    include 'includes/header.php';
    qm_hero('News & Updates', strip_tags(substr($article['title'], 0, 90)), 'QuickMed News', '📰');
    ?>
    <section class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6"><?= qm_back(SITE_URL . '/news.php', '← All News') ?></div>
            <article class="card card-pad-lg" data-aos="fade-up">
                <h1 class="text-3xl md:text-4xl font-bold text-[#065f46] mb-4 leading-tight">
                    <?= htmlspecialchars($article['title']) ?>
                </h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6 pb-6 border-b border-gray-200">
                    <span>📅 <?= date('d M Y', strtotime($article['published_at'] ?: $article['created_at'])) ?></span>
                    <?php if (!empty($article['author_name'])): ?>
                        <span>✍️ <?= htmlspecialchars($article['author_name']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($article['image'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($article['image']) ?>"
                         alt="<?= htmlspecialchars($article['title']) ?>"
                         class="w-full rounded-xl mb-8 shadow-lg"
                         onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-line">
                    <?= nl2br(htmlspecialchars($article['content'])) ?>
                </div>
            </article>

            <?php
            // More news
            $more = $conn->query("SELECT id, title, image, published_at, source_link FROM news
                                  WHERE is_published = 1 AND id != $newsId
                                  ORDER BY published_at DESC LIMIT 3");
            if ($more && $more->num_rows > 0):
            ?>
            <h2 class="text-2xl font-bold text-[#065f46] mt-12 mb-6 font-display">MORE NEWS</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <?php while ($n = $more->fetch_assoc()):
                    $link = !empty($n['source_link']) ? $n['source_link'] : SITE_URL . '/news.php?id=' . $n['id'];
                    $target = !empty($n['source_link']) ? '_blank' : '_self';
                ?>
                <a href="<?= htmlspecialchars($link) ?>" target="<?= $target ?>" class="product-card card-hover">
                    <?php if (!empty($n['image'])): ?>
                        <div class="p-img"><img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($n['image']) ?>" alt="" loading="lazy"></div>
                    <?php endif; ?>
                    <div class="p-body">
                        <h3 class="p-name line-clamp-2"><?= htmlspecialchars($n['title']) ?></h3>
                        <p class="p-meta mt-2">📅 <?= date('d M Y', strtotime($n['published_at'] ?: 'now')) ?></p>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

// ---------- ARCHIVE VIEW ----------
$pageTitle = 'News & Updates - QuickMed';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$totalNews = (int)$conn->query("SELECT COUNT(*) as t FROM news WHERE is_published = 1")->fetch_assoc()['t'];
$totalPages = max(1, (int)ceil($totalNews / $perPage));

$stmt = $conn->prepare("SELECT n.*, u.full_name as author_name FROM news n
                        LEFT JOIN users u ON n.author_id = u.id
                        WHERE n.is_published = 1
                        ORDER BY n.published_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$list = $stmt->get_result();

include 'includes/header.php';
qm_hero('News & Updates', 'Stay updated with QuickMed and the medical world.', 'QuickMed News', '📰');
?>

<section class="container mx-auto px-4 py-12 min-h-[40vh]">
    <?php if ($list->num_rows === 0): ?>
        <?php qm_empty('No news yet', 'Please check back later for updates.', '← Back Home', SITE_URL . '/index.php'); ?>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $delay = 0;
            while ($news = $list->fetch_assoc()):
                $linkUrl = !empty($news['source_link']) ? $news['source_link'] : SITE_URL . '/news.php?id=' . $news['id'];
                $target = !empty($news['source_link']) ? '_blank' : '_self';
            ?>
            <article class="product-card card-hover" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                <a href="<?= htmlspecialchars($linkUrl) ?>" target="<?= $target ?>" class="p-img relative block" style="height:12rem;padding:0;overflow:hidden">
                    <?php if (!empty($news['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($news['image']) ?>" alt="" loading="lazy" style="height:12rem;object-fit:cover;mix-blend-mode:normal">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-5xl bg-gray-100" style="height:12rem">📰</div>
                    <?php endif; ?>
                    <?php if (!empty($news['source_link'])): ?>
                        <span class="badge badge-lime absolute top-3 right-3">External ↗</span>
                    <?php endif; ?>
                </a>
                <div class="p-body">
                    <h3 class="p-name line-clamp-2">
                        <a href="<?= htmlspecialchars($linkUrl) ?>" target="<?= $target ?>"><?= htmlspecialchars($news['title']) ?></a>
                    </h3>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-3 flex-1"><?= htmlspecialchars(substr(strip_tags($news['content']), 0, 140)) ?>...</p>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-dashed border-gray-200">
                        <span class="text-xs text-gray-400 font-bold">📅 <?= date('d M Y', strtotime($news['published_at'] ?: $news['created_at'])) ?></span>
                        <a href="<?= htmlspecialchars($linkUrl) ?>" target="<?= $target ?>" class="btn btn-primary btn-sm">Read More →</a>
                    </div>
                </div>
            </article>
            <?php $delay += 100; endwhile; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
