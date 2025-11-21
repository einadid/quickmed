<?php
/**
 * QuickMed - Enhanced Global Footer
 */

// Set Timezone for Dynamic Status
date_default_timezone_set('Asia/Dhaka');
$currentHour = date('H');
$isOpen = ($currentHour >= 9 && $currentHour < 22); // Open between 9 AM and 10 PM
?>

<div class="mt-24">
    <svg class="w-full h-16 fill-deep-green bg-transparent" viewBox="0 0 1440 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 48H1440V0C1440 0 1140 48 720 48C300 48 0 0 0 0V48Z" fill="currentColor"/>
    </svg>

    <footer class="bg-deep-green text-white pt-12 pb-6 relative overflow-hidden">
        
        <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>

        <div class="container mx-auto px-4 relative z-10">
            
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 mb-12 border border-white/10 shadow-lg" data-aos="fade-up">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="text-center md:text-left">
                        <h3 class="text-2xl font-bold text-lime-accent mb-2">🚀 Join the Healthy Family!</h3>
                        <p class="text-gray-200 text-sm">Get health tips and exclusive discounts sent to your inbox.</p>
                    </div>
                    <form class="flex w-full md:w-auto gap-2" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                        <input type="email" placeholder="Enter your email" class="px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-4 focus:ring-lime-accent/50 w-full md:w-80" required>
                        <button type="submit" class="bg-lime-accent text-deep-green font-bold px-6 py-3 rounded-lg hover:bg-white transition-all shadow-lg transform hover:scale-105">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 border-b border-white/20 pb-12">
                
                <div data-aos="fade-up">
                    <h3 class="text-3xl font-mono font-bold text-white mb-4 flex items-center gap-2">
                        🏥 QuickMed
                    </h3>
                    <p class="text-sm text-gray-300 leading-relaxed mb-4">
                        Bangladesh's most trusted digital healthcare platform. We ensure 100% authentic medicine delivery right to your doorstep.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <?php $socials = ['facebook', 'twitter', 'instagram', 'youtube']; ?>
                        <?php foreach($socials as $social): ?>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-lime-accent hover:text-deep-green transition-all duration-300 transform hover:-translate-y-1">
                            <i class="fab fa-<?= $social ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-lime-accent font-bold text-lg mb-6 uppercase tracking-wider">Company</h3>
                    <ul class="space-y-3 text-sm">
                        <?php 
                        $links = [
                            'about.php' => 'About Us',
                            'contact.php' => 'Contact Support',
                            'terms.php' => 'Terms of Service',
                            'privacy.php' => 'Privacy Policy',
                            'faq.php' => 'FAQs'
                        ];
                        foreach($links as $url => $label): 
                        ?>
                        <li>
                            <a href="<?= SITE_URL ?>/<?= $url ?>" class="text-gray-300 hover:text-lime-accent transition-all flex items-center gap-2 group">
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity">→</span> <?= $label ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-lime-accent font-bold text-lg mb-6 uppercase tracking-wider">Top Locations</h3>
                    <ul class="space-y-4 text-sm">
                        <?php
                        // Limit to 3 shops for aesthetics
                        $shopsQuery = "SELECT * FROM shops WHERE is_active = 1 ORDER BY name LIMIT 3";
                        $shopsResult = $conn->query($shopsQuery);
                        if ($shopsResult->num_rows > 0):
                            while ($shop = $shopsResult->fetch_assoc()):
                        ?>
                            <li class="flex items-start gap-3">
                                <span class="text-xl">📍</span>
                                <div>
                                    <span class="font-bold block text-white"><?= htmlspecialchars($shop['city']) ?> Branch</span>
                                    <a href="tel:<?= htmlspecialchars($shop['phone']) ?>" class="text-xs text-gray-400 hover:text-white transition-colors">
                                        <?= htmlspecialchars($shop['phone']) ?>
                                    </a>
                                </div>
                            </li>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <li class="text-gray-400 italic">No physical stores listed yet.</li>
                        <?php endif; ?>
                    </ul>
                    <a href="<?= SITE_URL ?>/contact.php" class="inline-block mt-4 text-xs font-bold text-lime-accent hover:underline border-b border-dashed border-lime-accent pb-1">
                        View all locations
                    </a>
                </div>
                
                <div data-aos="fade-up" data-aos-delay="300">
                    <h3 class="text-lime-accent font-bold text-lg mb-6 uppercase tracking-wider">Customer Care</h3>
                    
                    <div class="bg-white/5 rounded-lg p-4 mb-4 border border-white/10">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $isOpen ? 'bg-green-400' : 'bg-red-400' ?> opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 <?= $isOpen ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                            </span>
                            <span class="font-bold text-sm <?= $isOpen ? 'text-green-400' : 'text-red-400' ?>">
                                <?= $isOpen ? 'Support Online' : 'Support Offline' ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">We are available 9 AM - 10 PM daily.</p>
                    </div>

                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center gap-3">
                            <span class="bg-lime-accent text-deep-green w-8 h-8 rounded-full flex items-center justify-center font-bold">📞</span>
                            <div>
                                <span class="block text-xs text-gray-400">Hotline</span>
                                <span class="font-bold text-lg">16247</span>
                            </div>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="bg-lime-accent text-deep-green w-8 h-8 rounded-full flex items-center justify-center font-bold">📧</span>
                            <a href="mailto:help@quickmed.com" class="hover:text-lime-accent transition-colors">help@quickmed.com</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-400">
                <div class="text-center md:text-left">
                    <p>&copy; <?= date('Y') ?> QuickMed. All rights reserved.</p>
                    <p class="text-xs mt-1">License No: DGDA-1234-5678 | Developed with ❤️ in Bangladesh</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="text-xs mr-2">We Accept:</span>
                    <div class="flex gap-2 grayscale hover:grayscale-0 transition-all duration-500">
                        <div class="h-8 w-12 bg-white rounded flex items-center justify-center text-deep-green font-bold text-[10px]">VISA</div>
                        <div class="h-8 w-12 bg-pink-600 rounded flex items-center justify-center text-white font-bold text-[10px]">bKash</div>
                        <div class="h-8 w-12 bg-orange-500 rounded flex items-center justify-center text-white font-bold text-[10px]">Nagad</div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

<button id="scrollToTop" class="fixed bottom-8 right-8 transform translate-y-20 opacity-0 transition-all duration-500 z-50 group" aria-label="Scroll to top">
    <div class="relative w-12 h-12 bg-lime-accent rounded-full flex items-center justify-center shadow-lg hover:scale-110 hover:bg-white transition-all">
        <span class="text-2xl group-hover:-translate-y-1 transition-transform duration-300">☝️</span>
        </div>
</button>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true,
        offset: 50
    });
    
    // Enhanced Scroll to top functionality
    const scrollBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.remove('translate-y-20', 'opacity-0');
        } else {
            scrollBtn.classList.add('translate-y-20', 'opacity-0');
        }
    });
    
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

</body>
</html>