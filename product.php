<?php
require_once 'config.php';
$productId = intval($_GET['id'] ?? 0);
if (!$productId) { redirect('shop.php'); }
$query = "SELECT m.*, sm.price, sm.stock_quantity, sm.shop_id, s.name as shop_name, s.city 
          FROM medicines m
          JOIN shop_medicines sm ON m.id = sm.medicine_id
          JOIN shops s ON sm.shop_id = s.id
          WHERE m.id = ? AND sm.stock_quantity > 0 AND s.is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    $baseQuery = "SELECT * FROM medicines WHERE id = ?";
    $stmt = $conn->prepare($baseQuery);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) {
        include 'includes/header.php';
        echo "<div class='text-center py-16 px-4'><h1 class='text-2xl sm:text-3xl font-bold text-red-600'>Product Not Found</h1><a href='".SITE_URL."/shop.php' class='btn btn-primary mt-4'>Back to Shop</a></div>";
        include 'includes/footer.php';
        exit;
    }
    $product['price'] = 0;
    $product['stock_quantity'] = 0;
    $product['shop_name'] = 'Not Available';
}
$pageTitle = $product['name'] . ' - QuickMed';
include 'includes/header.php';
?>
<?php qm_hero($product['name'], trim(($product['power'] ?? '') . ' | ' . ($product['form'] ?? ''), ' |'), $product['category'] ?? 'Medicine', '💊'); ?>
<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <div class="mb-4"><?= qm_back(SITE_URL . '/shop.php', '← Back to Shop') ?></div>
        <div class="card p-3 sm:p-5 rounded-xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-8">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 sm:p-6 flex items-center justify-center">
                <img src="<?= SITE_URL ?>/uploads/medicines/<?= $product['image'] ?? 'placeholder.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="max-h-60 sm:max-h-72 w-full object-contain hover:scale-105 transition-transform duration-500" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.png'">
            </div>
            <div class="flex flex-col justify-center">
                <div class="mb-2.5 flex flex-wrap gap-1.5">
                    <span class="badge badge-lime text-[10px]"><?= htmlspecialchars($product['category'] ?? 'General') ?></span>
                    <?php if (!empty($product['form'])): ?><span class="badge badge-neutral text-[10px]"><?= htmlspecialchars($product['form']) ?></span><?php endif; ?>
                </div>
                <h2 class="text-[1.3rem] sm:text-xl md:text-2xl font-bold text-[#065f46] mb-1 leading-tight"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-[0.85rem] sm:text-sm text-gray-500 mb-3 sm:mb-4 font-display"><?= htmlspecialchars($product['power'] ?? '') ?></p>
                <div class="mb-4 space-y-1 text-[0.78rem] sm:text-[0.82rem] text-gray-700 bg-gray-50 rounded-xl p-3 border border-gray-200">
                    <p><strong>Generic:</strong> <?= htmlspecialchars($product['generic_name'] ?? '—') ?></p>
                    <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand'] ?? '—') ?></p>
                    <p><strong>Manufacturer:</strong> <?= htmlspecialchars($product['manufacturer'] ?? '—') ?></p>
                    <p><strong>Shop:</strong> <?= htmlspecialchars($product['shop_name']) ?> (<?= htmlspecialchars($product['city'] ?? '') ?>)</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 mb-4 sm:mb-5">
                    <?php if ($product['price'] > 0): ?><span class="text-[1.6rem] sm:text-2xl font-bold text-[#065f46] font-display"><?= qm_money($product['price']) ?></span><?php else: ?><?= qm_badge('Out of Stock') ?><?php endif; ?>
                    <?php if ($product['stock_quantity'] > 0): ?><span class="badge badge-success text-[10px]">In Stock: <?= (int)$product['stock_quantity'] ?></span><?php else: ?><?= qm_badge('Out of Stock') ?><?php endif; ?>
                </div>
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="flex flex-wrap gap-2.5">
                        <div class="qty-stepper h-10"><button type="button" onclick="updateQty(-1)">−</button><input type="number" id="qty" value="1" min="1" max="<?= $product['stock_quantity'] ?>"><button type="button" onclick="updateQty(1)">+</button></div>
                        <button onclick="addToCart(<?= $product['id'] ?>, <?= $product['shop_id'] ?>, document.getElementById('qty').value)" class="btn btn-primary btn-lg flex-1 text-[0.82rem] sm:text-[0.85rem] py-2.5">🛒 Add to Cart</button>
                    </div>
                <?php endif; ?>
                <?php if ($product['requires_prescription']): ?>
                    <div class="alert alert-warning mt-4 text-[0.78rem]"><span>⚠️</span><div><b>Prescription Required.</b><br><span class="text-[11px]">You must upload a prescription to order this item.</span></div></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($product['description'])): ?>
        <div class="mt-6 sm:mt-8 pt-4 sm:pt-5 border-t border-gray-200">
            <h3 class="text-[0.95rem] sm:text-[1.05rem] font-bold text-[#065f46] mb-2 font-display">DESCRIPTION</h3>
            <p class="text-gray-600 leading-relaxed text-[0.82rem] sm:text-[0.88rem]"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
        <?php endif; ?>
        </div>
    </div>
</section>
<script>
function updateQty(change){const input=document.getElementById('qty');if(!input)return;let val=parseInt(input.value)+change;if(isNaN(val)||val<1)val=1;if(val>parseInt(input.max))val=parseInt(input.max);input.value=val;}
async function addToCart(medicineId, shopId, quantity){
    quantity=quantity||1;const siteUrl='<?= SITE_URL ?>';
    try{
        const formData=new FormData();formData.append('medicine_id',medicineId);formData.append('shop_id',shopId);formData.append('quantity',quantity);
        const response=await fetch(siteUrl+'/ajax/add_to_cart.php',{method:'POST',body:formData});
        const result=await response.json();
        if(result.success){Swal.fire({icon:'success',title:'Added to cart!',toast:true,position:'top-end',showConfirmButton:false,timer:2000,background:'#065f46',color:'#fff'});setTimeout(()=>{window.location.href=siteUrl+'/cart.php';},900);}
        else if(result.message==='login_required'){Swal.fire({title:'Login Required',text:'Please login to shop',icon:'warning',showCancelButton:true,confirmButtonText:'Login',confirmButtonColor:'#065f46'}).then((res)=>{if(res.isConfirmed)window.location.href=siteUrl+'/login.php';});}
        else{Swal.fire({icon:'error',title:'Error',text:result.message,confirmButtonColor:'#065f46'});}
    }catch(error){Swal.fire({icon:'error',title:'System Error',text:'Please try again.',confirmButtonColor:'#065f46'});}
}
</script>
<?php include 'includes/footer.php'; ?>
