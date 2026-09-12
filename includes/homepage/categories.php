<?php
/**
 * Shop by Health Concerns - Compact & Responsive v3.0
 */
$categories = [
    ['name' => __('cat_heart'), 'icon' => '❤️', 'slug' => 'Heart', 'color' => 'bg-red-50 border-red-200 hover:border-red-400 hover:bg-red-100', 'iconBg' => 'bg-red-100'],
    ['name' => __('cat_diabetes'), 'icon' => '💉', 'slug' => 'Diabetes', 'color' => 'bg-blue-50 border-blue-200 hover:border-blue-400 hover:bg-blue-100', 'iconBg' => 'bg-blue-100'],
    ['name' => __('cat_baby_care'), 'icon' => '👶', 'slug' => 'Baby Care', 'color' => 'bg-pink-50 border-pink-200 hover:border-pink-400 hover:bg-pink-100', 'iconBg' => 'bg-pink-100'],
    ['name' => __('cat_skin'), 'icon' => '✨', 'slug' => 'Skin', 'color' => 'bg-purple-50 border-purple-200 hover:border-purple-400 hover:bg-purple-100', 'iconBg' => 'bg-purple-100'],
    ['name' => __('cat_orthopedic'), 'icon' => '🦴', 'slug' => 'Orthopedic', 'color' => 'bg-orange-50 border-orange-200 hover:border-orange-400 hover:bg-orange-100', 'iconBg' => 'bg-orange-100'],
    ['name' => __('cat_eye_ear'), 'icon' => '👁️', 'slug' => 'Eye & Ear', 'color' => 'bg-cyan-50 border-cyan-200 hover:border-cyan-400 hover:bg-cyan-100', 'iconBg' => 'bg-cyan-100'],
    ['name' => __('cat_dental'), 'icon' => '🦷', 'slug' => 'Dental', 'color' => 'bg-teal-50 border-teal-200 hover:border-teal-400 hover:bg-teal-100', 'iconBg' => 'bg-teal-100'],
    ['name' => __('cat_allergy'), 'icon' => '🤧', 'slug' => 'Allergy', 'color' => 'bg-yellow-50 border-yellow-200 hover:border-yellow-400 hover:bg-yellow-100', 'iconBg' => 'bg-yellow-100'],
];
?>

<section class="container mx-auto px-3 sm:px-4 py-8 sm:py-12">
    <?php qm_section_head(__('shop_by_concerns'), 'Find medicines for your specific health needs', 'Categories'); ?>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 sm:gap-3 md:gap-4">
        <?php foreach ($categories as $index => $category): ?>
            <a 
                href="<?= SITE_URL ?>/shop.php?category=<?= urlencode($category['slug']) ?>" 
                class="group relative bg-white border-[1.5px] <?= $category['color'] ?> rounded-[0.85rem] sm:rounded-[1rem] p-3 sm:p-4 text-center hover:scale-[1.02] hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                data-aos="fade-up"
                data-aos-delay="<?= $index * 40 ?>"
            >
                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 sm:mb-2.5 <?= $category['iconBg'] ?> rounded-[0.7rem] flex items-center justify-center text-[1.4rem] sm:text-[1.6rem] group-hover:scale-110 transition-transform duration-200 shadow-sm">
                    <?= $category['icon'] ?>
                </div>
                <h3 class="text-[0.75rem] sm:text-[0.82rem] font-bold text-gray-800 uppercase tracking-wide leading-tight">
                    <?= $category['name'] ?>
                </h3>
                <span class="absolute top-2 right-2 w-1.5 h-1.5 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mt-6 sm:mt-8 text-center">
        <a href="<?= SITE_URL ?>/shop.php" class="inline-flex items-center gap-1.5 text-[0.78rem] sm:text-[0.85rem] font-bold text-deep-green hover:text-lime-600 transition">
            View all categories <span>→</span>
        </a>
    </div>
</section>
