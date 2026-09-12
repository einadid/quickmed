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
    <?php qm_section_head('⭐ ' . __('featured_products') . ' ⭐', 'Most Popular & Trusted Medicines', 'Handpicked'); ?>

    <!-- PRODUCTS GRID -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <?php
        $delay = 0;
        while ($product = $featuredResult->fetch_assoc()):
        ?>
            <div class="product-card" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
                <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="p-img block">
                    <img
                        src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        loading="lazy"
                        onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'"
                    >
                </a>
                <div class="p-body">
                    <h3 class="p-name truncate">
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="hover:underline"><?= htmlspecialchars($product['name']) ?></a>
                    </h3>
                    <p class="p-meta truncate hidden md:block"><?= htmlspecialchars($product['generic_name'] ?? '') ?></p>
                    <p class="p-meta truncate"><?= htmlspecialchars($product['power'] ?? '') ?> · 📍 <?= htmlspecialchars($product['city'] ?? '') ?></p>

                    <div class="mt-2 mb-1">
                        <?php if ($product['stock_quantity'] > 50): ?>
                            <span class="badge badge-success">✅ In Stock</span>
                        <?php elseif ($product['stock_quantity'] > 0): ?>
                            <span class="badge badge-warning">⚠️ Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Out</span>
                        <?php endif; ?>
                        <?php if ($product['requires_prescription']): ?>
                            <span class="badge badge-warning mt-1">⚠️ Rx</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <span class="p-price"><?= qm_money($product['price']) ?></span>
                        <span class="text-xs text-gray-400">/<?= htmlspecialchars($product['form'] ?? '') ?></span>
                    </div>

                    <div class="flex gap-2 mt-auto">
                        <button
                            onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, 1)"
                            class="btn btn-primary btn-sm flex-1"
                            <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                        >
                            🛒 Add
                        </button>
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="btn btn-outline btn-sm flex-1 hidden md:inline-flex">
                            👁️ View
                        </a>
                    </div>
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
