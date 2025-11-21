/**
 * QuickMed - Main JavaScript - FIXED
 */
/**
 * Add to Cart Function (FIXED)
 */
async function addToCart(medicineId, shopId, quantity) {
    quantity = quantity || 1;

    // Get Base URL dynamically
    const siteUrl = window.location.origin + '/quickmed';

    try {
        const formData = new FormData();
        formData.append('medicine_id', medicineId);
        formData.append('shop_id', shopId);
        formData.append('quantity', quantity);

        const response = await fetch(`${siteUrl}/ajax/add_to_cart.php`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Show Success Message
            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: 'Item added to your cart.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#065f46',
                color: '#fff'
            });

            // Update Cart Badge
            updateCartCount(result.cart_count);
        } else {
            if (result.message === 'login_required') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required',
                    text: 'Please login to shop.',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    confirmButtonColor: '#065f46'
                }).then((res) => {
                    if (res.isConfirmed) window.location.href = `${siteUrl}/login.php`;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: result.message,
                    confirmButtonColor: '#065f46'
                });
            }
        }
    } catch (error) {
        console.error('Cart Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong. Please try again.',
            confirmButtonColor: '#065f46'
        });
    }
}

// Update Cart Badges in Navbar (Desktop & Mobile)
function updateCartCount(count) {
    const badges = document.querySelectorAll('.cart-count, .absolute.-top-2, .absolute.top-1');
    badges.forEach(badge => {
        badge.innerText = count;
        badge.classList.remove('hidden');
        badge.classList.add('animate-bounce');
        setTimeout(() => badge.classList.remove('animate-bounce'), 1000);
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