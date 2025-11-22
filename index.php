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


<section class="relative z-20 -mt-16 px-4">
    <div class="container mx-auto">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-white/20 p-8 md:p-12 relative overflow-hidden">
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-lime-accent/10 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-10">
                
                <div class="group relative p-6 rounded-2xl border border-transparent hover:border-lime-accent/30 hover:bg-green-50/50 transition-all duration-300" data-aos="fade-up" data-aos-delay="0">
                    <div class="absolute top-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="text-xs font-mono font-bold text-lime-600 bg-lime-100 px-2 py-1 rounded">VERIFIED</span>
                    </div>
                    <div class="w-16 h-16 bg-deep-green/10 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:bg-deep-green group-hover:text-white transition-all duration-300 shadow-sm relative">
                        👨‍⚕️
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lime-500 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-lime-500"></span>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold font-mono text-deep-green mb-2 group-hover:text-lime-600 transition-colors">Doctor Review</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Your health is serious. Every prescription is <span class="font-bold text-gray-700">double-checked</span> by our panel of registered doctors before dispatch.
                    </p>
                </div>

                <div class="group relative p-6 rounded-2xl border border-transparent hover:border-blue-200 hover:bg-blue-50/50 transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        ❄️
                    </div>
                    <h3 class="text-lg font-bold font-mono text-deep-green mb-2 group-hover:text-blue-600 transition-colors">Cold-Chain Storage</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Insulin & Vaccines are stored and delivered in specialized <span class="font-bold text-gray-700">temperature-controlled</span> boxes to maintain potency.
                    </p>
                </div>

                <div class="group relative p-6 rounded-2xl border border-transparent hover:border-purple-200 hover:bg-purple-50/50 transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        🛡️
                    </div>
                    <h3 class="text-lg font-bold font-mono text-deep-green mb-2 group-hover:text-purple-600 transition-colors">100% Authentic</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Zero tolerance for fakes. We source directly from top <span class="font-bold text-gray-700">GMP Certified</span> pharma manufacturers only.
                    </p>
                </div>

                <div class="group relative p-6 rounded-2xl border border-transparent hover:border-orange-200 hover:bg-orange-50/50 transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:bg-orange-500 group-hover:text-white transition-all duration-300 shadow-sm">
                        💊
                    </div>
                    <h3 class="text-lg font-bold font-mono text-deep-green mb-2 group-hover:text-orange-600 transition-colors">Expert Counseling</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Confused about dosage? Chat anytime with our <span class="font-bold text-gray-700">A-Grade Pharmacists</span> for free medication advice.
                    </p>
                </div>

            </div>
            
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-lime-accent to-transparent opacity-50"></div>
        </div>
    </div>
</section>

<?php include 'includes/homepage/health_blog.php'; ?>

<?php include 'includes/homepage/news.php'; ?>

<?php include 'includes/homepage/testimonials.php'; ?>

<section class="relative bg-[#022c22] py-20 overflow-hidden border-t-4 border-lime-accent">
    
    <div class="absolute inset-0 z-0 opacity-20 pointer-events-none" 
         style="background-image: linear-gradient(#065f46 1px, transparent 1px), linear-gradient(90deg, #065f46 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <svg class="absolute top-1/2 left-0 w-full h-32 -translate-y-1/2 z-0 opacity-30 pointer-events-none" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,60 L200,60 L220,30 L240,90 L260,10 L280,110 L300,60 L1200,60" 
              fill="none" stroke="#84cc16" stroke-width="2" 
              class="animate-dash" stroke-dasharray="1200" stroke-dashoffset="1200">
        </path>
    </svg>
    <style>
        .animate-dash { animation: dash 3s linear infinite; }
        @keyframes dash { to { stroke-dashoffset: 0; } }
        .blink-cursor { animation: blink 1s step-end infinite; }
    </style>

    <div class="container mx-auto px-4 relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
            
            <div class="lg:w-1/2 text-left" data-aos="fade-right">
                <div class="inline-flex items-center gap-2 bg-lime-accent/10 border border-lime-accent/30 rounded-full px-4 py-1 mb-6">
                    <span class="w-2 h-2 rounded-full bg-lime-accent animate-ping"></span>
                    <span class="text-lime-accent font-mono text-xs font-bold tracking-widest uppercase">System Online</span>
                </div>
                
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 font-mono leading-tight">
                    NEED <span class="text-lime-accent">EMERGENCY</span> HELP?
                </h2>
                
                <p class="text-gray-400 text-lg mb-8 font-mono leading-relaxed border-l-4 border-lime-accent pl-4">
                    Our certified pharmacists are standing by on the secured channel to assist with your prescriptions.
                </p>

                <div class="flex flex-wrap gap-4">
                    <a href="tel:09678100100" class="group relative bg-lime-accent text-deep-green px-8 py-4 font-bold font-mono tracking-wider hover:bg-white transition-all overflow-hidden">
                        <span class="relative z-10 flex items-center gap-2">
                            📞 CALL HOTLINE
                        </span>
                        <div class="absolute inset-0 bg-white translate-y-full group-hover:translate-y-0 transition-transform duration-300 z-0"></div>
                    </a>

                    <a href="contact.php" class="px-8 py-4 border-2 border-lime-accent text-lime-accent font-bold font-mono hover:bg-lime-accent/10 transition flex items-center gap-2">
                        <span>✉️</span> SEND MESSAGE
                    </a>
                </div>
            </div>

            <div class="lg:w-1/2 w-full" data-aos="fade-left">
                <div class="bg-black/60 backdrop-blur-md border-2 border-[#065f46] p-6 rounded-lg font-mono relative shadow-[0_0_20px_rgba(6,95,70,0.5)]">
                    <div class="flex gap-2 mb-4 border-b border-[#065f46] pb-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <div class="ml-auto text-[#065f46] text-xs">TERMINAL_V.2.0</div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-400">
                            <span>> CONNECTING TO SERVER...</span>
                            <span class="text-lime-accent">SUCCESS</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>> CHECKING PHARMACIST STATUS...</span>
                            <span class="text-lime-accent">ACTIVE</span>
                        </div>
                        
                        <hr class="border-[#065f46] opacity-50 my-2">

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="bg-deep-green/50 p-3 border border-lime-accent/20">
                                <span class="text-xs text-gray-400 block mb-1">PHARMACISTS ONLINE</span>
                                <span class="text-2xl text-lime-accent font-bold" id="pharmacist-count">08</span>
                            </div>
                            <div class="bg-deep-green/50 p-3 border border-lime-accent/20">
                                <span class="text-xs text-gray-400 block mb-1">AVG. RESPONSE TIME</span>
                                <span class="text-2xl text-yellow-400 font-bold">~2 MIN</span>
                            </div>
                        </div>

                        <div class="mt-4 text-lime-accent animate-pulse">
                            > WAITING FOR USER INPUT<span class="blink-cursor">_</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    // Simulate changing numbers for "Live" feel
    setInterval(() => {
        const countEl = document.getElementById('pharmacist-count');
        // Random number between 5 and 12
        const randomNum = Math.floor(Math.random() * (12 - 5 + 1) + 5);
        // Add leading zero
        countEl.innerText = randomNum < 10 ? '0' + randomNum : randomNum;
    }, 5000); // Updates every 5 seconds
</script>


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