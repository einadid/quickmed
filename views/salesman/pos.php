<?php
/**
 * Ultra Aesthetic POS - Mobile Optimized & Dynamic VAT
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('salesman');

$user = getCurrentUser();
$shopId = $user['shop_id'];

if (!$shopId) {
    die("Error: No shop assigned.");
}

// Fetch Medicines
$medicines = $conn->query("SELECT m.*, sm.price, sm.stock_quantity 
                           FROM medicines m 
                           JOIN shop_medicines sm ON m.id = sm.medicine_id 
                           WHERE sm.shop_id = $shopId AND sm.stock_quantity > 0 
                           ORDER BY m.name ASC");

include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* Modern Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #065f46; border-radius: 10px; }
    
    /* Product Card Hover */
    .product-card { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .product-card:active { transform: scale(0.95); }
    .product-card:hover { border-color: #84cc16; box-shadow: 0 4px 12px rgba(6, 95, 70, 0.15); }
    
    /* Mobile Cart Sheet */
    .cart-sheet {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: white; z-index: 100;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.1);
        transform: translateY(100%); transition: transform 0.3s ease-out;
        height: 85vh; display: flex; flex-direction: column;
    }
    .cart-sheet.open { transform: translateY(0); }
    
    /* Bottom Bar (Mobile) */
    .bottom-cart-bar {
        position: fixed; bottom: 80px; left: 16px; right: 16px;
        background: #065f46; color: white;
        border-radius: 16px; padding: 12px 20px;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 8px 20px rgba(6,95,70,0.3); z-index: 90;
        cursor: pointer; transition: all 0.3s;
    }
    .bottom-cart-bar:active { transform: scale(0.98); }

    /* Desktop Layout */
    @media (min-width: 1024px) {
        .cart-sheet {
            position: static; transform: none; height: auto;
            width: 400px; border-radius: 0; border-left: 1px solid #eee;
            box-shadow: none; z-index: 1;
        }
        .bottom-cart-bar { display: none; }
    }
</style>

<div class="h-[calc(100vh-64px)] bg-gray-50 flex overflow-hidden relative">
    
    <!-- LEFT: Product Grid -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Search Header -->
        <div class="bg-white p-4 shadow-sm z-10 flex gap-4 items-center">
            <div class="relative flex-1">
                <span class="absolute left-4 top-3.5 text-gray-400">🔍</span>
                <input type="text" id="posSearch" class="w-full pl-12 pr-4 py-3 bg-gray-100 rounded-xl border-none focus:ring-2 focus:ring-green-500 transition-all" placeholder="Search medicine (Ctrl+K)..." autofocus>
            </div>
            <div class="hidden md:block text-right">
                <p class="text-xs text-gray-500 font-bold">DATE</p>
                <p class="font-mono text-deep-green"><?= date('d M Y') ?></p>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="px-4 py-2 flex gap-2 overflow-x-auto no-scrollbar bg-white border-b">
            <button class="px-4 py-1.5 bg-deep-green text-white rounded-full text-xs font-bold shadow-sm whitespace-nowrap">All</button>
            <button class="px-4 py-1.5 bg-gray-100 hover:bg-green-50 text-gray-600 rounded-full text-xs font-bold whitespace-nowrap transition">Tablets</button>
            <button class="px-4 py-1.5 bg-gray-100 hover:bg-green-50 text-gray-600 rounded-full text-xs font-bold whitespace-nowrap transition">Syrups</button>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-y-auto p-4 pb-24 lg:pb-4 custom-scroll">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php while ($med = $medicines->fetch_assoc()): ?>
                    <div class="product-card bg-white p-3 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden cursor-pointer group"
                         onclick="addToCart(<?= htmlspecialchars(json_encode($med)) ?>)"
                         data-name="<?= strtolower($med['name']) ?>">
                        
                        <div class="absolute top-2 right-2 bg-green-50 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            Qty: <?= $med['stock_quantity'] ?>
                        </div>
                        
                        <div class="h-16 bg-green-50/50 rounded-lg mb-2 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">💊</div>
                        
                        <h3 class="font-bold text-gray-800 text-sm truncate"><?= htmlspecialchars($med['name']) ?></h3>
                        <p class="text-xs text-gray-500 mb-2"><?= htmlspecialchars($med['power']) ?></p>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-deep-green">৳<?= (int)$med['price'] ?></span>
                            <div class="w-6 h-6 bg-deep-green text-white rounded-full flex items-center justify-center shadow-md group-hover:bg-lime-accent group-hover:text-deep-green transition-colors">+</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- MOBILE CART BAR (Bottom) -->
    <div class="bottom-cart-bar lg:hidden" onclick="toggleCart()">
        <div class="flex items-center gap-3">
            <div class="bg-lime-accent text-deep-green w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm" id="mobCount">0</div>
            <span class="font-bold text-sm">View Cart</span>
        </div>
        <span class="text-xl font-bold">৳<span id="mobTotal">0.00</span></span>
    </div>

    <!-- RIGHT: Cart Sheet/Panel -->
    <div id="cartSheet" class="cart-sheet lg:flex flex-col bg-white border-l border-gray-200">
        
        <!-- Mobile Handle -->
        <div class="lg:hidden w-full h-8 flex items-center justify-center bg-gray-50 rounded-t-2xl cursor-pointer border-b" onclick="toggleCart()">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="p-4 border-b bg-white flex justify-between items-center">
            <h2 class="text-lg font-bold text-deep-green flex items-center gap-2">
                <span>🛒 Current Order</span>
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs" id="cartHeaderCount">0 Items</span>
            </h2>
            <button class="lg:hidden text-gray-400 text-2xl" onclick="toggleCart()">&times;</button>
        </div>

        <!-- Customer Input -->
        <div class="p-4 bg-gray-50 border-b space-y-3">
            <div class="flex gap-2 items-center">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-2.5 text-gray-400 text-lg">👤</span>
                    <input type="text" id="memberId" 
                           class="w-full pl-10 p-2 text-sm border rounded-lg focus:border-green-500 outline-none shadow-sm transition-all" 
                           placeholder="Scan / Enter Member ID"
                           onkeypress="handleMemberEnter(event)">
                </div>
                <button onclick="checkMember()" class="bg-deep-green text-white px-4 py-2 rounded-lg shadow hover:bg-opacity-90 text-sm font-bold transition-transform active:scale-95">
                    Check
                </button>
            </div>

          <!-- Member Info Display -->
<div id="memberInfo" class="hidden bg-green-50 p-3 rounded-lg border border-green-200 shadow-sm relative overflow-hidden mt-2">
    <div class="absolute top-0 right-0 w-8 h-8 bg-green-200 rounded-bl-full opacity-50"></div>
    
    <div class="flex justify-between items-start mb-2">
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Member Name</p>
            <p class="font-bold text-deep-green text-sm truncate" id="memName">Unknown</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Points</p>
            <p class="font-bold text-lime-600 text-sm flex items-center justify-end gap-1">
                <span class="text-lg">⭐</span> <span id="memPoints">0</span>
            </p>
        </div>
    </div>

    <!-- Redeem Points Checkbox -->
    <div class="border-t border-green-200 pt-2 mt-1">
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="usePointsCheck" onchange="toggleRedeem()" class="w-4 h-4 accent-deep-green rounded">
            <span class="text-xs font-bold text-deep-green">Redeem Points?</span>
        </label>
        
        <!-- Points Input (Hidden by Default) -->
        <div id="redeemInputContainer" class="hidden mt-2 flex items-center gap-2">
            <input type="number" id="pointsToRedeem" class="w-full p-1 text-sm border rounded focus:outline-none focus:border-deep-green" placeholder="Points to use" oninput="renderCart()">
            <span class="text-xs text-gray-500 whitespace-nowrap">Max: <span id="maxPoints">0</span></span>
        </div>
        <p class="text-[10px] text-gray-500 mt-1 hidden" id="redeemInfo">100 Points = 10 BDT Discount</p>
    </div>
</div>
            
            <input type="text" id="customerName" class="w-full p-2 border rounded text-sm focus:border-green-500 outline-none" placeholder="Customer Name (Optional)">
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll" id="cartItems">
            <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50">
                <span class="text-5xl mb-2">🛍️</span>
                <p class="text-sm">Cart is empty</p>
            </div>
        </div>

        <!-- Footer & Checkout -->
        <div class="p-4 bg-white border-t shadow-[0_-5px_20px_rgba(0,0,0,0.05)] pb-8 lg:pb-4">
            
            <!-- Subtotal -->
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>Subtotal</span>
                <span class="font-mono font-bold">৳<span id="subtotal">0.00</span></span>
            </div>

            <!-- VAT Control -->
            <div class="flex justify-between items-center mb-4 bg-gray-50 p-2 rounded-lg border border-gray-200">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="vatCheck" onchange="renderCart()" class="w-4 h-4 accent-green-600 rounded cursor-pointer" checked>
                    <span class="text-xs font-bold text-gray-600">VAT</span>
                    <!-- Dynamic VAT Input -->
                    <div class="relative flex items-center">
                        <input type="number" id="vatRate" value="5" min="0" max="100" 
                               class="w-12 p-1 text-center text-xs font-bold border rounded focus:border-green-500 outline-none"
                               oninput="renderCart()">
                        <span class="text-xs font-bold text-gray-500 ml-1">%</span>
                    </div>
                </div>
                <span class="font-mono font-bold text-gray-700">৳<span id="vatAmount">0.00</span></span>
            </div>

            <!-- Total Payable -->
            <div class="flex justify-between items-center mb-4 pt-2 border-t border-dashed border-gray-300">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Payable</p>
                    <p class="text-3xl font-bold text-deep-green">৳<span id="grandTotal">0.00</span></p>
                </div>
            </div>

            <form method="POST" id="posForm">
                <input type="hidden" name="complete_sale" value="1">
                <input type="hidden" name="cart_items" id="formCart">
                <input type="hidden" name="member_id" id="formMemberId">
                <input type="hidden" name="customer_name" id="formCustomerName">
                
                <button type="button" onclick="submitSale()" class="w-full bg-deep-green text-white py-3.5 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>⚡ Complete Sale</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Audio -->
<audio id="beep" src="https://www.soundjay.com/button/beep-07.wav"></audio>

<script>
let cart = [];
let isCartOpen = false;
const beep = document.getElementById('beep');

// Toggle Mobile Cart
function toggleCart() {
    const sheet = document.getElementById('cartSheet');
    isCartOpen = !isCartOpen;
    if (isCartOpen) sheet.classList.add('open');
    else sheet.classList.remove('open');
}

// Add Item
function addToCart(product) {
    beep.currentTime = 0; beep.play();
    
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.quantity < product.stock_quantity) existing.quantity++;
        else showToast('Stock limit reached!', 'warning');
    } else {
        cart.push({...product, quantity: 1});
    }
    renderCart();
    
    // Mobile Animation
    if (window.innerWidth < 1024) {
        const bar = document.querySelector('.bottom-cart-bar');
        bar.classList.add('scale-105');
        setTimeout(() => bar.classList.remove('scale-105'), 200);
    }
}

// Render Cart
function renderCart() {
    const container = document.getElementById('cartItems');
    let subtotal = 0;
    let count = 0;

    if (cart.length > 0) container.innerHTML = '';
    else container.innerHTML = `<div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50"><span class="text-5xl mb-2">🛍️</span><p class="text-sm">Cart is empty</p></div>`;

    cart.forEach((item, idx) => {
        subtotal += item.price * item.quantity;
        count += item.quantity;
        
        container.innerHTML += `
            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="flex-1">
                    <h4 class="font-bold text-sm text-gray-800">${item.name}</h4>
                    <p class="text-xs text-gray-500">৳${item.price} x ${item.quantity}</p>
                </div>
                <div class="flex items-center gap-3 bg-white rounded-lg px-2 py-1 shadow-sm border border-gray-200">
                    <button onclick="updateQty(${idx}, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-red-500 font-bold">-</button>
                    <span class="text-sm font-bold w-4 text-center">${item.quantity}</span>
                    <button onclick="updateQty(${idx}, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-green-500 font-bold">+</button>
                </div>
                <div class="text-right w-16">
                    <p class="font-bold text-sm text-deep-green">৳${item.price * item.quantity}</p>
                </div>
            </div>
        `;
    });

    // Calculations
    const isVatEnabled = document.getElementById('vatCheck').checked;
    const vatRate = parseFloat(document.getElementById('vatRate').value) || 0;
    
    document.getElementById('vatRate').disabled = !isVatEnabled;

    const vatAmount = isVatEnabled ? (subtotal * vatRate / 100) : 0;
    const total = subtotal + vatAmount;

    // Update UI
    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('vatAmount').innerText = vatAmount.toFixed(2);
    document.getElementById('grandTotal').innerText = total.toFixed(2);
    
    document.getElementById('mobTotal').innerText = total.toFixed(2);
    document.getElementById('mobCount').innerText = count;
    document.getElementById('cartHeaderCount').innerText = count + ' Items';
}

function updateQty(idx, change) {
    if (cart[idx].quantity + change > 0 && cart[idx].quantity + change <= cart[idx].stock_quantity) {
        cart[idx].quantity += change;
    } else if (cart[idx].quantity + change <= 0) {
        cart.splice(idx, 1);
    }
    renderCart();
}

// Member Check
function handleMemberEnter(e) { if (e.key === 'Enter') checkMember(); }

async function checkMember() {
    const id = document.getElementById('memberId').value.trim();
    const infoBox = document.getElementById('memberInfo');
    
    if (!id) return showToast('Enter ID', 'warning');
    
    const res = await fetch(`../../ajax/check_member.php?member_id=${encodeURIComponent(id)}`);
    const data = await res.json();
    
    infoBox.classList.remove('hidden');
    if (data.success) {
        infoBox.className = "bg-green-50 p-3 rounded-lg border border-green-200 shadow-sm relative overflow-hidden";
        document.getElementById('memName').innerText = data.member.full_name;
        document.getElementById('memPoints').innerText = data.member.points;
        document.getElementById('customerName').value = data.member.full_name;
        showToast('Member Verified!', 'success');
    } else {
        infoBox.className = "bg-red-50 p-3 rounded-lg border border-red-200 shadow-sm relative overflow-hidden";
        document.getElementById('memName').innerText = 'Not Found';
        document.getElementById('memPoints').innerText = '-';
        showToast('Member not found', 'error');
    }
}

// Submit Sale
async function submitSale() {
    if (cart.length === 0) return showToast('Cart Empty', 'warning');

    const btn = document.querySelector('button[onclick="submitSale()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Processing...';
    btn.disabled = true;

    const payload = {
        cart: cart,
        member_id: document.getElementById('memberId').value,
        customer_name: document.getElementById('customerName').value,
        vat_percent: document.getElementById('vatCheck').checked ? parseFloat(document.getElementById('vatRate').value) : 0
    };

    try {
        const res = await fetch('../../ajax/pos_submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            showToast('Sale Complete!', 'success');
            cart = [];
            renderCart();
            if(window.innerWidth < 1024) toggleCart(); 
            window.open('print-invoice.php?id=' + data.order_id, '_blank', 'width=400,height=600');
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Network Error', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function showToast(msg, type) {
    Swal.fire({ icon: type, title: msg, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
}

// Search & Shortcuts
document.getElementById('posSearch').addEventListener('input', (e) => {
    const val = e.target.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(el => {
        el.style.display = el.dataset.name.includes(val) ? 'block' : 'none';
    });
});

document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        document.getElementById('posSearch').focus();
    }
});
</script>