<?php
/**
 * Featured Products Section
 */

// Get featured medicines (popular/in-stock)
$featuredQuery = "SELECT m.*, 
                  sm.price, sm.stock_quantity, sm.shop_id,
                  s.name as shop_name, s.city,
                  COUNT(oi.id) as order_count
                  FROM medicines m
                  JOIN shop_medicines sm ON m.id = sm.medicine_id
                  JOIN shops s ON sm.shop_id = s.id
                  LEFT JOIN order_items oi ON m.id = oi.medicine_id
                  WHERE sm.stock_quantity > 0
                  GROUP BY m.id, sm.id
                  ORDER BY order_count DESC, m.created_at DESC
                  LIMIT 8";
$featuredResult = $conn->query($featuredQuery);

if ($featuredResult->num_rows > 0):
?>

<section class="container mx-auto px-4 py-16">
    
    <!-- Heading -->
    <div class="text-center mb-12" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-green mb-4 uppercase">
            ⭐ <?= __('featured_products') ?> ⭐
        </h2>

        <div class="bg-lime-accent inline-block px-4 md:px-6 py-2 border-4 border-green">
            <p class="text-green font-bold text-sm md:text-base">
                Most Popular & Trusted Medicines
            </p>
        </div>
    </div>

    <!-- PRODUCTS GRID -->
    <!-- Mobile: grid-cols-2 | Desktop: grid-cols-4 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <?php 
        $delay = 0;
        while ($product = $featuredResult->fetch_assoc()): 
        ?>
            <div 
                class="card bg-white hover:shadow-retro-lg transition-all p-2 md:p-4"
                data-aos="zoom-in"
                data-aos-delay="<?= $delay ?>"
            >
                <!-- IMAGE -->
                <div class="bg-off-white p-2 md:p-4 mb-2 md:mb-4 border-2 border-deep-green">
                    <img 
                        src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="w-full h-28 md:h-40 object-contain"
                        loading="lazy"
                    >
                </div>

                <!-- NAME -->
                <h3 class="text-xs md:text-lg font-bold text-deep-green mb-1 md:mb-2 uppercase leading-tight">
                    <?= htmlspecialchars($product['name']) ?>
                </h3>

                <!-- Generic (hidden on mobile) -->
                <p class="text-xs md:text-sm text-gray-600 mb-1 hidden md:block">
                    <strong>Generic:</strong> <?= htmlspecialchars($product['generic_name']) ?>
                </p>

                <!-- Power -->
                <p class="text-xs md:text-sm text-gray-600 mb-1">
                    <strong>Power:</strong> <?= htmlspecialchars($product['power']) ?>
                </p>

                <!-- City -->
                <p class="text-xs md:text-sm text-gray-600 mb-1 md:mb-3">
                    📍 <?= htmlspecialchars($product['city']) ?>
                </p>

                <!-- STOCK BADGES -->
                <?php if ($product['stock_quantity'] > 50): ?>
                    <span class="badge badge-success mb-1 md:mb-3 text-xs">✅ In Stock</span>
                <?php elseif ($product['stock_quantity'] > 0): ?>
                    <span class="badge badge-warning mb-1 md:mb-3 text-xs">⚠️ Low Stock</span>
                <?php else: ?>
                    <span class="badge badge-danger mb-1 md:mb-3 text-xs">❌ Out</span>
                <?php endif; ?>

                <!-- PRICE -->
                <div class="mb-1 md:mb-4">
                    <span class="text-lg md:text-3xl font-bold text-deep-green">
                        ৳<?= number_format($product['price'], 2) ?>
                    </span>
                    <span class="text-xs md:text-sm text-gray-500">
                        /<?= htmlspecialchars($product['form']) ?>
                    </span>
                </div>

                <!-- RX REQUIRED -->
                <?php if ($product['requires_prescription']): ?>
                    <div class="bg-yellow-100 border-2 border-yellow-500 text-yellow-800 
                                text-[10px] md:text-xs px-2 py-1 mb-1 md:mb-3 text-center font-bold">
                        ⚠️ Rx Required
                    </div>
                <?php endif; ?>

                <!-- ACTION BUTTONS -->
                <div class="flex gap-2">
                    <button 
                        onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, 1)"
                        class="btn btn-primary flex-1 text-[10px] md:text-sm py-1.5 md:py-2"
                        <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                    >
                        🛒 Add
                    </button>

                    <!-- View button hidden on mobile -->
                    <a 
                        href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" 
                        class="btn btn-outline flex-1 text-xs md:text-sm py-2 hidden md:block"
                    >
                        👁️ View
                    </a>
                </div>
            </div>

        <?php 
        $delay += 50;
        endwhile; 
        ?>
    </div>

    <!-- View All -->
    <div class="text-center mt-8" data-aos="fade-up">
        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-lime btn-lg">
            View All Medicines →
        </a>
    </div>

</section>

<?php endif; ?>
