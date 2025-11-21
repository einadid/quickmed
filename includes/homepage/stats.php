<?php
/**
 * Live Statistics Counter
 */

// Get real statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM medicines) as total_medicines,
    (SELECT COUNT(*) FROM shops WHERE is_active = 1) as active_shops,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COUNT(*) FROM users WHERE role_id = 1) as total_customers,
    (SELECT COUNT(*) FROM parcels WHERE status = 'delivered') as delivered_parcels,
    (SELECT COUNT(*) FROM parcels) as total_parcels";

$stats = $conn->query($statsQuery)->fetch_assoc();
$deliveryRate = $stats['total_parcels'] > 0 ? round(($stats['delivered_parcels'] / $stats['total_parcels']) * 100, 1) : 0;
?>

<section class="bg-green text-white py-16 border-y-4 border-lime-accent relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: repeating-linear-gradient(0deg, transparent, transparent 50px, rgba(132, 204, 22, 0.3) 50px, rgba(132, 204, 22, 0.3) 100px);"></div>
    </div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl font-bold mb-4 uppercase">
                📊 QuickMed in Numbers 📊
            </h2>
            <p class="text-xl">Our Journey of Excellence</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <!-- Total Medicines -->
            <div class="bg-white bg-opacity-10 border-4 border-lime-accent p-6 text-center backdrop-blur-sm" data-aos="flip-up" data-aos-delay="0">
                <div class="text-5xl mb-3">💊</div>
                <div class="text-4xl font-bold mb-2 counter" data-target="<?= $stats['total_medicines'] ?>">0</div>
                <div class="text-lg font-bold uppercase"><?= __('total_medicines') ?></div>
            </div>
            
            <!-- Active Shops -->
            <div class="bg-white bg-opacity-10 border-4 border-lime-accent p-6 text-center backdrop-blur-sm" data-aos="flip-up" data-aos-delay="100">
                <div class="text-5xl mb-3">🏪</div>
                <div class="text-4xl font-bold mb-2 counter" data-target="<?= $stats['active_shops'] ?>">0</div>
                <div class="text-lg font-bold uppercase"><?= __('active_shops') ?></div>
            </div>
            
            <!-- Total Orders -->
            <div class="bg-white bg-opacity-10 border-4 border-lime-accent p-6 text-center backdrop-blur-sm" data-aos="flip-up" data-aos-delay="200">
                <div class="text-5xl mb-3">📦</div>
                <div class="text-4xl font-bold mb-2 counter" data-target="<?= $stats['total_orders'] ?>">0</div>
                <div class="text-lg font-bold uppercase"><?= __('total_orders') ?></div>
            </div>
            
            <!-- Happy Customers -->
            <div class="bg-white bg-opacity-10 border-4 border-lime-accent p-6 text-center backdrop-blur-sm" data-aos="flip-up" data-aos-delay="300">
                <div class="text-5xl mb-3">😊</div>
                <div class="text-4xl font-bold mb-2 counter" data-target="<?= $stats['total_customers'] ?>">0</div>
                <div class="text-lg font-bold uppercase"><?= __('happy_customers') ?></div>
            </div>
            
            <!-- Delivery Success -->
            <div class="bg-white bg-opacity-10 border-4 border-lime-accent p-6 text-center backdrop-blur-sm" data-aos="flip-up" data-aos-delay="400">
                <div class="text-5xl mb-3">✅</div>
                <div class="text-4xl font-bold mb-2">
                    <span class="counter" data-target="<?= $deliveryRate ?>">0</span>%
                </div>
                <div class="text-lg font-bold uppercase"><?= __('delivery_success') ?></div>
            </div>
        </div>
    </div>
</section>

<script>
// Animated Counter
function animateCounter() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // Animation speed
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const increment = target / speed;
        
        const updateCount = () => {
            const count = +counter.innerText;
            
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

// Trigger animation when section is visible
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounter();
            observer.unobserve(entry.target);
        }
    });
});

const statsSection = document.querySelector('.counter')?.closest('section');
if (statsSection) {
    observer.observe(statsSection);
}
</script>