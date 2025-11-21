<?php
/**
 * QuickMed - Homepage
 * Main landing page with all dynamic sections
 */

require_once 'config.php';

$pageTitle = 'QuickMed - Your Trusted Online Pharmacy | আপনার বিশ্বস্ত অনলাইন ফার্মেসি';
$pageDescription = 'Buy genuine medicines online with home delivery across Bangladesh. Best prices, fast delivery, 100% authentic products.';

// Include header
include 'includes/header.php';
?>

<!-- Hero Section with Search -->
<?php include 'includes/homepage/hero.php'; ?>

<!-- Shop by Categories -->
<?php include 'includes/homepage/categories.php'; ?>

<!-- Flash Sale -->
<?php include 'includes/homepage/flash_sale.php'; ?>

<!-- Featured Products -->
<?php include 'includes/homepage/featured.php'; ?>

<!-- Stats Counter -->
<?php include 'includes/homepage/stats.php'; ?>

<!-- Customer Reviews -->
<?php include 'includes/homepage/testimonials.php'; ?>

<!-- Health Blog -->
<?php include 'includes/homepage/health_blog.php'; ?>

<!-- Latest News -->
<?php include 'includes/homepage/news.php'; ?>

<!-- Trust & Security Section -->
<section class="bg-light-green py-16 border-t-4 border-green">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center" data-aos="zoom-in" data-aos-delay="0">
                <div class="text-6xl mb-4">✅</div>
                <h3 class="text-xl font-bold text-green mb-2 uppercase">100% Genuine</h3>
                <p class="text-gray-700">All medicines sourced from authorized manufacturers</p>
            </div>
            
            <div class="text-center" data-aos="zoom-in" data-aos-delay="100">
                <div class="text-6xl mb-4">🚚</div>
                <h3 class="text-xl font-bold text-green mb-2 uppercase">Fast Delivery</h3>
                <p class="text-gray-700">Home delivery within 24-48 hours across Bangladesh</p>
            </div>
            
            <div class="text-center" data-aos="zoom-in" data-aos-delay="200">
                <div class="text-6xl mb-4">🔒</div>
                <h3 class="text-xl font-bold text-green mb-2 uppercase">Secure Payment</h3>
                <p class="text-gray-700">Safe and secure cash on delivery option</p>
            </div>
            
            <div class="text-center" data-aos="zoom-in" data-aos-delay="300">
                <div class="text-6xl mb-4">💰</div>
                <h3 class="text-xl font-bold text-green mb-2 uppercase">Best Prices</h3>
                <p class="text-gray-700">Competitive pricing with regular discounts</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-green text-white py-16 text-center border-y-4 border-lime-accent">
    <div class="container mx-auto px-4" data-aos="fade-up">
        <h2 class="text-4xl font-bold mb-4 uppercase">
            Ready to Order Your Medicines?
        </h2>
        <p class="text-xl mb-8">
            Join thousands of satisfied customers across Bangladesh
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-lime btn-lg">
                🛍️ Start Shopping
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/signup.php" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                    ✍️ Sign Up Now
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>