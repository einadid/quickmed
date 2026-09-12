<?php
/**
 * Shop - Products Listing Page (Responsive Mobile Update)
 */

require_once 'config.php';

$pageTitle = 'Shop Medicines - QuickMed';

// Get filters
$category = clean($_GET['category'] ?? '');
$searchQuery = clean($_GET['search'] ?? '');
$shopId = intval($_GET['shop'] ?? 0);
$sort = clean($_GET['sort'] ?? 'name_asc');

// Pagination
$page = intval($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ["sm.stock_quantity > 0", "s.is_active = 1"];
$params = [];
$types = "";

if (!empty($category)) {
    $whereConditions[] = "m.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(m.name LIKE ? OR m.generic_name LIKE ? OR m.brand LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if ($shopId > 0) {
    $whereConditions[] = "sm.shop_id = ?";
    $params[] = $shopId;
    $types .= "i";
}

$whereClause = implode(" AND ", $whereConditions);

// Sort options
$orderBy = match($sort) {
    'price_asc' => 'sm.price ASC',
    'price_desc' => 'sm.price DESC',
    'name_desc' => 'm.name DESC',
    default => 'm.name ASC'
};

// Count total
$countQuery = "SELECT COUNT(DISTINCT m.id) as total
               FROM medicines m
               JOIN shop_medicines sm ON m.id = sm.medicine_id
               JOIN shops s ON sm.shop_id = s.id
               WHERE $whereClause";

$countStmt = $conn->prepare($countQuery);
if (!empty($types)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$productsQuery = "SELECT m.*, sm.price, sm.stock_quantity, sm.shop_id,
                  s.name as shop_name, s.city
                  FROM medicines m
                  JOIN shop_medicines sm ON m.id = sm.medicine_id
                  JOIN shops s ON sm.shop_id = s.id
                  WHERE $whereClause
                  GROUP BY m.id
                  ORDER BY $orderBy
                  LIMIT ? OFFSET ?";

$productsStmt = $conn->prepare($productsQuery);
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes = $types . "ii";
if (!empty($allTypes)) {
    $productsStmt->bind_param($allTypes, ...$allParams);
}
$productsStmt->execute();
$products = $productsStmt->get_result();

// Get categories & shops
$categories = $conn->query("SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL ORDER BY category");
$shops = $conn->query("SELECT * FROM shops WHERE is_active = 1 ORDER BY name");

include 'includes/header.php';
?>

<?php qm_hero('Shop Medicines', $totalProducts . ' genuine products available from verified branches.', 'QuickMed Shop', '🛍️'); ?>

<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="flex flex-col lg:grid lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
        <aside class="lg:col-span-1 order-1">
            <div class="card sticky top-[4.5rem] p-3 sm:p-4">
                <details class="group" open>
                    <summary class="list-none flex justify-between items-center cursor-pointer lg:cursor-default">
                        <h3 class="text-[0.9rem] sm:text-[1rem] font-bold text-[#065f46] font-display">🔍 FILTERS</h3>
                        <span class="lg:hidden text-[#065f46] transform group-open:rotate-180 transition-transform text-sm">▼</span>
                    </summary>
                    <div class="mt-3">
                        <form method="GET">
                            <div class="mb-3">
                                <label class="label text-[0.78rem]">Search</label>
                                <input type="text" name="search" class="input text-[0.82rem] py-2" placeholder="Medicine name..." value="<?= htmlspecialchars($searchQuery) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="label text-[0.78rem]">Category</label>
                                <select name="category" class="input text-[0.82rem] py-2" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php 
                                    // Reset pointer just in case
                                    $categories->data_seek(0);
                                    while ($cat = $categories->fetch_assoc()): 
                                    ?>
                                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="label text-[0.78rem]">Shop</label>
                                <select name="shop" class="input text-[0.82rem] py-2" onchange="this.form.submit()">
                                    <option value="">All Shops</option>
                                    <?php 
                                    $shops->data_seek(0);
                                    while ($shop = $shops->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $shop['id'] ?>" <?= $shopId === $shop['id'] ? 'selected' : '' ?>><?= htmlspecialchars($shop['city']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="label text-[0.78rem]">Sort By</label>
                                <select name="sort" class="input text-[0.82rem] py-2" onchange="this.form.submit()">
                                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-sm py-2.5 text-[0.78rem]">Apply Filters</button>
                            <?php if (!empty($category) || !empty($searchQuery) || $shopId > 0): ?>
                                <a href="shop.php" class="btn btn-ghost btn-block mt-2 btn-sm text-[0.75rem]">✕ Clear All</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        <main class="lg:col-span-3 order-2">
            <?php if ($products->num_rows === 0): ?>
                <?php qm_empty('No Products Found', 'Try a different search or clear the filters.', '✕ Clear Filters', SITE_URL . '/shop.php'); ?>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-3 md:gap-4">
                    <?php while ($prod = $products->fetch_assoc()): ?>
                        <div class="product-card">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $prod['id'] ?>" class="p-img block">
                                <img src="<?= SITE_URL ?>/uploads/medicines/<?= $prod['image'] ?? 'placeholder.png' ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'">
                            </a>
                            <div class="p-body">
                                <h3 class="p-name truncate">
                                    <a href="<?= SITE_URL ?>/product.php?id=<?= $prod['id'] ?>" class="hover:underline"><?= htmlspecialchars($prod['name']) ?></a>
                                </h3>
                                <p class="p-meta truncate"><?= htmlspecialchars($prod['power'] ?? '') ?></p>
                                <p class="p-meta truncate">📍 <?= htmlspecialchars($prod['city'] ?? '') ?></p>
                                <div class="flex flex-wrap justify-between items-center mt-3 mb-3 gap-1">
                                    <span class="p-price"><?= qm_money($prod['price']) ?></span>
                                    <?= qm_badge($prod['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock') ?>
                                </div>
                                <button onclick="addToCart(<?= $prod['id'] ?>, <?= $prod['shop_id'] ?>, 1)" class="btn btn-primary btn-block btn-sm mt-auto">
                                    🛒 Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</section>

<script>
async function addToCart(medicineId, shopId, quantity) {
    const siteUrl = '<?= SITE_URL ?>';
    
    try {
        const formData = new FormData();
        formData.append('medicine_id', medicineId);
        formData.append('shop_id', shopId);
        formData.append('quantity', quantity);

        const response = await fetch(siteUrl + '/ajax/add_to_cart.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: 'Item added to cart',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                background: '#065f46',
                color: '#fff'
            });
            
            // Update Badge Logic
            const badges = document.querySelectorAll('.cart-count, .absolute.-top-2');
            badges.forEach(b => {
                b.innerText = result.cart_count;
                b.classList.remove('hidden');
            });
            
        } else {
            if (result.message === 'login_required') {
                Swal.fire({
                    title: 'Login Required',
                    text: 'Please login to shop',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    confirmButtonColor: '#065f46'
                }).then((res) => {
                    if(res.isConfirmed) window.location.href = siteUrl + '/login.php';
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, confirmButtonColor: '#065f46' });
            }
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: 'error', title: 'System Error', text: 'Check console for details' });
    }
}
</script>

<?php include 'includes/footer.php'; ?>