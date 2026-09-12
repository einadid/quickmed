<?php
require_once 'config.php';
$pageTitle = 'About Us - QuickMed';
$stats = $conn->query("SELECT (SELECT COUNT(*) FROM users) as users, (SELECT COUNT(*) FROM orders) as orders, (SELECT COUNT(*) FROM shops) as shops, (SELECT COUNT(*) FROM medicines) as medicines")->fetch_assoc();
$doctors = $conn->query("SELECT full_name, profile_image FROM users WHERE role_id = (SELECT id FROM roles WHERE name='doctor') LIMIT 4");
include 'includes/header.php';
?>
<?php qm_hero('Healthcare Redefined', "Bangladesh's most trusted digital healthcare platform — genuine medicine with technology and care.", 'Since 2025 · About Us', '🏥'); ?>
<section class="py-8 sm:py-12 md:py-16 bg-white relative">
    <div class="container mx-auto px-3 sm:px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-10 md:gap-12 items-center">
            <div data-aos="fade-right">
                <div class="relative">
                    <div class="absolute -top-4 -left-4 w-16 h-16 sm:w-20 sm:h-20 bg-lime-accent rounded-full opacity-15 blur-xl"></div>
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" class="rounded-xl shadow-lg border-4 border-white relative z-10 transform rotate-1 hover:rotate-0 transition duration-500 w-full">
                    <div class="absolute -bottom-4 -right-2 sm:-bottom-6 sm:-right-6 bg-deep-green text-white p-3 sm:p-4 rounded-xl shadow-lg z-20 max-w-[200px] sm:max-w-xs">
                        <p class="font-mono text-[11px] sm:text-xs leading-snug">"We don't just deliver medicine; we deliver hope and care to every doorstep."</p>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left" class="mt-6 md:mt-0">
                <h2 class="text-[1.3rem] sm:text-xl md:text-2xl font-bold text-deep-green mb-3 sm:mb-4 border-l-4 border-lime-accent pl-3">Our Mission</h2>
                <p class="text-gray-600 text-[0.85rem] sm:text-[0.92rem] mb-4 leading-relaxed">At QuickMed, our mission is simple: <strong class="text-deep-green">To make healthcare accessible, affordable, and authentic for everyone.</strong> We understand the struggle of finding genuine medicines, and we are here to solve it.</p>
                <ul class="space-y-2.5 text-gray-700 text-[0.82rem] sm:text-[0.88rem]">
                    <li class="flex items-center gap-2"><span class="text-base text-lime-600">✅</span><span>100% Authentic Medicines sourced directly from manufacturers.</span></li>
                    <li class="flex items-center gap-2"><span class="text-base text-lime-600">🚀</span><span>Fastest Delivery Network covering 64 districts.</span></li>
                    <li class="flex items-center gap-2"><span class="text-base text-lime-600">👨‍⚕️</span><span>Expert Pharmacist Supervision on every order.</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="py-8 sm:py-12 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>
    <div class="container mx-auto px-3 sm:px-4 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 text-center">
            <div data-aos="fade-up"><div class="text-2xl sm:text-3xl md:text-4xl font-bold font-mono text-lime-accent mb-1 counter" data-target="<?= $stats['medicines'] ?>">0</div><p class="uppercase tracking-widest text-[10px] sm:text-xs text-gray-400">Products</p></div>
            <div data-aos="fade-up" data-aos-delay="80"><div class="text-2xl sm:text-3xl md:text-4xl font-bold font-mono text-blue-400 mb-1 counter" data-target="<?= $stats['users'] ?>">0</div><p class="uppercase tracking-widest text-[10px] sm:text-xs text-gray-400">Happy Users</p></div>
            <div data-aos="fade-up" data-aos-delay="160"><div class="text-2xl sm:text-3xl md:text-4xl font-bold font-mono text-purple-400 mb-1 counter" data-target="<?= $stats['orders'] ?>">0</div><p class="uppercase tracking-widest text-[10px] sm:text-xs text-gray-400">Orders Delivered</p></div>
            <div data-aos="fade-up" data-aos-delay="240"><div class="text-2xl sm:text-3xl md:text-4xl font-bold font-mono text-red-400 mb-1 counter" data-target="<?= $stats['shops'] ?>">0</div><p class="uppercase tracking-widest text-[10px] sm:text-xs text-gray-400">Active Branches</p></div>
        </div>
    </div>
</section>
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="container mx-auto px-3 sm:px-4">
        <div class="text-center mb-6 sm:mb-10" data-aos="fade-up">
            <span class="text-lime-600 font-bold tracking-widest uppercase text-[10px] sm:text-xs">Meet The Experts</span>
            <h2 class="text-[1.3rem] sm:text-2xl font-bold text-deep-green mt-1">Our Medical Board</h2>
            <div class="w-12 h-0.5 bg-lime-accent mx-auto mt-2 rounded-full"></div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <?php while ($doc = $doctors->fetch_assoc()): $img = !empty($doc['profile_image']) ? SITE_URL . '/uploads/profiles/' . $doc['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($doc['full_name']); ?>
                <div class="bg-white p-4 sm:p-5 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300 text-center group" data-aos="fade-up">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 rounded-full p-0.5 border-2 border-lime-accent relative">
                        <img src="<?= $img ?>" class="w-full h-full rounded-full object-cover grayscale group-hover:grayscale-0 transition duration-500">
                        <div class="absolute bottom-0 right-0 bg-deep-green text-white text-[9px] p-0.5 rounded-full border border-white">🩺</div>
                    </div>
                    <h3 class="text-[0.85rem] sm:text-sm font-bold text-deep-green truncate"><?= htmlspecialchars($doc['full_name']) ?></h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">Senior Consultant</p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<section class="py-8 sm:py-12 bg-white border-t-2 border-lime-accent">
    <div class="container mx-auto px-3 sm:px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 text-center">
            <div class="p-4 sm:p-5 hover:bg-green-50 rounded-xl transition" data-aos="zoom-in"><div class="text-3xl sm:text-4xl mb-2">💊</div><h3 class="text-[1rem] sm:text-lg font-bold text-deep-green mb-1">Genuine Medicine</h3><p class="text-gray-600 text-[0.78rem] sm:text-[0.82rem]">Directly from manufacturer to your hand. No middleman, no fake.</p></div>
            <div class="p-4 sm:p-5 hover:bg-blue-50 rounded-xl transition" data-aos="zoom-in" data-aos-delay="80"><div class="text-3xl sm:text-4xl mb-2">🚚</div><h3 class="text-[1rem] sm:text-lg font-bold text-deep-green mb-1">Express Delivery</h3><p class="text-gray-600 text-[0.78rem] sm:text-[0.82rem]">We value your time. Get medicine delivered within hours.</p></div>
            <div class="p-4 sm:p-5 hover:bg-purple-50 rounded-xl transition" data-aos="zoom-in" data-aos-delay="160"><div class="text-3xl sm:text-4xl mb-2">📞</div><h3 class="text-[1rem] sm:text-lg font-bold text-deep-green mb-1">24/7 Support</h3><p class="text-gray-600 text-[0.78rem] sm:text-[0.82rem]">Our pharmacists are always available to answer queries.</p></div>
        </div>
    </div>
</section>
<script>
const counters = document.querySelectorAll('.counter');
counters.forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const increment = target / 50;
    const updateCount = () => {
        const count = +counter.innerText;
        if (count < target) { counter.innerText = Math.ceil(count + increment); setTimeout(updateCount, 30); }
        else { counter.innerText = target + '+'; }
    };
    updateCount();
});
</script>
<?php include 'includes/footer.php'; ?>
