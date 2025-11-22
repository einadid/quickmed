<?php
/**
 * Customer Reviews - Dual Marquee Animation (Updated Design)
 */

// 1. Fetch Reviews
// আমরা LIMIT একটু বাড়িয়ে দিচ্ছি যাতে এনিমেশন সুন্দর দেখায়
$reviewsQuery = "SELECT r.*, u.full_name, u.username, u.profile_image 
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 WHERE r.is_approved = 1
                 ORDER BY r.created_at DESC
                 LIMIT 10"; 
$reviewsResult = $conn->query($reviewsQuery);

$reviewsData = [];
if ($reviewsResult->num_rows > 0) {
    while($row = $reviewsResult->fetch_assoc()) {
        $reviewsData[] = $row;
    }
}

// ফাংশন: রিভিউ কার্ড জেনারেট করার জন্য (Code Reusability)
if (!function_exists('renderReviewCard')) {
    function renderReviewCard($review) {
        // Profile Image Logic
        $profileImg = !empty($review['profile_image']) 
            ? SITE_URL . '/uploads/profiles/' . $review['profile_image'] 
            : 'https://ui-avatars.com/api/?name=' . urlencode($review['full_name']) . '&background=065f46&color=fff&size=128';
        
        // Time Logic (Simple fallback if timeAgo function missing)
        $timeStr = function_exists('timeAgo') ? timeAgo($review['created_at']) : date('M d, Y', strtotime($review['created_at']));

        return '
        <div class="flex-shrink-0 w-[350px] mx-4">
            <div class="bg-white/90 backdrop-blur-sm border-2 border-[#065f46] p-6 rounded-2xl relative shadow-[6px_6px_0px_#84cc16] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px] transition-all duration-200 cursor-pointer group h-full flex flex-col justify-between">
                
                <div class="absolute -top-4 -right-4 bg-[#84cc16] text-[#065f46] w-10 h-10 flex items-center justify-center text-2xl font-serif border-2 border-white rounded-full shadow-sm group-hover:rotate-12 transition-transform">
                    ”
                </div>

                <div>
                    <div class="flex items-center gap-3 mb-4 border-b border-gray-100 pb-3">
                        <img src="'.$profileImg.'" alt="'.htmlspecialchars($review['full_name']).'" class="w-12 h-12 rounded-full object-cover border-2 border-[#065f46] p-0.5 bg-white">
                        <div>
                            <h3 class="font-bold text-[#065f46] text-base leading-none">'.htmlspecialchars($review['full_name']).'</h3>
                            <span class="text-xs text-gray-500 font-mono mt-1 block">'.$timeStr.'</span>
                        </div>
                    </div>

                    <div class="flex gap-0.5 mb-3 text-lg">
                        '.str_repeat('<span class="text-yellow-400 drop-shadow-sm">★</span>', $review['rating']).'
                        '.str_repeat('<span class="text-gray-200">★</span>', 5 - $review['rating']).'
                    </div>

                    <p class="text-gray-700 italic text-sm leading-relaxed line-clamp-3">
                        "'.htmlspecialchars($review['review_text']).'"
                    </p>
                </div>
                
                <div class="mt-4 pt-2 border-t border-dashed border-gray-200 flex items-center gap-1 text-[10px] font-bold text-[#065f46] uppercase tracking-wider">
                    <span class="text-[#84cc16]">✔</span> Verified Purchase
                </div>
            </div>
        </div>';
    }
}

if (!empty($reviewsData)):
?>

<style>
    /* Marquee Animations */
    @keyframes scrollLeft {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    @keyframes scrollRight {
        0% { transform: translateX(-50%); }
        100% { transform: translateX(0); }
    }

    .marquee-container {
        display: flex;
        overflow: hidden;
        width: 100%;
        mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    }

    .marquee-track {
        display: flex;
        width: max-content;
    }

    /* Animation Classes */
    .animate-scroll-left {
        animation: scrollLeft 40s linear infinite;
    }
    .animate-scroll-right {
        animation: scrollRight 40s linear infinite;
    }

    /* Pause on Hover */
    .marquee-container:hover .marquee-track {
        animation-play-state: paused;
    }
</style>

<section class="bg-[#ecfccb]/30 py-20 border-y-4 border-[#065f46] relative overflow-hidden">
    
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#065f46 1px, transparent 1px); background-size: 20px 20px;"></div>

    <div class="container mx-auto px-4 relative z-10 mb-12">
        <div class="text-center" data-aos="fade-down">
            <h2 class="text-4xl md:text-5xl font-bold text-[#065f46] mb-3 font-mono uppercase tracking-tighter">
                Community Love 💬
            </h2>
            <p class="text-gray-600 max-w-xl mx-auto">See what our customers are saying about their QuickMed experience.</p>
        </div>
    </div>

    <div class="marquee-container mb-8">
        <div class="marquee-track animate-scroll-left">
            <?php 
            // লুপ ২ বার চালানো হচ্ছে যাতে ইনফিনিট স্ক্রল স্মুথ হয় (Seamless Loop)
            foreach ($reviewsData as $review) { echo renderReviewCard($review); }
            foreach ($reviewsData as $review) { echo renderReviewCard($review); }
            ?>
        </div>
    </div>

    <div class="marquee-container">
        <div class="marquee-track animate-scroll-right">
            <?php 
            // ডাটা রিভার্স করে দেওয়া হলো যাতে দুটি রো ভিন্ন অর্ডারে দেখায়
            $reversedData = array_reverse($reviewsData);
            foreach ($reversedData as $review) { echo renderReviewCard($review); }
            foreach ($reversedData as $review) { echo renderReviewCard($review); }
            ?>
        </div>
    </div>

    <?php
    $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE is_approved = 1";
    $avgResult = $conn->query($avgQuery)->fetch_assoc();
    ?>
    <div class="container mx-auto px-4 mt-12 text-center relative z-10">
        <div class="inline-block bg-[#065f46] text-white px-6 py-2 rounded-full text-sm font-bold shadow-lg animate-bounce">
            ★ <?= number_format($avgResult['avg_rating'], 1) ?> Rating based on <?= $avgResult['total_reviews'] ?>+ Happy Customers
        </div>
    </div>

</section>

<?php endif; ?>