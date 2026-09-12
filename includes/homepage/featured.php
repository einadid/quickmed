<?php
/**
 * Featured Products Section - Compact Responsive v3.0
 */
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

<section class="container mx-auto px-3 sm:px-4 py-8 sm:py-12">
    <?php qm_section_head('⭐ ' . __('featured_products') . ' ⭐', 'Most Popular & Trusted Medicines', 'Handpicked'); ?>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3 md:gap-4">
        <?php
        $delay = 0;
        while ($product = $featuredResult->fetch_assoc()):
        ?>
            <div class="product-card group" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
                <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="p-img block relative">
                    <img
                        src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        loading="lazy"
                        onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                    >
                    <?php if ($product['stock_quantity'] <= 10): ?>
                        <span class="absolute top-2 left-2 text-[9px] bg-orange-500 text-white px-1.5 py-0.5 rounded-full font-bold">Low Stock</span>
                    <?php endif; ?>
                </a>
                <div class="p-body">
                    <h3 class="p-name">
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="hover:text-lime-600 transition"><?= htmlspecialchars($product['name']) ?></a>
                    </h3>
                    <p class="p-meta truncate hidden sm:block"><?= htmlspecialchars($product['generic_name'] ?? '') ?></p>
                    <p class="p-meta truncate flex items-center gap-1"><span class="hidden sm:inline"><?= htmlspecialchars($product['power'] ?? '') ?> ·</span> 📍 <?= htmlspecialchars($product['city'] ?? '') ?></p>

                    <div class="mt-1.5 mb-1.5 flex items-center gap-1 flex-wrap">
                        <?php if ($product['stock_quantity'] > 20): ?>
                            <span class="badge badge-success text-[9px] px-1.5 py-0.5">In Stock</span>
                        <?php elseif ($product['stock_quantity'] > 0): ?>
                            <span class="badge badge-warning text-[9px] px-1.5 py-0.5">Few left</span>
                        <?php else: ?>
                            <span class="badge badge-danger text-[9px] px-1.5 py-0.5">Out</span>
                        <?php endif; ?>
                        <?php if ($product['requires_prescription']): ?>
                            <span class="badge badge-warning text-[8px] px-1 py-0.5">Rx</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2 flex items-baseline gap-1">
                        <span class="p-price"><?= qm_money($product['price']) ?></span>
                        <span class="text-[10px] text-gray-400 hidden sm:inline">/<?= htmlspecialchars($product['form'] ?? '') ?></span>
                    </div>

                    <div class="flex gap-1.5 mt-auto">
                        <button
                            onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, 1)"
                            class="btn btn-primary btn-sm flex-1 text-[0.68rem] sm:text-[0.72rem] py-1.5 sm:py-2"
                            <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                        >
                            🛒 Add
                        </button>
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="btn btn-outline btn-sm hidden sm:inline-flex text-[0.70rem] px-2">
                            👁️
                        </a>
                    </div>
                </div>
            </div>
        <?php
        $delay += 40;
        endwhile;
        ?>
    </div>

    <div class="text-center mt-6 sm:mt-8" data-aos="fade-up">
        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-lime btn-lg text-[0.85rem] sm:text-[0.9rem] px-5 sm:px-6 py-2.5 sm:py-3">
            View All Medicines →
        </a>
    </div>
</section>

<?php endif; ?>
