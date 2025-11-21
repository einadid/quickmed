<?php
/**
 * Shop - Products Listing Page
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
                  s.name as shop_name, s.city,
                  MIN(sm.price) as min_price
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

// Get categories
$categoriesQuery = "SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL ORDER BY category";
$categories = $conn->query($categoriesQuery);

// Get shops
$shopsQuery = "SELECT * FROM shops WHERE is_active = 1 ORDER BY name";
$shops = $conn->query($shopsQuery);

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <!-- Header -->
    <div class="text-center mb-12" data-aos="fade-down">
        <h1 class="text-5xl font-bold text-deep-green mb-4 font-mono uppercase">
            🛍️ Shop Medicines
        </h1>
        <div class="bg-lime-accent inline-block px-6 py-3 border-4 border-deep-green">
            <p class="text-deep-green font-bold text-xl">
                <?= $totalProducts ?> Products Available
            </p>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Filters Sidebar -->
        <aside class="lg:col-span-1">
            <div class="card bg-white border-4 border-deep-green sticky top-24" data-aos="fade-right">
                <h3 class="text-2xl font-bold text-deep-green mb-6 uppercase border-b-4 border-deep-green pb-3">
                    🔍 Filters
                </h3>

                <form method="GET" action="" id="filterForm">
                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block font-bold mb-2 text-deep-green">Search</label>
                        <input 
                            type="text" 
                            name="search" 
                            class="input border-4 border-deep-green" 
                            placeholder="Search medicine..."
                            value="<?= htmlspecialchars($searchQuery) ?>"
                        >
                    </div>

                    <!-- Category -->
                    <div class="mb-6">
                        <label class="block font-bold mb-2 text-deep-green">Category</label>
                        <select name="category" class="input border-4 border-deep-green" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Shop -->
                    <div class="mb-6">
                        <label class="block font-bold mb-2 text-deep-green">Shop Location</label>
                        <select name="shop" class="input border-4 border-deep-green" onchange="this.form.submit()">
                            <option value="">All Shops</option>
                            <?php while ($shop = $shops->fetch_assoc()): ?>
                                <option value="<?= $shop['id'] ?>" <?= $shopId === $shop['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($shop['city']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="mb-6">
                        <label class="block font-bold mb-2 text-deep-green">Sort By</label>
                        <select name="sort" class="input border-4 border-deep-green" onchange="this.form.submit()">
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        Apply Filters
                    </button>

                    <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline w-full mt-3">
                        Clear All
                    </a>
                </form>
            </div>
        </aside>

        <!-- Products Grid -->
<!-- Products Grid - 2 columns on mobile -->
<main class="lg:col-span-3">
    <?php if ($products->num_rows === 0): ?>
        <div class="card bg-white text-center py-20" data-aos="zoom-in">
            <div class="text-9xl mb-6">😔</div>
            <h2 class="text-3xl font-bold text-gray-600 mb-4">No Products Found</h2>
            <p class="text-lg text-gray-500 mb-8">Try adjusting your filters</p>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary">
                View All Products
            </a>
        </div>
    <?php else: ?>
        <!-- Changed from md:grid-cols-3 to grid-cols-2 md:grid-cols-3 -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="card bg-white hover:shadow-retro-lg transition-all" data-aos="zoom-in">
                    <!-- Image -->
                    <div class="bg-off-white p-2 md:p-4 mb-2 md:mb-4 border-2 border-deep-green">
                        <img 
                            src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>" 
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="w-full h-32 md:h-40 object-contain"
                            loading="lazy"
                        >
                    </div>

                    <!-- Info -->
                    <h3 class="text-sm md:text-lg font-bold text-deep-green mb-2 uppercase leading-tight">
                        <?= htmlspecialchars($product['name']) ?>
                    </h3>

                    <p class="text-xs md:text-sm text-gray-600 mb-2 hidden md:block">
                        <strong>Generic:</strong> <?= htmlspecialchars($product['generic_name']) ?>
                    </p>

                    <p class="text-xs md:text-sm text-gray-600 mb-2">
                        <strong>Power:</strong> <?= htmlspecialchars($product['power']) ?>
                    </p>

                    <p class="text-xs md:text-sm text-gray-600 mb-2 md:mb-3">
                        📍 <?= htmlspecialchars($product['city']) ?>
                    </p>

                    <!-- Stock -->
                    <?php if ($product['stock_quantity'] > 50): ?>
                        <span class="badge badge-success mb-2 md:mb-3 text-xs">✅ In Stock</span>
                    <?php elseif ($product['stock_quantity'] > 0): ?>
                        <span class="badge badge-warning mb-2 md:mb-3 text-xs">⚠️ Low Stock</span>
                    <?php else: ?>
                        <span class="badge badge-danger mb-2 md:mb-3 text-xs">❌ Out of Stock</span>
                    <?php endif; ?>

                    <!-- Price -->
                    <div class="mb-2 md:mb-4">
                        <span class="text-xl md:text-3xl font-bold text-deep-green">৳<?= number_format($product['price'], 2) ?></span>
                    </div>

                    <!-- Prescription Badge -->
                    <?php if ($product['requires_prescription']): ?>
                        <div class="bg-yellow-100 border-2 border-yellow-500 text-yellow-800 text-xs px-2 py-1 mb-2 md:mb-3 text-center font-bold">
                            ⚠️ Rx Required
                        </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <button 
                        onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, 1)"
                        class="btn btn-primary w-full text-xs md:text-base py-2 md:py-3"
                        <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                    >
                        🛒 Add
                    </button>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination mt-8 md:mt-12 flex justify-center gap-2 flex-wrap" data-aos="fade-up">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 md:px-4 py-2 border-4 border-deep-green hover:bg-lime-accent transition-all text-sm md:text-base">
                        ← Prev
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a 
                        href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                        class="px-3 md:px-4 py-2 border-4 border-deep-green <?= $i === $page ? 'bg-deep-green text-white' : 'hover:bg-lime-accent' ?> transition-all text-sm md:text-base"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 md:px-4 py-2 border-4 border-deep-green hover:bg-lime-accent transition-all text-sm md:text-base">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
    </div>
</section>

<?php include 'includes/footer.php'; ?>