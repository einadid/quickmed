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

<section class="container mx-auto px-2 md:px-4 py-8 md:py-16 min-h-screen">
    <div class="text-center mb-6 md:mb-12">
        <h1 class="text-3xl md:text-5xl font-bold text-deep-green mb-2 md:mb-4 font-mono uppercase">🛍️ Shop Medicines</h1>
        <div class="bg-lime-accent inline-block px-4 py-2 md:px-6 md:py-3 border-2 md:border-4 border-deep-green rounded md:rounded-none">
            <p class="text-deep-green font-bold text-sm md:text-xl"><?= $totalProducts ?> Products Available</p>
        </div>
    </div>

    <div class="flex flex-col lg:grid lg:grid-cols-4 gap-6 md:gap-8">
        
        <aside class="lg:col-span-1 order-1">
            <div class="bg-white border-2 md:border-4 border-deep-green md:p-6 sticky top-20 shadow-lg">
                
                <details class="group lg:open" open>
                    <summary class="list-none flex justify-between items-center p-4 md:p-0 cursor-pointer lg:cursor-default bg-gray-50 lg:bg-transparent">
                        <h3 class="text-lg md:text-2xl font-bold text-deep-green">🔍 Filters</h3>
                        <span class="lg:hidden text-deep-green transform group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    
                    <div class="p-4 md:p-0 border-t-2 border-gray-100 md:border-t-0 mt-2 md:mt-6">
                        <form method="GET">
                            <div class="mb-3 md:mb-4">
                                <label class="block font-bold mb-1 md:mb-2 text-sm md:text-base">Search</label>
                                <input type="text" name="search" class="w-full p-2 md:p-3 border-2 border-deep-green text-sm md:text-base" placeholder="Medicine name..." value="<?= htmlspecialchars($searchQuery) ?>">
                            </div>
                            <div class="mb-3 md:mb-4">
                                <label class="block font-bold mb-1 md:mb-2 text-sm md:text-base">Category</label>
                                <select name="category" class="w-full p-2 md:p-3 border-2 border-deep-green text-sm md:text-base bg-white" onchange="this.form.submit()">
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
                            <div class="mb-3 md:mb-4">
                                <label class="block font-bold mb-1 md:mb-2 text-sm md:text-base">Shop</label>
                                <select name="shop" class="w-full p-2 md:p-3 border-2 border-deep-green text-sm md:text-base bg-white" onchange="this.form.submit()">
                                    <option value="">All Shops</option>
                                    <?php 
                                    $shops->data_seek(0);
                                    while ($shop = $shops->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $shop['id'] ?>" <?= $shopId === $shop['id'] ? 'selected' : '' ?>><?= htmlspecialchars($shop['city']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3 md:mb-4">
                                <label class="block font-bold mb-1 md:mb-2 text-sm md:text-base">Sort By</label>
                                <select name="sort" class="w-full p-2 md:p-3 border-2 border-deep-green text-sm md:text-base bg-white" onchange="this.form.submit()">
                                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-deep-green text-white py-2 md:py-3 font-bold hover:bg-lime-accent hover:text-deep-green border-2 border-transparent hover:border-deep-green transition text-sm md:text-base">Apply Filters</button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        <main class="lg:col-span-3 order-2">
            <?php if ($products->num_rows === 0): ?>
                <div class="text-center py-20">
                    <div class="text-6xl md:text-8xl mb-4">😔</div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-500">No Products Found</h2>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
                    <?php while ($prod = $products->fetch_assoc()): ?>
                        <div class="bg-white border-2 md:border-4 border-deep-green p-2 md:p-4 hover:shadow-xl transition-all transform hover:-translate-y-1 group flex flex-col justify-between h-full">
                            
                            <div>
                                <div class="bg-gray-50 p-2 md:p-4 mb-2 md:mb-4 border border-gray-200 group-hover:border-lime-accent transition-colors">
                                    <img src="<?= SITE_URL ?>/uploads/medicines/<?= $prod['image'] ?? 'placeholder.png' ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-24 md:h-32 object-contain mix-blend-multiply">
                                </div>
                                
                                <h3 class="text-sm md:text-lg font-bold text-deep-green truncate leading-tight"><?= htmlspecialchars($prod['name']) ?></h3>
                                <p class="text-xs text-gray-500 mb-1 md:mb-2 truncate"><?= htmlspecialchars($prod['power']) ?></p>
                                <p class="text-[10px] md:text-xs text-gray-400 mb-2 truncate">📍 <?= htmlspecialchars($prod['city']) ?></p>
                            </div>

                            <div>
                                <div class="flex flex-wrap justify-between items-center mb-2 md:mb-4 gap-1">
                                    <span class="text-lg md:text-2xl font-bold text-deep-green">৳<?= (int)$prod['price'] ?></span>
                                    <span class="text-[10px] md:text-xs bg-lime-accent px-1.5 py-0.5 md:px-2 md:py-1 rounded font-bold text-deep-green whitespace-nowrap">In Stock</span>
                                </div>
                                
                                <button onclick="addToCart(<?= $prod['id'] ?>, <?= $prod['shop_id'] ?>, 1)" class="w-full bg-deep-green text-white py-1.5 md:py-2 text-xs md:text-base font-bold hover:bg-lime-accent hover:text-deep-green border-2 border-transparent hover:border-deep-green transition-all active:scale-95">
                                    🛒 Add
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="flex flex-wrap justify-center gap-1 md:gap-2 mt-8 md:mt-12">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="px-3 py-1 md:px-4 md:py-2 text-sm md:text-base border-2 border-deep-green font-bold <?= $i === $page ? 'bg-deep-green text-white' : 'hover:bg-lime-accent' ?>">
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