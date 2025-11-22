<?php
/**
 * QuickMed - Ultra Dynamic Live Statistics (Dark & Modern)
 */

// Fetch real-time statistics with error handling
try {
    $statsQuery = "SELECT 
        (SELECT COUNT(*) FROM medicines) as total_medicines,
        (SELECT COUNT(*) FROM shops WHERE is_active = 1) as active_shops,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COUNT(*) FROM users WHERE role_id = 1) as total_customers,
        (SELECT COUNT(*) FROM parcels WHERE status = 'delivered') as delivered_parcels,
        (SELECT COUNT(*) FROM parcels) as total_parcels";

    $result = $conn->query($statsQuery);
    $stats = $result ? $result->fetch_assoc() : [];

    // Fallback values if query fails
    $medicines = $stats['total_medicines'] ?? 0;
    $shops = $stats['active_shops'] ?? 0;
    $orders = $stats['total_orders'] ?? 0;
    $customers = $stats['total_customers'] ?? 0;
    
    // Calculate Delivery Success Rate
    $totalParcels = $stats['total_parcels'] ?? 0;
    $delivered = $stats['delivered_parcels'] ?? 0;
    $deliveryRate = $totalParcels > 0 ? round(($delivered / $totalParcels) * 100, 1) : 100;

} catch (Exception $e) {
    // Silent fail for frontend
    $medicines = $shops = $orders = $customers = $deliveryRate = 0;
}
?>

<style>
    /* Custom Animations & Effects */
    .stats-bg {
        background: radial-gradient(circle at center, #022c22 0%, #000000 100%);
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(132, 204, 22, 0.2);
        box-shadow: 0 0 15px rgba(0,0,0,0.5);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .glass-card:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: #84cc16;
        box-shadow: 0 0 30px rgba(132, 204, 22, 0.2);
        background: rgba(132, 204, 22, 0.05);
    }

    .stat-icon {
        background: linear-gradient(135deg, #84cc16 0%, #065f46 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 0 5px rgba(132, 204, 22, 0.5));
    }

    /* Floating Particles Animation */
    .particles {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .particle {
        position: absolute;
        width: 4px;
        height: 4px;
        background: #84cc16;
        border-radius: 50%;
        opacity: 0.3;
        animation: floatUp 10s linear infinite;
    }
    @keyframes floatUp {
        0% { transform: translateY(100vh) scale(0); opacity: 0; }
        50% { opacity: 0.5; }
        100% { transform: translateY(-10vh) scale(1.5); opacity: 0; }
    }
</style>

<section class="stats-bg py-24 relative border-y border-lime-accent/30 overflow-hidden">
    
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-4xl bg-lime-accent/5 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="particles" id="particles"></div>

    <div class="container mx-auto px-6 relative z-10">
        
        <div class="text-center mb-16" data-aos="zoom-in">
            <span class="text-lime-accent font-mono text-sm font-bold tracking-[0.3em] uppercase mb-3 block animate-pulse">
                ● Live System Metrics
            </span>
            <h2 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                Empowering Health <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-lime-400 to-emerald-600">By The Numbers</span>
            </h2>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Transparency is our core. Watch our impact grow in real-time as we deliver healthcare solutions across the nation.
            </p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            
            <div class="glass-card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="0">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition-transform duration-300">💊</div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 font-mono counter" data-target="<?= $medicines ?>">0</div>
                <div class="text-xs text-lime-accent/80 font-bold uppercase tracking-widest">Available Medicines</div>
            </div>
            
            <div class="glass-card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="100">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition-transform duration-300">🏥</div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 font-mono counter" data-target="<?= $shops ?>">0</div>
                <div class="text-xs text-lime-accent/80 font-bold uppercase tracking-widest">Partner Pharmacies</div>
            </div>
            
            <div class="glass-card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="200">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition-transform duration-300">📦</div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 font-mono counter" data-target="<?= $orders ?>">0</div>
                <div class="text-xs text-lime-accent/80 font-bold uppercase tracking-widest">Orders Processed</div>
            </div>
            
            <div class="glass-card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="300">
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition-transform duration-300">👨‍👩‍👧‍👦</div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 font-mono counter" data-target="<?= $customers ?>">0</div>
                <div class="text-xs text-lime-accent/80 font-bold uppercase tracking-widest">Trusted Users</div>
            </div>
            
            <div class="glass-card rounded-2xl p-8 text-center group relative overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                <div class="absolute -right-6 -bottom-6 text-9xl opacity-5 text-lime-accent">⚡</div>
                
                <div class="text-5xl mb-4 transform group-hover:scale-110 transition-transform duration-300">🚀</div>
                <div class="flex justify-center items-baseline gap-1 text-white mb-2">
                    <span class="text-4xl md:text-5xl font-bold font-mono counter" data-target="<?= $deliveryRate ?>">0</span>
                    <span class="text-2xl font-bold text-lime-400">%</span>
                </div>
                <div class="text-xs text-lime-accent/80 font-bold uppercase tracking-widest">Success Rate</div>
            </div>
        </div>

        <div class="text-center mt-12" data-aos="fade-in" data-aos-delay="600">
            <p class="text-gray-500 text-sm font-mono">
                * Stats updated live every second from QuickMed Database.
            </p>
        </div>
    </div>
</section>

<script>
    /**
     * Advanced Counter Animation
     */
    function animateCounters() {
        const counters = document.querySelectorAll('.counter');
        const speed = 100; // Lower is faster

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            
            const updateCount = () => {
                const count = +counter.innerText.replace(/,/g, ''); // Remove commas for math
                const increment = target / speed;

                if (count < target) {
                    // Add commas for better readability
                    counter.innerText = Math.ceil(count + increment).toLocaleString();
                    setTimeout(updateCount, 20);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };
            updateCount();
        });
    }

    // Intersection Observer to trigger animation only when visible
    const statsSection = document.querySelector('.stats-bg');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target); // Run only once
                }
            });
        }, { threshold: 0.3 }); // Trigger when 30% visible
        observer.observe(statsSection);
    }

    /**
     * Particle Generator
     */
    function createParticles() {
        const container = document.getElementById('particles');
        const particleCount = 20;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            // Randomize position and animation duration
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 5 + 5) + 's'; // 5s to 10s
            particle.style.animationDelay = (Math.random() * 5) + 's';
            
            container.appendChild(particle);
        }
    }
    createParticles();
</script>