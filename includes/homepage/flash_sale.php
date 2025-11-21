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

<section class="bg-lime-accent py-16 border-y-4 border-green">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-block bg-green text-white px-8 py-4 border-4 border-white mb-4">
                <h2 class="text-4xl font-bold uppercase">⚡ <?= __('flash_sale') ?> ⚡</h2>
            </div>
            <p class="text-green text-xl font-bold">Limited Time Offers - Grab Them Fast!</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php 
            $delay = 0;
            while ($sale = $flashResult->fetch_assoc()): 
                $timeLeft = strtotime($sale['expires_at']) - time();
                $remaining = $sale['stock_limit'] - $sale['sold_count'];
            ?>
                <div 
                    class="card card-lime bg-white relative overflow-hidden"
                    data-aos="flip-left"
                    data-aos-delay="<?= $delay ?>"
                >
                    <!-- Discount Badge -->
                    <div class="absolute top-4 right-4 bg-green text-white px-4 py-2 border-2 border-white z-10">
                        <span class="text-2xl font-bold"><?= round($sale['discount_percent']) ?>% OFF</span>
                    </div>
                    
                    <!-- Medicine Image -->
                    <div class="bg-gray-100 p-4 mb-4 border-2 border-gray-300">
                        <img 
                            src="<?= SITE_URL ?>/uploads/medicines/<?= $sale['image'] ?? 'placeholder.png' ?>" 
                            alt="<?= htmlspecialchars($sale['medicine_name']) ?>"
                            class="w-full h-48 object-contain"
                        >
                    </div>
                    
                    <h3 class="text-xl font-bold text-green mb-2">
                        <?= htmlspecialchars($sale['medicine_name']) ?>
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-3">
                        <?= htmlspecialchars($sale['power']) ?> | 📍 <?= htmlspecialchars($sale['city']) ?>
                    </p>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <span class="text-gray-500 line-through text-lg">৳<?= number_format($sale['original_price'], 2) ?></span>
                        <span class="text-3xl font-bold text-green ml-2">৳<?= number_format($sale['sale_price'], 2) ?></span>
                    </div>
                    
                    <!-- Stock Progress -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-bold">Stock: <?= $remaining ?> left</span>
                            <span><?= round(($remaining / $sale['stock_limit']) * 100) ?>%</span>
                        </div>
                        <div class="bg-gray-300 h-3 border-2 border-green">
                            <div 
                                class="bg-green h-full transition-all" 
                                style="width: <?= ($remaining / $sale['stock_limit']) * 100 ?>%"
                            ></div>
                        </div>
                    </div>
                    
                    <!-- Countdown Timer -->
                    <div class="bg-green text-white text-center py-2 mb-4 font-mono font-bold border-2 border-gray-800">
                        <span class="countdown" data-expires="<?= $sale['expires_at'] ?>">
                            ⏰ Calculating...
                        </span>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <button 
                        onclick="addToCart(<?= $sale['medicine_id'] ?>, <?= $sale['shop_id'] ?>, 1)"
                        class="btn btn-lime w-full"
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