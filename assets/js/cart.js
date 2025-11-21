/**
 * QuickMed - Cart Management JavaScript
 */

// Update cart quantity
async function updateCartQuantity(cartId, newQuantity, maxStock) {
    if (newQuantity < 1) {
        Swal.fire({
            title: 'Remove Item?',
            text: 'Do you want to remove this item from cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#065f46',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                removeFromCart(cartId);
            }
        });
        return;
    }

    if (newQuantity > maxStock) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock Limit',
            text: `Only ${maxStock} items available in stock`,
            confirmButtonColor: '#065f46'
        });
        return;
    }

    try {
        const siteUrl = window.location.origin + '/quickmed';
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', newQuantity);

        const response = await fetch(siteUrl + '/ajax/update_cart.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to update cart',
            confirmButtonColor: '#065f46'
        });
    }
}

// Update quantity with +/- buttons
function updateQuantity(cartId, change, maxStock) {
    const input = document.getElementById(`qty-${cartId}`);
    if (!input) return;

    const currentQty = parseInt(input.value);
    const newQty = currentQty + change;

    updateCartQuantity(cartId, newQty, maxStock);
}

// Update quantity directly from input
function updateQuantityDirect(cartId, value, maxStock) {
    const newQty = parseInt(value);
    updateCartQuantity(cartId, newQty, maxStock);
}

// Remove item from cart
async function removeFromCart(cartId) {
    const result = await Swal.fire({
        title: 'Remove Item?',
        text: 'Are you sure you want to remove this item from cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#065f46',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const siteUrl = window.location.origin + '/quickmed';
            const formData = new FormData();
            formData.append('cart_id', cartId);

            const response = await fetch(siteUrl + '/ajax/remove_cart.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Removed!',
                    text: data.message,
                    confirmButtonColor: '#065f46',
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to remove item',
                confirmButtonColor: '#065f46'
            });
        }
    }
}

// Apply coupon code (future feature)
async function applyCoupon() {
    const couponCode = document.getElementById('couponCode')?.value.trim();
    
    if (!couponCode) {
        Swal.fire({
            icon: 'warning',
            title: 'Enter Coupon',
            text: 'Please enter a coupon code',
            confirmButtonColor: '#065f46'
        });
        return;
    }

    Swal.fire({
        icon: 'info',
        title: 'Coming Soon',
        text: 'Coupon feature will be available soon!',
        confirmButtonColor: '#065f46'
    });
}

// Clear entire cart
async function clearCart() {
    const result = await Swal.fire({
        title: 'Clear Cart?',
        text: 'Are you sure you want to remove all items from cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, clear all!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const siteUrl = window.location.origin + '/quickmed';
            const response = await fetch(siteUrl + '/ajax/clear_cart.php', {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cleared!',
                    text: 'Your cart has been cleared',
                    confirmButtonColor: '#065f46'
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to clear cart',
                confirmButtonColor: '#065f46'
            });
        }
    }
}

// Initialize cart page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart page loaded');
});