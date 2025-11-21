<?php
/**
 * About Us - Ultra Dynamic & Detailed
 */
require_once 'config.php';
$pageTitle = 'About Us - QuickMed';

// Get Live Stats
$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM users) as users,
    (SELECT COUNT(*) FROM orders) as orders,
    (SELECT COUNT(*) FROM shops) as shops,
    (SELECT COUNT(*) FROM medicines) as medicines
")->fetch_assoc();

// Get Doctors Team
$doctors = $conn->query("SELECT full_name, profile_image FROM users WHERE role_id = (SELECT id FROM roles WHERE name='doctor') LIMIT 4");

include 'includes/header.php';
?>

<style>
    /* Background Animation */
    .floating-bg {
        position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 0; pointer-events: none;
    }
    .emoji {
        position: absolute; font-size: 3rem; opacity: 0.1; animation: float 15s infinite linear;
    }
    @keyframes float {
        0% { transform: translateY(100vh) rotate(0deg); }
        100% { transform: translateY(-10vh) rotate(360deg); }
    }
</style>

<!-- Hero Section -->
<section class="relative py-32 bg-deep-green text-white overflow-hidden">
    <!-- Animated Background -->
    <div class="floating-bg">
        <div class="emoji" style="left: 10%; animation-duration: 12s;">💊</div>
        <div class="emoji" style="left: 30%; animation-duration: 18s; font-size: 4rem;">💉</div>
        <div class="emoji" style="left: 50%; animation-duration: 15s;">🩸</div>
        <div class="emoji" style="left: 70%; animation-duration: 20s; font-size: 5rem;">🧬</div>
        <div class="emoji" style="left: 90%; animation-duration: 14s;">🩺</div>
    </div>

    <div class="container mx-auto px-4 text-center relative z-10" data-aos="zoom-in">
        <span class="bg-lime-accent text-deep-green px-6 py-2 rounded-full font-bold text-sm uppercase tracking-widest mb-6 inline-block shadow-[4px_4px_0px_white] transform -rotate-2">Since 2025</span>
        <h1 class="text-5xl md:text-7xl font-bold font-mono mb-6 leading-tight">Healthcare <br> Redefined</h1>
        <p class="text-xl md:text-2xl text-gray-200 max-w-3xl mx-auto leading-relaxed font-light">
            QuickMed is Bangladesh's most trusted digital healthcare platform, bridging the gap between patients and genuine medicine with technology and care.
        </p>
    </div>
</section>

<!-- Our Story & Mission -->
<section class="py-24 bg-white relative">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right">
                <div class="relative">
                    <div class="absolute -top-6 -left-6 w-24 h-24 bg-lime-accent rounded-full opacity-20 blur-xl"></div>
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" class="rounded-2xl shadow-2xl border-8 border-white relative z-10 transform rotate-2 hover:rotate-0 transition duration-500">
                    <div class="absolute -bottom-10 -right-10 bg-deep-green text-white p-6 rounded-xl shadow-xl z-20 max-w-xs">
                        <p class="font-mono text-sm">"We don't just deliver medicine; we deliver hope and care to every doorstep."</p>
                    </div>
                </div>
            </div>
            
            <div data-aos="fade-left">
                <h2 class="text-4xl font-bold text-deep-green mb-6 border-l-8 border-lime-accent pl-4">Our Mission</h2>
                <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                    At QuickMed, our mission is simple: <strong class="text-deep-green">To make healthcare accessible, affordable, and authentic for everyone.</strong> We understand the struggle of finding genuine medicines, and we are here to solve it.
                </p>
                <ul class="space-y-4 text-gray-700">
                    <li class="flex items-center gap-3">
                        <span class="text-2xl text-lime-600">✅</span>
                        <span>100% Authentic Medicines sourced directly from manufacturers.</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-2xl text-lime-600">🚀</span>
                        <span>Fastest Delivery Network covering 64 districts.</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-2xl text-lime-600">👨‍⚕️</span>
                        <span>Expert Pharmacist Supervision on every order.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Live Stats -->
<section class="py-20 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-aos="fade-up" data-aos-delay="0">
                <div class="text-5xl md:text-6xl font-bold font-mono text-lime-accent mb-2 counter" data-target="<?= $stats['medicines'] ?>">0</div>
                <p class="uppercase tracking-widest text-sm text-gray-400">Products</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="text-5xl md:text-6xl font-bold font-mono text-blue-400 mb-2 counter" data-target="<?= $stats['users'] ?>">0</div>
                <p class="uppercase tracking-widest text-sm text-gray-400">Happy Users</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="200">
                <div class="text-5xl md:text-6xl font-bold font-mono text-purple-400 mb-2 counter" data-target="<?= $stats['orders'] ?>">0</div>
                <p class="uppercase tracking-widest text-sm text-gray-400">Orders Delivered</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="300">
                <div class="text-5xl md:text-6xl font-bold font-mono text-red-400 mb-2 counter" data-target="<?= $stats['shops'] ?>">0</div>
                <p class="uppercase tracking-widest text-sm text-gray-400">Active Branches</p>
            </div>
        </div>
    </div>
</section>

<!-- Expert Team -->
<section class="py-24 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="text-lime-600 font-bold tracking-widest uppercase text-sm">Meet The Experts</span>
            <h2 class="text-4xl font-bold text-deep-green mt-2">Our Medical Board</h2>
            <div class="w-20 h-1 bg-lime-accent mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
            <?php while ($doc = $doctors->fetch_assoc()): 
                $img = !empty($doc['profile_image']) ? SITE_URL . '/uploads/profiles/' . $doc['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($doc['full_name']);
            ?>
                <div class="bg-white p-6 rounded-2xl shadow-lg hover:-translate-y-2 transition-all duration-300 text-center group" data-aos="fade-up">
                    <div class="w-32 h-32 mx-auto mb-6 rounded-full p-1 border-4 border-lime-accent relative">
                        <img src="<?= $img ?>" class="w-full h-full rounded-full object-cover grayscale group-hover:grayscale-0 transition duration-500">
                        <div class="absolute bottom-0 right-0 bg-deep-green text-white text-xs p-1 rounded-full border-2 border-white">🩺</div>
                    </div>
                    <h3 class="text-xl font-bold text-deep-green"><?= htmlspecialchars($doc['full_name']) ?></h3>
                    <p class="text-sm text-gray-500 mt-1">Senior Consultant</p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-20 bg-white border-t-4 border-lime-accent">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-8 text-center">
            <div class="p-8 hover:bg-green-50 rounded-xl transition" data-aos="zoom-in">
                <div class="text-6xl mb-4">💊</div>
                <h3 class="text-2xl font-bold text-deep-green mb-2">Genuine Medicine</h3>
                <p class="text-gray-600">Directly from manufacturer to your hand. No middleman, no fake products.</p>
            </div>
            <div class="p-8 hover:bg-blue-50 rounded-xl transition" data-aos="zoom-in" data-aos-delay="100">
                <div class="text-6xl mb-4">🚚</div>
                <h3 class="text-2xl font-bold text-deep-green mb-2">Express Delivery</h3>
                <p class="text-gray-600">We value your time. Get your medicine delivered within hours.</p>
            </div>
            <div class="p-8 hover:bg-purple-50 rounded-xl transition" data-aos="zoom-in" data-aos-delay="200">
                <div class="text-6xl mb-4">📞</div>
                <h3 class="text-2xl font-bold text-deep-green mb-2">24/7 Support</h3>
                <p class="text-gray-600">Our pharmacists are always available to answer your queries.</p>
            </div>
        </div>
    </div>
</section>

<script>
// Counter Animation
const counters = document.querySelectorAll('.counter');
counters.forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const increment = target / 50;
    
    const updateCount = () => {
        const count = +counter.innerText;
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(updateCount, 30);
        } else {
            counter.innerText = target + '+';
        }
    };
    updateCount();
});
</script>

<?php include 'includes/footer.php'; ?>