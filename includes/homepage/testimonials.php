<?php
/**
 * Customer Reviews - Compact Responsive v3.0
 */
$reviewsQuery = "SELECT r.*, u.full_name, u.username, u.profile_image 
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 WHERE r.is_approved = 1
                 ORDER BY r.created_at DESC
                 LIMIT 10"; 
$reviewsResult = $conn->query($reviewsQuery);
$reviewsData = [];
if ($reviewsResult && $reviewsResult->num_rows > 0) {
    while($row = $reviewsResult->fetch_assoc()) { $reviewsData[] = $row; }
}
if (!function_exists('renderReviewCard')) {
    function renderReviewCard($review) {
        $profileImg = !empty($review['profile_image']) 
            ? SITE_URL . '/uploads/profiles/' . $review['profile_image'] 
            : 'https://ui-avatars.com/api/?name=' . urlencode($review['full_name']) . '&background=065f46&color=fff&size=128';
        $timeStr = function_exists('timeAgo') ? timeAgo($review['created_at']) : date('M d, Y', strtotime($review['created_at']));
        return '
        <div class="flex-shrink-0 w-[260px] sm:w-[300px] mx-2 sm:mx-3">
            <div class="bg-white/95 backdrop-blur-sm border-[1.5px] border-[#065f46] p-4 sm:p-5 rounded-xl relative shadow-[3px_3px_0px_#84cc16] hover:shadow-none hover:translate-x-[1px] hover:translate-y-[1px] transition-all duration-200 cursor-pointer group h-full flex flex-col justify-between">
                <div class="absolute -top-3 -right-3 bg-[#84cc16] text-[#065f46] w-7 h-7 flex items-center justify-center text-lg font-serif border-2 border-white rounded-full shadow-sm group-hover:rotate-12 transition-transform">”</div>
                <div>
                    <div class="flex items-center gap-2.5 mb-3 border-b border-gray-100 pb-2.5">
                        <img src="'.$profileImg.'" alt="'.htmlspecialchars($review['full_name']).'" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-[#065f46] p-0.5 bg-white flex-shrink-0">
                        <div class="min-w-0">
                            <h3 class="font-bold text-[#065f46] text-[0.82rem] sm:text-sm leading-none truncate">'.htmlspecialchars($review['full_name']).'</h3>
                            <span class="text-[10px] text-gray-500 font-mono mt-0.5 block">'.$timeStr.'</span>
                        </div>
                    </div>
                    <div class="flex gap-0.5 mb-2 text-[0.9rem]">'.str_repeat('<span class="text-yellow-400 drop-shadow-sm">★</span>', $review['rating']).str_repeat('<span class="text-gray-200">★</span>', 5 - $review['rating']).'</div>
                    <p class="text-gray-700 italic text-[0.75rem] sm:text-xs leading-relaxed line-clamp-3">"'.htmlspecialchars($review['review_text']).'"</p>
                </div>
                <div class="mt-3 pt-2 border-t border-dashed border-gray-200 flex items-center gap-1 text-[9px] font-bold text-[#065f46] uppercase tracking-wider"><span class="text-[#84cc16]">✔</span> Verified Purchase</div>
            </div>
        </div>';
    }
}
if (!empty($reviewsData)):
?>

<style>
    @keyframes scrollLeft { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    @keyframes scrollRight { 0% { transform: translateX(-50%); } 100% { transform: translateX(0); } }
    .rv-marquee { display: flex; overflow: hidden; width: 100%; mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent); }
    .rv-track { display: flex; width: max-content; }
    .rv-left { animation: scrollLeft 45s linear infinite; }
    .rv-right { animation: scrollRight 45s linear infinite; }
    .rv-marquee:hover .rv-track { animation-play-state: paused; }
    @media (max-width: 640px) {
        .rv-left, .rv-right { animation-duration: 30s; }
    }
</style>

<section class="bg-[#ecfccb]/40 py-8 sm:py-12 border-y-[3px] border-[#065f46] relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.025] pointer-events-none" style="background-image: radial-gradient(#065f46 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="container mx-auto px-3 sm:px-4 relative z-10 mb-6 sm:mb-8">
        <div class="text-center" data-aos="fade-down">
            <h2 class="text-[1.3rem] sm:text-2xl md:text-3xl font-bold text-[#065f46] mb-1.5 font-mono uppercase tracking-tight">Community Love 💬</h2>
            <p class="text-gray-600 max-w-xl mx-auto text-[0.78rem] sm:text-[0.85rem]">See what our customers are saying about QuickMed.</p>
        </div>
    </div>

    <div class="rv-marquee mb-4 sm:mb-6">
        <div class="rv-track rv-left">
            <?php foreach ($reviewsData as $review) { echo renderReviewCard($review); } foreach ($reviewsData as $review) { echo renderReviewCard($review); } ?>
        </div>
    </div>

    <div class="rv-marquee">
        <div class="rv-track rv-right">
            <?php $reversedData = array_reverse($reviewsData); foreach ($reversedData as $review) { echo renderReviewCard($review); } foreach ($reversedData as $review) { echo renderReviewCard($review); } ?>
        </div>
    </div>

    <?php
    $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE is_approved = 1";
    $avgResult = $conn->query($avgQuery)->fetch_assoc();
    ?>
    <div class="container mx-auto px-3 sm:px-4 mt-6 sm:mt-8 text-center relative z-10">
        <div class="inline-block bg-[#065f46] text-white px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-bold shadow-md animate-bounce">
            ★ <?= number_format($avgResult['avg_rating'] ?? 5, 1) ?> Rating based on <?= $avgResult['total_reviews'] ?? 0 ?>+ Happy Customers
        </div>
    </div>
</section>

<?php endif; ?>
