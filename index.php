<?php
/**
 * QuickMed - Homepage (Ultra Modern)
 * Main landing page with dynamic sections and responsive layout
 */

require_once 'config.php';

$pageTitle = 'QuickMed - Your Trusted Online Pharmacy | আপনার বিশ্বস্ত অনলাইন ফার্মেসি';
$pageDescription = 'Buy genuine medicines online with home delivery across Bangladesh. Best prices, fast delivery, 100% authentic products.';

include 'includes/header.php';
?>

<?php include 'includes/homepage/hero.php'; ?>

<?php include 'includes/homepage/stats.php'; ?>

<div class="mt-16">
    <?php include 'includes/homepage/categories.php'; ?>
</div>

<section class="py-24 bg-gray-50 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none">
        <div class="absolute top-10 left-10 text-9xl transform -rotate-12">📄</div>
        <div class="absolute bottom-10 right-10 text-9xl transform rotate-12">📱</div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="text-lime-600 font-bold tracking-widest uppercase text-sm bg-lime-100 px-3 py-1 rounded-full">Easy Process</span>
            <h2 class="text-4xl md:text-5xl font-bold text-deep-green mt-4 mb-4 font-mono">Order via Prescription?</h2>
            <div class="w-24 h-1.5 bg-lime-accent mx-auto rounded-full"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-8 text-center max-w-6xl mx-auto">
            <div class="bg-white p-8 rounded-2xl shadow-lg border-b-4 border-lime-accent relative transform hover:-translate-y-2 transition duration-300" data-aos="fade-right">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-deep-green text-white flex items-center justify-center rounded-full font-bold text-xl border-4 border-white shadow-md">1</div>
                <div class="text-6xl mb-6 mt-4">📸</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Upload Photo</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Take a clear photo of your doctor's prescription and upload it to our system.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-lg border-b-4 border-lime-accent relative transform hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-deep-green text-white flex items-center justify-center rounded-full font-bold text-xl border-4 border-white shadow-md">2</div>
                <div class="text-6xl mb-6 mt-4">👨‍⚕️</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Doctor Review</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Our certified pharmacists and doctors will review and verify your medicine list.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-lg border-b-4 border-lime-accent relative transform hover:-translate-y-2 transition duration-300" data-aos="fade-left">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-deep-green text-white flex items-center justify-center rounded-full font-bold text-xl border-4 border-white shadow-md">3</div>
                <div class="text-6xl mb-6 mt-4">🚚</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Fast Delivery</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Once confirmed, get your medicines delivered to your doorstep within hours.</p>
            </div>
        </div>

        <div class="text-center mt-16" data-aos="zoom-in">
            <a href="prescription-upload.php" class="btn btn-primary px-10 py-4 text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all neon-border flex items-center justify-center gap-3 inline-flex">
                <span class="text-2xl">📤</span> Upload Prescription Now
            </a>
        </div>
    </div>
</section>

<?php include 'includes/homepage/flash_sale.php'; ?>

<?php include 'includes/homepage/featured.php'; ?>

<section class="py-24 bg-deep-green text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
        </svg>
    </div>
    
    <div class="container mx-auto px-4 grid md:grid-cols-2 gap-12 items-center relative z-10">
        <div data-aos="fade-right">
            <span class="text-lime-accent font-bold tracking-widest uppercase text-sm border-b-2 border-lime-accent pb-1">Who We Are</span>
            <h2 class="text-4xl md:text-5xl font-bold font-mono mt-6 mb-6 leading-tight">Your Digital Healthcare Partner</h2>
            <p class="text-gray-200 text-lg leading-relaxed mb-8 font-light">
                QuickMed is committed to providing accessible, affordable, and authentic healthcare solutions. From genuine medicines to expert consultations, we bridge the gap between you and better health with technology and care.
            </p>
            
            <ul class="space-y-3 mb-8 text-gray-300">
                <li class="flex items-center gap-3">✅ <span class="font-bold text-white">24/7 Support</span> for all your queries</li>
                <li class="flex items-center gap-3">✅ <span class="font-bold text-white">Licensed Pharmacy</span> with certified pharmacists</li>
                <li class="flex items-center gap-3">✅ <span class="font-bold text-white">Nationwide Coverage</span> including rural areas</li>
            </ul>

            <a href="about.php" class="btn bg-transparent border-2 border-lime-accent text-lime-accent hover:bg-lime-accent hover:text-deep-green px-8 py-3 font-bold text-lg rounded-lg transition-all duration-300">
                Learn More About Us →
            </a>
        </div>
        
        <div class="relative" data-aos="fade-left">
            <div class="absolute inset-0 bg-lime-accent rounded-full blur-[100px] opacity-20 animate-pulse"></div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-8 rounded-2xl shadow-2xl text-center">
                <div class="text-9xl mb-4 animate-bounce">🏥</div>
                <h3 class="text-3xl font-bold text-lime-accent">Since 2025</h3>
                <p class="text-gray-300">Serving Bangladesh with pride</p>
            </div>
        </div>
    </div>
</section>


<section class="relative z-20 -mt-10 px-4">
    <div class="container mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl border-b-4 border-lime-accent p-8 md:p-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <div class="text-center group" data-aos="fade-up" data-aos-delay="0">
                    <div class="w-20 h-20 mx-auto bg-green-50 rounded-full flex items-center justify-center text-4xl mb-4 group-hover:scale-110 transition duration-500 shadow-sm">
                        ✅
                    </div>
                    <h3 class="text-xl font-bold text-deep-green mb-2">100% Genuine</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Sourced directly from authorized manufacturers with warranty.</p>
                </div>

                <div class="text-center group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-20 h-20 mx-auto bg-blue-50 rounded-full flex items-center justify-center text-4xl mb-4 group-hover:scale-110 transition duration-500 shadow-sm">
                        🚚
                    </div>
                    <h3 class="text-xl font-bold text-deep-green mb-2">Fast Delivery</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Express home delivery within 24-48 hours nationwide.</p>
                </div>

                <div class="text-center group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-20 h-20 mx-auto bg-purple-50 rounded-full flex items-center justify-center text-4xl mb-4 group-hover:scale-110 transition duration-500 shadow-sm">
                        🔒
                    </div>
                    <h3 class="text-xl font-bold text-deep-green mb-2">Secure Payment</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">100% safe digital payments and Cash on Delivery.</p>
                </div>

                <div class="text-center group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-20 h-20 mx-auto bg-yellow-50 rounded-full flex items-center justify-center text-4xl mb-4 group-hover:scale-110 transition duration-500 shadow-sm">
                        💰
                    </div>
                    <h3 class="text-xl font-bold text-deep-green mb-2">Best Prices</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Competitive pricing with regular discounts and offers.</p>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include 'includes/homepage/health_blog.php'; ?>

<?php include 'includes/homepage/news.php'; ?>

<?php include 'includes/homepage/testimonials.php'; ?>

<section class="py-16 bg-lime-accent text-deep-green text-center relative overflow-hidden">
    <div class="container mx-auto px-4 relative z-10" data-aos="zoom-in">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Still have questions?</h2>
        <p class="text-lg mb-8 font-medium">Our pharmacists are ready to help you.</p>
        <div class="flex justify-center gap-4">
            <a href="contact.php" class="bg-deep-green text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition shadow-lg">Contact Us</a>
            <a href="tel:09678100100" class="bg-white text-deep-green px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition shadow-lg">📞 Call 09678-100100</a>
        </div>
    </div>
</section>


<!-- CART SCRIPT (Directly Embedded to Fix ReferenceError) -->
<script>
async function addToCart(medicineId, shopId, quantity) {
    quantity = quantity || 1;
    const siteUrl = '<?= SITE_URL ?>'; // Get dynamic URL from PHP

    try {
        const formData = new FormData();
        formData.append('medicine_id', medicineId);
        formData.append('shop_id', shopId);
        formData.append('quantity', quantity);

        const response = await fetch(siteUrl + '/ajax/add_to_cart.php', {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            throw new Error('Server Error: ' + text);
        }

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: 'Item added to cart successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#065f46',
                color: '#fff'
            });

            // Update Cart Badge
            const badges = document.querySelectorAll('.cart-count, .absolute.-top-2');
            badges.forEach(b => {
                b.innerText = result.cart_count;
                b.classList.remove('hidden');
                b.classList.add('animate-bounce');
            });

        } else {
            if (result.message === 'login_required') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required',
                    text: 'Please login to continue shopping.',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    confirmButtonColor: '#065f46'
                }).then((res) => {
                    if(res.isConfirmed) window.location.href = siteUrl + '/login.php';
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
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong. Check console.',
            confirmButtonColor: '#065f46'
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>