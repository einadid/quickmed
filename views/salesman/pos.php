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

// --- LOAD PRESCRIPTION DATA IF ID EXISTS ---
$prescData = null;
if (isset($_GET['prescription_id'])) {
    $pid = intval($_GET['prescription_id']);
    // Using LEFT JOIN in case the user was deleted or it's a guest order handled differently
    $pQuery = "SELECT p.*, u.member_id, u.full_name, u.phone 
               FROM prescriptions p 
               LEFT JOIN users u ON p.user_id = u.id 
               WHERE p.id = ?";
    $pStmt = $conn->prepare($pQuery);
    $pStmt->bind_param("i", $pid);
    $pStmt->execute();
    $prescData = $pStmt->get_result()->fetch_assoc();
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
    
    <div class="flex-1 flex flex-col h-full overflow-hidden">
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

        <div class="px-4 py-2 flex gap-2 overflow-x-auto no-scrollbar bg-white border-b">
            <button class="px-4 py-1.5 bg-deep-green text-white rounded-full text-xs font-bold shadow-sm whitespace-nowrap">All</button>
            <button class="px-4 py-1.5 bg-gray-100 hover:bg-green-50 text-gray-600 rounded-full text-xs font-bold whitespace-nowrap transition">Tablets</button>
            <button class="px-4 py-1.5 bg-gray-100 hover:bg-green-50 text-gray-600 rounded-full text-xs font-bold whitespace-nowrap transition">Syrups</button>
        </div>

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

    <div class="bottom-cart-bar lg:hidden" onclick="toggleCart()">
        <div class="flex items-center gap-3">
            <div class="bg-lime-accent text-deep-green w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm" id="mobCount">0</div>
            <span class="font-bold text-sm">View Cart</span>
        </div>
        <span class="text-xl font-bold">৳<span id="mobTotal">0.00</span></span>
    </div>

    <div id="cartSheet" class="cart-sheet lg:flex flex-col bg-white border-l border-gray-200">
        
        <div class="lg:hidden w-full h-8 flex items-center justify-center bg-gray-50 rounded-t-2xl cursor-pointer border-b" onclick="toggleCart()">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <div class="p-4 border-b bg-white flex justify-between items-center">
            <h2 class="text-lg font-bold text-deep-green flex items-center gap-2">
                <span>🛒 Current Order</span>
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs" id="cartHeaderCount">0 Items</span>
            </h2>
            <button class="lg:hidden text-gray-400 text-2xl" onclick="toggleCart()">&times;</button>
        </div>

        <div class="p-4 bg-gray-50 border-b space-y-3">
            
            <input type="hidden" id="prescriptionId" value="<?= $prescData ? $prescData['id'] : '' ?>">

            <div class="flex gap-2 items-center">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-2.5 text-gray-400 text-lg">👤</span>
                    <input type="text" id="memberId" 
                           class="w-full pl-10 p-2 text-sm border rounded-lg focus:border-green-500 outline-none shadow-sm transition-all" 
                           placeholder="Scan / Enter Member ID"
                           value="<?= $prescData ? htmlspecialchars($prescData['member_id']) : '' ?>"
                           onkeypress="handleMemberEnter(event)">
                </div>
                <button onclick="checkMember()" class="bg-deep-green text-white px-4 py-2 rounded-lg shadow hover:bg-opacity-90 text-sm font-bold transition-transform active:scale-95">
                    Check
                </button>
            </div>

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

                <div class="border-t border-green-200 pt-2 mt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="usePointsCheck" onchange="toggleRedeem()" class="w-4 h-4 accent-deep-green rounded">
                        <span class="text-xs font-bold text-deep-green">Redeem Points?</span>
                    </label>
                    
                    <div id="redeemInputContainer" class="hidden mt-2 flex items-center gap-2">
                        <input type="number" id="pointsToRedeem" class="w-full p-1 text-sm border rounded focus:outline-none focus:border-deep-green" placeholder="Points to use" oninput="renderCart()">
                        <span class="text-xs text-gray-500 whitespace-nowrap">Max: <span id="maxPoints">0</span></span>
                    </div>
                </div>
            </div>
            
            <input type="text" id="customerName" 
                   class="w-full p-2 border rounded text-sm focus:border-green-500 outline-none" 
                   placeholder="Customer Name"
                   value="<?= $prescData ? htmlspecialchars($prescData['customer_name']) : '' ?>">
            
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1 block">Delivery Address / Note (Printed on Invoice)</label>
                <textarea id="orderNote" rows="2" class="w-full p-2 border rounded text-sm focus:border-green-500 outline-none resize-none" placeholder="Enter delivery address or special note..."><?= $prescData ? htmlspecialchars($prescData['customer_address']) : '' ?></textarea>
            </div>

            <?php if ($prescData): ?>
                <div class="bg-yellow-50 p-3 border border-yellow-200 rounded text-sm text-yellow-800 mb-2 shadow-sm">
                    📝 <strong>Rx Notes:</strong><br>
                    <span class="italic text-gray-700"><?= nl2br(htmlspecialchars($prescData['notes'])) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll" id="cartItems">
            <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50">
                <span class="text-5xl mb-2">🛍️</span>
                <p class="text-sm">Cart is empty</p>
            </div>
        </div>

        <div class="p-4 bg-white border-t shadow-[0_-5px_20px_rgba(0,0,0,0.05)] pb-8 lg:pb-4">
            
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>Subtotal</span>
                <span class="font-mono font-bold">৳<span id="subtotal">0.00</span></span>
            </div>
            
            <div id="discountRow" class="flex justify-between items-center mb-2 text-sm text-green-600 hidden">
                <span>Discount (Points)</span>
                <span class="font-mono font-bold">-৳<span id="discountVal">0.00</span></span>
            </div>

            <div class="flex justify-between items-center mb-4 bg-gray-50 p-2 rounded-lg border border-gray-200">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="vatCheck" onchange="renderCart()" class="w-4 h-4 accent-green-600 rounded cursor-pointer" checked>
                    <span class="text-xs font-bold text-gray-600">VAT</span>
                    <div class="relative flex items-center">
                        <input type="number" id="vatRate" value="5" min="0" max="100" 
                               class="w-12 p-1 text-center text-xs font-bold border rounded focus:border-green-500 outline-none"
                               oninput="renderCart()">
                        <span class="text-xs font-bold text-gray-500 ml-1">%</span>
                    </div>
                </div>
                <span class="font-mono font-bold text-gray-700">৳<span id="vatAmount">0.00</span></span>
            </div>

            <div class="flex justify-between items-center mb-4 pt-2 border-t border-dashed border-gray-300">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Payable</p>
                    <p class="text-3xl font-bold text-deep-green">৳<span id="grandTotal">0.00</span></p>
                </div>
            </div>

            <form method="POST" id="posForm">
                <input type="hidden" name="complete_sale" value="1">
                
                <button type="button" onclick="submitSale()" class="w-full bg-deep-green text-white py-3.5 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>⚡ Complete Sale</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
let cart = [];
let isCartOpen = false;
let memberPoints = 0;

// ✅ 1. Safe Beep Function
function playBeep() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (AudioContext) {
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 1000;
            osc.type = "sine";
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.1);
            osc.start();
            osc.stop(ctx.currentTime + 0.1);
        }
    } catch (e) {
        console.log("Audio play failed, but cart should work.");
    }
}

// ✅ 2. Add to Cart
function addToCart(product) {
    playBeep();
    
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.quantity < product.stock_quantity) {
            existing.quantity++;
        } else {
            showToast('Stock limit reached!', 'warning');
        }
    } else {
        cart.push({...product, quantity: 1});
    }
    renderCart();
    
    // Mobile Animation
    const bar = document.querySelector('.bottom-cart-bar');
    if (bar && window.innerWidth < 1024) {
        bar.classList.add('scale-105');
        setTimeout(() => bar.classList.remove('scale-105'), 200);
    }
}

// ✅ 3. Render Cart Logic
function renderCart() {
    const container = document.getElementById('cartItems');
    let subtotal = 0;
    let count = 0;

    if (cart.length === 0) {
        container.innerHTML = `<div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50"><span class="text-5xl mb-2">🛍️</span><p class="text-sm">Cart is empty</p></div>`;
    } else {
        container.innerHTML = '';
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
                        <p class="font-bold text-sm text-deep-green">৳${(item.price * item.quantity).toFixed(2)}</p>
                        <button onclick="remove(${idx})" class="text-[10px] text-red-400 hover:text-red-600">✕</button>
                    </div>
                </div>
            `;
        });
    }

    // --- CALCULATIONS ---
    // 1. Points Discount
    let discount = 0;
    const pointsCheck = document.getElementById('usePointsCheck');
    if (pointsCheck && pointsCheck.checked) {
        let pointsInput = parseInt(document.getElementById('pointsToRedeem').value) || 0;
        if (pointsInput > memberPoints) {
            pointsInput = memberPoints;
            document.getElementById('pointsToRedeem').value = pointsInput;
        }
        let validPoints = Math.floor(pointsInput / 100) * 100;
        discount = (validPoints / 100) * 10;
    }

    // 2. VAT Calculation
    const isVatEnabled = document.getElementById('vatCheck').checked;
    let vatRate = parseFloat(document.getElementById('vatRate').value) || 0;
    document.getElementById('vatRate').disabled = !isVatEnabled;

    const taxableAmount = subtotal - discount;
    const vatAmount = isVatEnabled ? (taxableAmount * vatRate / 100) : 0;
    const total = taxableAmount + vatAmount;

    // Update UI
    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    
    const discountRow = document.getElementById('discountRow');
    if (discountRow) {
        if (discount > 0) {
            discountRow.classList.remove('hidden');
            document.getElementById('discountVal').innerText = discount.toFixed(2);
        } else {
            discountRow.classList.add('hidden');
        }
    }

    document.getElementById('vatAmount').innerText = vatAmount.toFixed(2);
    document.getElementById('grandTotal').innerText = total.toFixed(2);
    
    // Mobile Updates
    const mobTotal = document.getElementById('mobTotal');
    if(mobTotal) mobTotal.innerText = total.toFixed(2);
    
    const mobCount = document.getElementById('mobCount');
    if(mobCount) mobCount.innerText = count;
    
    const headerCount = document.getElementById('cartHeaderCount');
    if(headerCount) headerCount.innerText = count + ' Items';
}

// Helper Functions
function updateQty(idx, change) {
    if (cart[idx].quantity + change > 0 && cart[idx].quantity + change <= cart[idx].stock_quantity) {
        cart[idx].quantity += change;
    }
    renderCart();
}

function remove(idx) {
    cart.splice(idx, 1);
    renderCart();
}

// Toggle Logic
function toggleCart() {
    const sheet = document.getElementById('cartSheet');
    isCartOpen = !isCartOpen;
    if (isCartOpen) sheet.classList.add('open');
    else sheet.classList.remove('open');
}

function toggleRedeem() {
    const isChecked = document.getElementById('usePointsCheck').checked;
    const container = document.getElementById('redeemInputContainer');
    const input = document.getElementById('pointsToRedeem');

    if (isChecked) {
        container.classList.remove('hidden');
        input.focus();
    } else {
        container.classList.add('hidden');
        input.value = '';
        renderCart();
    }
}

function handleMemberEnter(e) { if (e.key === 'Enter') checkMember(); }

async function checkMember() {
    const id = document.getElementById('memberId').value.trim();
    const infoBox = document.getElementById('memberInfo');
    
    if (!id) return showToast('Enter Member ID', 'warning');
    
    try {
        const res = await fetch(`../../ajax/check_member.php?member_id=${encodeURIComponent(id)}`);
        const data = await res.json();
        
        infoBox.classList.remove('hidden');
        if (data.success) {
            infoBox.className = "bg-green-50 p-3 rounded-lg border border-green-200 shadow-sm relative overflow-hidden mt-2";
            document.getElementById('memName').innerText = data.member.full_name;
            document.getElementById('memPoints').innerText = data.member.points;
            document.getElementById('customerName').value = data.member.full_name;
            
            memberPoints = parseInt(data.member.points);
            document.getElementById('maxPoints').innerText = memberPoints;
            
            showToast('Member Verified!', 'success');
            playBeep();
        } else {
            infoBox.className = "bg-red-50 p-3 rounded-lg border border-red-200 shadow-sm relative overflow-hidden mt-2";
            document.getElementById('memName').innerText = 'Not Found';
            document.getElementById('memPoints').innerText = '-';
            memberPoints = 0;
            showToast('Member not found', 'error');
        }
    } catch (e) {
        console.error(e);
    }
}

// Submit Sale (UPDATED)
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
        // ✅ Added Address/Note
        customer_address: document.getElementById('orderNote').value, 
        prescription_id: document.getElementById('prescriptionId') ? document.getElementById('prescriptionId').value : null,
        vat_percent: document.getElementById('vatCheck').checked ? parseFloat(document.getElementById('vatRate').value) : 0,
        points_used: document.getElementById('usePointsCheck') && document.getElementById('usePointsCheck').checked ? (parseInt(document.getElementById('pointsToRedeem').value) || 0) : 0
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
            
            // Clear Cart
            cart = [];
            renderCart();
            
            // AUTO OPEN PRINT WINDOW
            const printUrl = 'print-invoice.php?id=' + data.order_id;
            const printWindow = window.open(printUrl, '_blank', 'width=400,height=600,scrollbars=no,resizable=no');
            
            // If prescription order, redirect back to list after a delay
            if (payload.prescription_id) {
                setTimeout(() => window.location.href = 'prescriptions.php', 2000);
            }
            
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Network Error', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function showToast(msg, type) {
    Swal.fire({ icon: type, title: msg, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
}

// Search
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