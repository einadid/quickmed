/**
 * QuickMed - Main JavaScript - FIXED
 */

// Add to Cart Function
async function addToCart(medicineId, shopId, quantity) {
    if (!medicineId || !shopId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Invalid product data',
            confirmButtonColor: '#065f46'
        });
        return;
    }

    quantity = quantity || 1;

    try {
        const siteUrl = window.location.origin + '/quickmed';
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
                title: 'Added to Cart!',
                text: result.message,
                confirmButtonColor: '#065f46',
                timer: 2000
            });

            // Update cart count in navbar
            if (result.cart_count) {
                updateCartCount(result.cart_count);
            }
        } else {
            if (result.message.includes('login')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required',
                    text: result.message,
                    confirmButtonColor: '#065f46',
                    confirmButtonText: 'Login Now'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = siteUrl + '/login.php';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message,
                    confirmButtonColor: '#065f46'
                });
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to add item to cart',
            confirmButtonColor: '#065f46'
        });
    }
}

// Update cart count in navbar
function updateCartCount(count) {
    // Find all cart count elements
    const cartBadges = document.querySelectorAll('[class*="cart-count"], .absolute.-top-2.-right-2');
    cartBadges.forEach(function(badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('animate-bounce');
            setTimeout(function() {
                badge.classList.remove('animate-bounce');
            }, 1000);
        }
    });
}

// Smooth scroll to top
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scrollToTop');
    if (scrollBtn) {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.remove('hidden');
        } else {
            scrollBtn.classList.add('hidden');
        }
    }
});

// Counter animation
function animateCounter() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200;
    
    counters.forEach(function(counter) {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / speed;
        
        const updateCount = function() {
            const count = parseInt(counter.innerText);
            
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 10);
            } else {
                counter.innerText = target;
            }
        };
        
        updateCount();
    });
}

// Intersection Observer for counters
const observerOptions = {
    threshold: 0.5
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            animateCounter();
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

const statsSection = document.querySelector('.counter');
if (statsSection) {
    const parent = statsSection.closest('section');
    if (parent) {
        observer.observe(parent);
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    console.log('QuickMed loaded successfully!');
    
    // Add loading animation to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="animate-spin inline-block">⏳</span> Processing...';
                submitBtn.disabled = true;
                
                // Re-enable after 10 seconds as fallback
                setTimeout(function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });
});

// Prescription form submit
const prescriptionForm = document.getElementById('prescriptionForm');
if (prescriptionForm) {
    prescriptionForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="animate-spin">⏳</span> Uploading...';
        
        try {
            const siteUrl = window.location.origin + '/quickmed';
            const response = await fetch(siteUrl + '/ajax/upload_prescription.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message,
                    confirmButtonColor: '#065f46'
                }).then(function() {
                    closePrescriptionModal();
                    prescriptionForm.reset();
                    const preview = document.getElementById('imagePreview');
                    if (preview) {
                        preview.classList.add('hidden');
                    }
                });
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to upload prescription',
                confirmButtonColor: '#065f46'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '📤 UPLOAD PRESCRIPTION';
        }
    });
}