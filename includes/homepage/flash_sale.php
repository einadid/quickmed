<?php
/**
 * Flash Sale / Deals Section - Compact Responsive v3.0
 */
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

<section class="bg-[#84cc16] py-8 sm:py-12 border-y-[3px] border-[#065f46] relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(#065f46 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="container mx-auto px-3 sm:px-4 relative z-10">
        <div class="text-center mb-6 sm:mb-8" data-aos="fade-up">
            <div class="inline-block bg-[#065f46] text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl shadow-md mb-2 sm:mb-3">
                <h2 class="text-[1.1rem] sm:text-xl md:text-2xl font-bold uppercase font-display tracking-wide">⚡ <?= __('flash_sale') ?> ⚡</h2>
            </div>
            <p class="text-[#04402f] text-[0.82rem] sm:text-sm font-bold">Limited Time Offers — Grab Them Fast!</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <?php 
            $delay = 0;
            while ($sale = $flashResult->fetch_assoc()): 
                $timeLeft = strtotime($sale['expires_at']) - time();
                $remaining = $sale['stock_limit'] - $sale['sold_count'];
            ?>
                <div class="card relative overflow-hidden card-hover p-3 sm:p-4 rounded-[0.9rem]" data-aos="flip-left" data-aos-delay="<?= $delay ?>">
                    <div class="absolute top-2.5 right-2.5 z-10">
                        <span class="badge badge-green text-[0.7rem] px-2 py-1 shadow-sm">🔥 <?= round($sale['discount_percent']) ?>% OFF</span>
                    </div>

                    <a href="<?= SITE_URL ?>/product.php?id=<?= $sale['medicine_id'] ?>" class="block bg-gray-50 rounded-xl p-3 mb-3 border border-gray-200 hover:border-lime-300 transition">
                        <img
                            src="<?= SITE_URL ?>/uploads/medicines/<?= $sale['image'] ?? 'placeholder.png' ?>"
                            alt="<?= htmlspecialchars($sale['medicine_name']) ?>"
                            class="w-full h-28 sm:h-32 object-contain"
                            loading="lazy"
                            onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                        >
                    </a>

                    <h3 class="text-[0.95rem] sm:text-[1.05rem] font-bold text-[#065f46] mb-1 truncate">
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $sale['medicine_id'] ?>" class="hover:underline"><?= htmlspecialchars($sale['medicine_name']) ?></a>
                    </h3>

                    <p class="text-[11px] sm:text-xs text-gray-500 mb-2.5 truncate">
                        <?= htmlspecialchars($sale['power'] ?? '') ?> | 📍 <?= htmlspecialchars($sale['city'] ?? '') ?>
                    </p>

                    <div class="mb-3 flex items-baseline gap-2">
                        <span class="text-gray-400 line-through text-[0.8rem] sm:text-sm"><?= qm_money($sale['original_price']) ?></span>
                        <span class="text-[1.25rem] sm:text-[1.4rem] font-bold text-[#065f46] font-display"><?= qm_money($sale['sale_price']) ?></span>
                    </div>

                    <?php $pct = $sale['stock_limit'] > 0 ? max(0, min(100, ($remaining / $sale['stock_limit']) * 100)) : 0; ?>
                    <div class="mb-3">
                        <div class="flex justify-between text-[11px] mb-1 font-medium">
                            <span><?= max(0, $remaining) ?> left</span>
                            <span><?= round($pct) ?>%</span>
                        </div>
                        <div class="bg-gray-200 h-2 rounded-full overflow-hidden">
                            <div class="bg-[#84cc16] h-full transition-all rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>

                    <div class="bg-[#065f46] text-white text-center py-2 mb-3 font-display font-bold rounded-lg text-[0.78rem] sm:text-[0.82rem]">
                        <span class="countdown" data-expires="<?= $sale['expires_at'] ?>">⏰ Calculating...</span>
                    </div>

                    <button
                        onclick="addToCart(<?= $sale['medicine_id'] ?>, <?= $sale['shop_id'] ?>, 1)"
                        class="btn btn-primary btn-block btn-sm text-[0.8rem] py-2.5"
                    >
                        🛒 <?= __('add_to_cart') ?>
                    </button>
                </div>
            <?php 
                $delay += 60;
            endwhile; 
            ?>
        </div>
    </div>
</section>

<script>
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
setInterval(updateCountdowns, 1000);
updateCountdowns();
</script>

<?php endif; ?>
