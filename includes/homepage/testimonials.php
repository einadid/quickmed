<?php
/**
 * Customer Reviews & Testimonials
 */

$reviewsQuery = "SELECT r.*, u.full_name, u.username
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 WHERE r.is_approved = 1
                 ORDER BY r.created_at DESC
                 LIMIT 6";
$reviewsResult = $conn->query($reviewsQuery);

if ($reviewsResult->num_rows > 0):
?>

<section class="bg-light-green py-16 border-y-4 border-green">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl font-bold text-green mb-4 uppercase">
                💬 <?= __('customer_reviews') ?> 💬
            </h2>
            <div class="bg-green text-white inline-block px-6 py-2 border-4 border-white">
                <p class="font-bold">What Our Customers Say About Us</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php 
            $delay = 0;
            while ($review = $reviewsResult->fetch_assoc()): 
            ?>
                <div 
                    class="card bg-white"
                    data-aos="fade-up"
                    data-aos-delay="<?= $delay ?>"
                >
                    <!-- Star Rating -->
                    <div class="flex items-center gap-2 mb-4">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $review['rating']): ?>
                                <span class="text-2xl text-yellow-500">⭐</span>
                            <?php else: ?>
                                <span class="text-2xl text-gray-300">☆</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Review Text -->
                    <p class="text-gray-700 mb-4 italic leading-relaxed">
                        "<?= htmlspecialchars($review['review_text']) ?>"
                    </p>
                    
                    <!-- Customer Info -->
                    <div class="border-t-2 border-green pt-4">
                        <p class="font-bold text-green text-lg">
                            <?= htmlspecialchars($review['full_name']) ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?= timeAgo($review['created_at']) ?>
                        </p>
                    </div>
                </div>
            <?php 
                $delay += 100;
            endwhile; 
            ?>
        </div>
        
        <!-- Overall Rating -->
        <?php
        $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                     FROM reviews WHERE is_approved = 1";
        $avgResult = $conn->query($avgQuery)->fetch_assoc();
        ?>
        
        <div class="text-center mt-12 bg-white border-4 border-green inline-block px-12 py-6 shadow-retro-lg" data-aos="zoom-in">
            <div class="text-5xl font-bold text-green mb-2">
                <?= number_format($avgResult['avg_rating'], 1) ?> ⭐
            </div>
            <p class="text-gray-600 font-bold">
                Based on <?= $avgResult['total_reviews'] ?> Reviews
            </p>
        </div>
    </div>
</section>

<?php endif; ?>