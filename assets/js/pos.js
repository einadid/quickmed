/**
 * POS System JavaScript
 */

let posCart = [];

// Search products
document.getElementById('posSearch')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const products = document.querySelectorAll('.product-item');
    
    products.forEach(product => {
        const name = product.dataset.name.toLowerCase();
        if (name.includes(query)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
});

// Add to POS cart
function addToPOSCart(element) {
    const product = {
        id: parseInt(element.dataset.id),
        name: element.dataset.name,
        power: element.dataset.power,
        price: parseFloat(element.dataset.price),
        stock: parseInt(element.dataset.stock),
        quantity: 1
    };

    // Check if already in cart
    const existingIndex = posCart.findIndex(item => item.id === product.id);
    
    if (existingIndex >= 0) {
        if (posCart[existingIndex].quantity < product.stock) {
            posCart[existingIndex].quantity++;
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Stock Limit',
                text: `Only ${product.stock} items available`,
                confirmButtonColor: '#065f46'
            });
            return;
        }
    } else {
        posCart.push(product);
    }

    renderPOSCart();
}

// Render POS cart
function renderPOSCart() {
    const container = document.getElementById('posCartItems');
    
    if (posCart.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-600 py-8">Cart is empty</div>';
        document.getElementById('completeSaleBtn').disabled = true;
    } else {
        let html = '';
        let subtotal = 0;

        posCart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            html += `
                <div class="bg-white border-2 border-deep-green p-3">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <p class="font-bold text-sm">${item.name}</p>
                            <p class="text-xs text-gray-600">${item.power}</p>
                        </div>
                        <button onclick="removeFromPOSCart(${index})" class="text-red-600 hover:text-red-800 font-bold">
                            ✕
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updatePOSQuantity(${index}, -1)" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 font-bold border-2 border-deep-green">
                            -
                        </button>
                        <input 
                            type="number" 
                            value="${item.quantity}" 
                            min="1" 
                            max="${item.stock}"
                            onchange="updatePOSQuantityDirect(${index}, this.value)"
                            class="w-16 text-center font-bold border-2 border-deep-green py-1"
                        >
                        <button onclick="updatePOSQuantity(${index}, 1)" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 font-bold border-2 border-deep-green">
                            +
                        </button>
                        <span class="flex-1 text-right font-bold text-deep-green">৳${itemTotal.toFixed(2)}</span>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('posSubtotal').textContent = '৳' + subtotal.toFixed(2);
        document.getElementById('posTotal').textContent = '৳' + subtotal.toFixed(2);
        document.getElementById('completeSaleBtn').disabled = false;
    }
}

// Update quantity
function updatePOSQuantity(index, change) {
    const newQty = posCart[index].quantity + change;
    
    if (newQty < 1) {
        removeFromPOSCart(index);
        return;
    }
    
    if (newQty > posCart[index].stock) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock Limit',
            text: `Only ${posCart[index].stock} items available`,
            confirmButtonColor: '#065f46'
        });
        return;
    }
    
    posCart[index].quantity = newQty;
    renderPOSCart();
}

function updatePOSQuantityDirect(index, value) {
    const newQty = parseInt(value);
    
    if (newQty < 1 || isNaN(newQty)) {
        removeFromPOSCart(index);
        return;
    }
    
    if (newQty > posCart[index].stock) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock Limit',
            text: `Only ${posCart[index].stock} items available`,
            confirmButtonColor: '#065f46'
        });
        return;
    }
    
    posCart[index].quantity = newQty;
    renderPOSCart();
}

// Remove from cart
function removeFromPOSCart(index) {
    posCart.splice(index, 1);
    renderPOSCart();
}

// Clear cart
function clearPOSCart() {
    Swal.fire({
        title: 'Clear Cart?',
        text: 'Are you sure you want to clear the cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Yes, clear it!'
    }).then((result) => {
        if (result.isConfirmed) {
            posCart = [];
            renderPOSCart();
        }
    });
}

// Complete sale
async function completeSale() {
    if (posCart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Cart',
            text: 'Please add items to cart',
            confirmButtonColor: '#065f46'
        });
        return;
    }

    const customerName = document.getElementById('customerName').value || 'Walk-in Customer';
    const customerPhone = document.getElementById('customerPhone').value;

    const result = await Swal.fire({
        title: 'Complete Sale?',
        html: `
            <div class="text-left">
                <p class="mb-2"><strong>Customer:</strong> ${customerName}</p>
                ${customerPhone ? `<p class="mb-2"><strong>Phone:</strong> ${customerPhone}</p>` : ''}
                <p class="mb-2"><strong>Items:</strong> ${posCart.length}</p>
                <p class="text-2xl font-bold text-green-600">Total: ৳${calculatePOSTotal().toFixed(2)}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, complete sale!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="complete_sale" value="1">
            <input type="hidden" name="customer_name" value="${customerName}">
            <input type="hidden" name="customer_phone" value="${customerPhone}">
            <input type="hidden" name="cart_items" value='${JSON.stringify(posCart)}'>
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function calculatePOSTotal() {
    return posCart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Print modal functions
function closePrintModal() {
    const modal = document.getElementById('printModal');
    if (modal) {
        modal.remove();
        window.location.href = window.location.pathname;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderPOSCart();
});