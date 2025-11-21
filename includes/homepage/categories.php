<?php
/**
 * Shop by Health Concerns
 */

$categories = [
    ['name' => __('cat_heart'), 'icon' => '❤️', 'slug' => 'Heart', 'color' => 'bg-red-100 border-red-500'],
    ['name' => __('cat_diabetes'), 'icon' => '💉', 'slug' => 'Diabetes', 'color' => 'bg-blue-100 border-blue-500'],
    ['name' => __('cat_baby_care'), 'icon' => '👶', 'slug' => 'Baby Care', 'color' => 'bg-pink-100 border-pink-500'],
    ['name' => __('cat_skin'), 'icon' => '✨', 'slug' => 'Skin', 'color' => 'bg-purple-100 border-purple-500'],
    ['name' => __('cat_orthopedic'), 'icon' => '🦴', 'slug' => 'Orthopedic', 'color' => 'bg-orange-100 border-orange-500'],
    ['name' => __('cat_eye_ear'), 'icon' => '👁️', 'slug' => 'Eye & Ear', 'color' => 'bg-cyan-100 border-cyan-500'],
    ['name' => __('cat_dental'), 'icon' => '🦷', 'slug' => 'Dental', 'color' => 'bg-teal-100 border-teal-500'],
    ['name' => __('cat_allergy'), 'icon' => '🤧', 'slug' => 'Allergy', 'color' => 'bg-yellow-100 border-yellow-500'],
];
?>

<section class="container mx-auto px-4 py-16">
    <div class="text-center mb-12" data-aos="fade-up">
        <h2 class="text-4xl font-bold text-green mb-4 uppercase">
            <?= __('shop_by_concerns') ?>
        </h2>
        <div class="bg-lime-accent inline-block px-6 py-2 border-4 border-green">
            <p class="text-green font-bold">Find medicines for your specific health needs</p>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($categories as $index => $category): ?>
            <a 
                href="<?= SITE_URL ?>/shop.php?category=<?= urlencode($category['slug']) ?>" 
                class="card <?= $category['color'] ?> hover:scale-105 transition-transform text-center group"
                data-aos="fade-up"
                data-aos-delay="<?= $index * 50 ?>"
            >
                <div class="text-6xl mb-4 group-hover:scale-110 transition-transform">
                    <?= $category['icon'] ?>
                </div>
                <h3 class="text-xl font-bold text-gray-800 uppercase">
                    <?= $category['name'] ?>
                </h3>
            </a>
        <?php endforeach; ?>
    </div>
</section>