<?php
/**
 * QuickMed - Global Footer
 */
?>

<!-- Footer -->
<footer class="bg-green text-white mt-16 border-t-4 border-lime-accent">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div data-aos="fade-up">
                <h3 class="text-lime-accent font-bold text-xl mb-4 uppercase">QuickMed</h3>
                <p class="text-sm leading-relaxed mb-4">
                    Your trusted online pharmacy delivering genuine medicines across Bangladesh since 2020.
                </p>
                <p class="text-sm">
                    আপনার বিশ্বস্ত অনলাইন ফার্মেসি যা ২০২০ সাল থেকে বাংলাদেশ জুড়ে খাঁটি ওষুধ সরবরাহ করছে।
                </p>
            </div>
            
            <!-- Quick Links -->
            <div data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-lime-accent font-bold text-xl mb-4 uppercase"><?= __('about_us') ?></h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= SITE_URL ?>/about.php" class="hover:text-lime-accent transition-colors">About QuickMed</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php" class="hover:text-lime-accent transition-colors"><?= __('contact_us') ?></a></li>
                    <li><a href="<?= SITE_URL ?>/terms.php" class="hover:text-lime-accent transition-colors"><?= __('terms') ?></a></li>
                    <li><a href="<?= SITE_URL ?>/privacy.php" class="hover:text-lime-accent transition-colors"><?= __('privacy') ?></a></li>
                    <li><a href="<?= SITE_URL ?>/faq.php" class="hover:text-lime-accent transition-colors">FAQ</a></li>
                </ul>
            </div>
            
            <!-- Our Shops -->
            <div data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-lime-accent font-bold text-xl mb-4 uppercase">Our Locations</h3>
                <ul class="space-y-2 text-sm">
                    <?php
                    $shopsQuery = "SELECT * FROM shops WHERE is_active = 1 ORDER BY name";
                    $shopsResult = $conn->query($shopsQuery);
                    while ($shop = $shopsResult->fetch_assoc()):
                    ?>
                        <li>
                            📍 <?= htmlspecialchars($shop['city']) ?>
                            <span class="text-xs block text-gray-300"><?= htmlspecialchars($shop['phone']) ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            
            <!-- Contact & Social -->
            <div data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-lime-accent font-bold text-xl mb-4 uppercase"><?= __('contact_us') ?></h3>
                <ul class="space-y-3 text-sm mb-6">
                    <li>📞 Hotline: 09678-100100</li>
                    <li>📱 WhatsApp: +880 1711-000000</li>
                    <li>📧 support@quickmed.com</li>
                    <li>⏰ 24/7 Service Available</li>
                </ul>
                
                <h4 class="text-lime-accent font-bold mb-3 uppercase"><?= __('follow_us') ?></h4>
                <div class="flex gap-3">
                    <a href="#" class="bg-lime-accent text-green px-4 py-2 hover:bg-white transition-colors font-bold border-2 border-white">FB</a>
                    <a href="#" class="bg-lime-accent text-green px-4 py-2 hover:bg-white transition-colors font-bold border-2 border-white">TW</a>
                    <a href="#" class="bg-lime-accent text-green px-4 py-2 hover:bg-white transition-colors font-bold border-2 border-white">IG</a>
                    <a href="#" class="bg-lime-accent text-green px-4 py-2 hover:bg-white transition-colors font-bold border-2 border-white">YT</a>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t-2 border-lime-accent mt-8 pt-6 text-center text-sm">
            <p>&copy; <?= date('Y') ?> QuickMed. <?= __('all_rights_reserved') ?> | Developed with ❤️ in Bangladesh</p>
            <p class="mt-2 text-xs text-gray-300">
                Licensed Pharmacy | Drug License No: DA-12345 | All medicines are sourced from authentic manufacturers
            </p>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" class="fixed bottom-8 right-8 bg-lime-accent text-green px-4 py-3 border-4 border-green shadow-retro-lg hover:bg-green hover:text-white transition-all hidden z-40" aria-label="Scroll to top">
    ⬆️
</button>

<!-- AOS Animation Init -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
    
    // Scroll to top functionality
    const scrollBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.remove('hidden');
        } else {
            scrollBtn.classList.add('hidden');
        }
    });
    
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>

<!-- Custom Scripts -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

</body>
</html>