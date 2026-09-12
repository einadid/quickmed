<?php
/**
 * Flash Sale / Deals Section
 */

// Get active flash sales
$flashQuery = "SELECT fs.*, m.name as medicine_name, m.image, m.power, s.name as shop_name, s.city
               FROM flash_sales fs
               JOIN medicines m ON fs.medicine_id = m.id
               JOIN shops s ON fs.shop_id = s.id
               WHERE fs.is_active = 1 
               AND fs.expires_at > NOW()
               AND fs.sold_count < fs.stock_limit
               ORDER BY fs.discount_percent DESC
               LIMIT 6";
$flashResult = $conn->query($flashQuery);

if ($flashResult->num_rows > 0):
?>

<section class="bg-[#84cc16] py-16 border-y-4 border-[#065f46]">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-block bg-[#065f46] text-white px-8 py-4 rounded-2xl shadow-xl mb-4">
                <h2 class="text-3xl md:text-4xl font-bold uppercase font-display">⚡ <?= __('flash_sale') ?> ⚡</h2>
            </div>
            <p class="text-[#04402f] text-xl font-bold">Limited Time Offers — Grab Them Fast!</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php 
            $delay = 0;
            while ($sale = $flashResult->fetch_assoc()): 
                $timeLeft = strtotime($sale['expires_at']) - time();
                $remaining = $sale['stock_limit'] - $sale['sold_count'];
            ?>
                <div class="card relative overflow-hidden card-hover" data-aos="flip-left" data-aos-delay="<?= $delay ?>">
                    <div class="absolute top-4 right-4 z-10">
                        <span class="badge badge-green" style="font-size:0.9rem;padding:0.5rem 1rem">🔥 <?= round($sale['discount_percent']) ?>% OFF</span>
                    </div>

                    <a href="<?= SITE_URL ?>/product.php?id=<?= $sale['medicine_id'] ?>" class="block bg-gray-50 rounded-xl p-4 mb-4 border border-gray-200">
                        <img
                            src="<?= SITE_URL ?>/uploads/medicines/<?= $sale['image'] ?? 'placeholder.png' ?>"
                            alt="<?= htmlspecialchars($sale['medicine_name']) ?>"
                            class="w-full h-44 object-contain"
                            loading="lazy"
                            onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                        >
                    </a>

                    <h3 class="text-xl font-bold text-[#065f46] mb-1">
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $sale['medicine_id'] ?>" class="hover:underline"><?= htmlspecialchars($sale['medicine_name']) ?></a>
                    </h3>

                    <p class="text-sm text-gray-500 mb-3">
                        <?= htmlspecialchars($sale['power'] ?? '') ?> | 📍 <?= htmlspecialchars($sale['city'] ?? '') ?>
                    </p>

                    <div class="mb-4">
                        <span class="text-gray-400 line-through text-lg"><?= qm_money($sale['original_price']) ?></span>
                        <span class="text-3xl font-bold text-[#065f46] ml-2 font-display"><?= qm_money($sale['sale_price']) ?></span>
                    </div>

                    <?php $pct = $sale['stock_limit'] > 0 ? max(0, min(100, ($remaining / $sale['stock_limit']) * 100)) : 0; ?>
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-bold">Stock: <?= max(0, $remaining) ?> left</span>
                            <span><?= round($pct) ?>%</span>
                        </div>
                        <div class="bg-gray-200 h-3 rounded-full overflow-hidden">
                            <div class="bg-[#84cc16] h-full transition-all rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>

                    <div class="bg-[#065f46] text-white text-center py-2.5 mb-4 font-display font-bold rounded-xl">
                        <span class="countdown" data-expires="<?= $sale['expires_at'] ?>">⏰ Calculating...</span>
                    </div>

                    <button
                        onclick="addToCart(<?= $sale['medicine_id'] ?>, <?= $sale['shop_id'] ?>, 1)"
                        class="btn btn-primary btn-block"
                    >
                        🛒 <?= __('add_to_cart') ?>
                    </button>
                </div>
            <?php 
                $delay += 100;
            endwhile; 
            ?>
        </div>
    </div>
</section>

<script>
// Countdown Timer for Flash Sales
function updateCountdowns() {
    document.querySelectorAll('.countdown').forEach(element => {
        const expiresAt = new Date(element.dataset.expires).getTime();
        const now = new Date().getTime();
        const distance = expiresAt - now;
        
        if (distance < 0) {
            element.textContent = '⏰ EXPIRED';
            element.parentElement.classList.add('bg-gray-500');
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        element.textContent = `⏰ ${days}d ${hours}h ${minutes}m ${seconds}s`;
    });
}

// Update every second
setInterval(updateCountdowns, 1000);
updateCountdowns();
</script>

<?php endif; ?>