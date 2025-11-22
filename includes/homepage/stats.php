<?php
/**
 * 📊 QuickMed - Modern Live Analytics Section
 */

// --- 1. DATA FETCHING (Keep logic same, it works well) ---
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

    $medicines = $stats['total_medicines'] ?? 0;
    $shops = $stats['active_shops'] ?? 0;
    $orders = $stats['total_orders'] ?? 0;
    $customers = $stats['total_customers'] ?? 0;
    
    $totalParcels = $stats['total_parcels'] ?? 0;
    $delivered = $stats['delivered_parcels'] ?? 0;
    $deliveryRate = $totalParcels > 0 ? round(($delivered / $totalParcels) * 100, 1) : 100;

} catch (Exception $e) {
    $medicines = $shops = $orders = $customers = $deliveryRate = 0;
}
?>

<style>
    /* Scoped Styles for this section */
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    /* Hover Effect: Glow & Lift */
    .stat-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.07);
        border-color: rgba(132, 204, 22, 0.3); /* Lime-500 equivalent */
        box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.3), 0 0 15px rgba(132, 204, 22, 0.1);
    }

    /* Gradient Text for Numbers */
    .text-gradient {
        background: linear-gradient(to right, #ffffff, #a3e635);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Background Ambient Glow */
    .ambient-glow {
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.2;
        z-index: 0;
        pointer-events: none;
    }
</style>

<section class="relative bg-[#0f172a] py-20 overflow-hidden border-y border-slate-800">
    
    <div class="ambient-glow bg-emerald-500 top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="ambient-glow bg-lime-500 bottom-0 right-0 translate-x-1/2 translate-y-1/2"></div>
    
    <div class="absolute inset-0 opacity-[0.03]" 
         style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 30px 30px;">
    </div>

    <div class="container mx-auto px-4 relative z-10">
        
        <div class="text-center mb-16 max-w-2xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-lime-900/30 border border-lime-500/30 text-lime-400 text-xs font-bold uppercase tracking-wider mb-4 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-lime-500"></span> Live Metrics
            </div>
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">
                QuickMed <span class="text-lime-400">Ecosystem</span>
            </h2>
            <p class="text-slate-400 text-sm md:text-base">
                Real-time visibility into our healthcare network's performance and reach across the country.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
            
            <div class="stat-card rounded-2xl p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-blue-500/10 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-white mb-1 counter" data-target="<?= $medicines ?>">0</div>
                <div class="text-sm text-slate-400 font-medium">Available Medicines</div>
            </div>

            <div class="stat-card rounded-2xl p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-purple-500/10 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-white mb-1 counter" data-target="<?= $shops ?>">0</div>
                <div class="text-sm text-slate-400 font-medium">Active Pharmacies</div>
            </div>

            <div class="stat-card rounded-2xl p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-white mb-1 counter" data-target="<?= $orders ?>">0</div>
                <div class="text-sm text-slate-400 font-medium">Orders Completed</div>
            </div>

            <div class="stat-card rounded-2xl p-6 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-orange-500/10 text-orange-400 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-white mb-1 counter" data-target="<?= $customers ?>">0</div>
                <div class="text-sm text-slate-400 font-medium">Happy Customers</div>
            </div>

            <div class="stat-card rounded-2xl p-6 group border-lime-500/30 bg-lime-500/5">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-lime-400 text-slate-900 shadow-[0_0_15px_rgba(163,230,53,0.5)]">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1">
                    <div class="text-4xl font-bold text-white mb-1 counter" data-target="<?= $deliveryRate ?>">0</div>
                    <span class="text-lg font-bold text-lime-400">%</span>
                </div>
                <div class="text-sm text-lime-100/70 font-medium">Delivery Success</div>
                
                <div class="w-full bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                    <div class="h-full bg-lime-400 rounded-full" style="width: <?= $deliveryRate ?>%"></div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    /**
     * Optimized Counter Animation
     * Uses IntersectionObserver for performance
     */
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.counter');
        
        const observerOptions = {
            threshold: 0.5 // Start when 50% of the element is visible
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    const duration = 1500; // Animation duration in ms
                    const increment = target / (duration / 16); // 60fps

                    let current = 0;
                    
                    const updateCount = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current).toLocaleString();
                            requestAnimationFrame(updateCount);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };

                    updateCount();
                    observer.unobserve(counter); // Only run once
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));
    });
</script>