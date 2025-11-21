<?php
/**
 * Customer Reviews & Testimonials - WITH PROFILE PICTURE
 */

// Fetch reviews with user details (including profile_image)
$reviewsQuery = "SELECT r.*, u.full_name, u.username, u.profile_image 
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 WHERE r.is_approved = 1
                 ORDER BY r.created_at DESC
                 LIMIT 6";
$reviewsResult = $conn->query($reviewsQuery);

if ($reviewsResult->num_rows > 0):
?>

<section class="bg-[#ecfccb] py-20 border-y-4 border-[#065f46] relative overflow-hidden">
    <!-- Background Decoration -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-10 left-10 text-9xl text-[#065f46]">❝</div>
        <div class="absolute bottom-10 right-10 text-9xl text-[#065f46]">❞</div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16" data-aos="fade-down">
            <h2 class="text-5xl font-bold text-[#065f46] mb-4 font-mono uppercase tracking-tighter">
                💬 Customer Voices
            </h2>
            <div class="bg-[#065f46] text-white inline-block px-8 py-3 text-xl font-bold transform rotate-2 shadow-[8px_8px_0px_#84cc16]">
                What Our Community Says
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php 
            $delay = 0;
            while ($review = $reviewsResult->fetch_assoc()): 
                // Profile Image Logic
                $profileImg = !empty($review['profile_image']) 
                    ? SITE_URL . '/uploads/profiles/' . $review['profile_image'] 
                    : 'https://ui-avatars.com/api/?name=' . urlencode($review['full_name']) . '&background=065f46&color=fff&size=128';
            ?>
                <div 
                    class="bg-white border-4 border-[#065f46] p-8 relative hover:translate-y-[-10px] transition-transform duration-300 shadow-[10px_10px_0px_#84cc16]"
                    data-aos="fade-up"
                    data-aos-delay="<?= $delay ?>"
                >
                    <!-- Quote Icon -->
                    <div class="absolute -top-6 -right-6 bg-[#84cc16] text-[#065f46] w-12 h-12 flex items-center justify-center text-3xl font-serif border-4 border-white rounded-full shadow-lg">
                        ”
                    </div>

                    <!-- User Info (Image + Name) -->
                    <div class="flex items-center gap-4 mb-6 border-b-2 border-gray-100 pb-4">
                        <img 
                            src="<?= $profileImg ?>" 
                            alt="<?= htmlspecialchars($review['full_name']) ?>" 
                            class="w-16 h-16 rounded-full object-cover border-4 border-[#065f46] p-1 bg-white"
                        >
                        <div>
                            <h3 class="font-bold text-lg text-[#065f46] leading-tight">
                                <?= htmlspecialchars($review['full_name']) ?>
                            </h3>
                            <p class="text-xs text-gray-500 font-mono">
                                <?= timeAgo($review['created_at']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Star Rating -->
                    <div class="flex gap-1 mb-4 text-2xl">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $review['rating']): ?>
                                <span class="text-yellow-400 drop-shadow-sm">★</span>
                            <?php else: ?>
                                <span class="text-gray-200">★</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Review Text -->
                    <p class="text-gray-700 italic leading-relaxed text-lg">
                        "<?= htmlspecialchars($review['review_text']) ?>"
                    </p>
                </div>
            <?php 
                $delay += 100;
            endwhile; 
            ?>
        </div>
        
        <!-- Overall Rating Badge -->
        <?php
        $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE is_approved = 1";
        $avgResult = $conn->query($avgQuery)->fetch_assoc();
        ?>
        
        <div class="text-center mt-16" data-aos="zoom-in">
            <div class="inline-flex items-center gap-4 bg-white border-4 border-[#065f46] px-8 py-4 shadow-[8px_8px_0px_rgba(0,0,0,0.2)]">
                <div class="text-5xl font-bold text-[#065f46]">
                    <?= number_format($avgResult['avg_rating'], 1) ?>
                </div>
                <div class="text-left">
                    <div class="text-yellow-400 text-2xl">★★★★★</div>
                    <p class="text-sm font-bold text-gray-600 uppercase tracking-wider">
                        Based on <?= $avgResult['total_reviews'] ?> Reviews
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>