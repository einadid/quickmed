<?php
/**
 * Ultra Modern Footer - Live & Dynamic
 */

// Handle Message Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $msg = clean($_POST['message']);
    
    if (!empty($name) && !empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $msg);
        if ($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Message Sent!',
                        text: 'We will contact you soon.',
                        confirmButtonColor: '#065f46'
                    });
                });
            </script>";
        }
    }
}
?>

<!-- Spacer -->
<div class="h-24 md:h-32"></div>

<!-- Footer Section -->
<footer class="relative bg-[#022c22] text-white pt-24 pb-10 overflow-hidden border-t-4 border-[#84cc16] z-10">
    
    <!-- 🌌 LIVE CANVAS BACKGROUND -->
    <canvas id="footerCanvas" class="absolute inset-0 w-full h-full z-0 opacity-20 pointer-events-none"></canvas>
    
    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-t from-[#065f46] via-transparent to-transparent opacity-90 z-0 pointer-events-none"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12 items-start">
            
            <!-- 🏥 Brand & About (Col-4) -->
            <div class="lg:col-span-4 space-y-6" data-aos="fade-right">
                <a href="<?= SITE_URL ?>/index.php" class="inline-flex items-center gap-3 group">
                    <div class="w-14 h-14 bg-[#84cc16] text-[#065f46] rounded-xl flex items-center justify-center text-3xl font-bold shadow-[4px_4px_0px_white] group-hover:rotate-6 transition-transform duration-300">QM</div>
                    <div>
                        <h2 class="text-3xl font-mono font-bold tracking-tighter text-white">QuickMed</h2>
                        <p class="text-xs text-[#84cc16] tracking-widest uppercase">Digital Pharmacy</p>
                    </div>
                </a>
                
                <p class="text-gray-300 text-lg leading-relaxed font-light">
                    QuickMed brings healthcare to your fingertips. We ensure <span class="text-[#84cc16] font-bold">100% genuine medicine</span>, fast delivery, and expert consultation. Your health is our priority.
                </p>
                
                <div class="flex gap-4 pt-2">
                    <a href="#" class="social-icon">FB</a>
                    <a href="#" class="social-icon">IG</a>
                    <a href="#" class="social-icon">TW</a>
                    <a href="#" class="social-icon">YT</a>
                </div>
            </div>

            <!-- 🔗 Quick Links (Col-3) -->
            <div class="lg:col-span-3 pt-2 space-y-8" data-aos="fade-up">
                <div>
                    <h3 class="text-xl font-bold text-[#84cc16] mb-6 uppercase tracking-widest border-b-2 border-[#84cc16] inline-block pb-1">Explore</h3>
                    <ul class="space-y-3 font-mono text-lg">
                        <li><a href="<?= SITE_URL ?>/about.php" class="footer-link">➜ About Us</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php" class="footer-link">➜ Contact Support</a></li>
                        <li><a href="<?= SITE_URL ?>/shop.php" class="footer-link">➜ Shop Medicines</a></li>
                        <li><a href="<?= SITE_URL ?>/blog.php" class="footer-link">➜ Health Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold text-[#84cc16] mb-4 uppercase tracking-widest">Legal</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-white transition">Return Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- 💬 LIVE MESSAGE BOX (Col-5) -->
            <div class="lg:col-span-5" data-aos="fade-left">
                <div class="glass-box p-8 rounded-2xl relative overflow-hidden group hover:border-[#84cc16] transition-colors duration-500">
                    <!-- Neon Glow Line -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-[#84cc16] to-transparent animate-shimmer"></div>
                    
                    <h3 class="text-2xl font-bold text-white mb-2 flex items-center gap-3">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#84cc16] opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-[#84cc16]"></span>
                        </span>
                        Direct Message to Admin
                    </h3>
                    <p class="text-gray-400 text-sm mb-6">Have a question? Send us a message directly.</p>
                    
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-[#84cc16]"></span>
                                <input type="text" name="name" placeholder="Your Name" class="footer-input pl-10" required>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-[#84cc16]"></span>
                                <input type="email" name="email" placeholder="Email" class="footer-input pl-10" required>
                            </div>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-[#84cc16]"></span>
                            <textarea name="message" rows="2" placeholder="Type your message..." class="footer-input pl-10 w-full" required></textarea>
                        </div>
                        
                        <button type="submit" name="send_message" class="w-full bg-[#84cc16] text-[#065f46] font-bold py-3 uppercase tracking-widest hover:bg-white hover:shadow-[0_0_20px_#84cc16] transition-all transform active:scale-95 rounded-lg">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/10 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400 font-mono">
    <p>
        &copy; <?= date('Y') ?> QuickMed. Built with 
        <a href="https://github.com/einadid" target="_blank" class="font-bold text-[#84cc16] hover:text-white transition-colors">
            einadid
        </a>
    </p>
    <div class="mt-4 md:mt-0">
        <span class="text-[#84cc16]">System Version 1.0</span>
    </div>
</div>
    </div>
</footer>

<!-- Scroll to Top -->
<button id="scrollToTop" class="fixed bottom-24 right-6 bg-[#84cc16] text-[#065f46] p-3 rounded-full shadow-2xl transform translate-y-20 opacity-0 transition-all duration-500 hover:scale-110 z-40 border-4 border-white hidden md:block">
    ⬆️
</button>

<style>
    /* Animations */
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .animate-shimmer { animation: shimmer 3s infinite; }
    
    /* Styles */
    .social-icon {
        width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;
        border: 2px solid rgba(255,255,255,0.2); color: white; font-weight: bold;
        transition: all 0.3s; border-radius: 50%;
    }
    .social-icon:hover { background: #84cc16; border-color: #84cc16; color: #065f46; transform: translateY(-3px); }
    
    .footer-link {
        color: #d1d5db; transition: all 0.3s; display: flex; align-items: center; gap: 8px;
    }
    .footer-link:hover { color: #84cc16; padding-left: 8px; }
    
    .glass-box {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    
    .footer-input {
        width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
        color: white; padding: 12px; border-radius: 8px; transition: all 0.3s;
        outline: none; font-size: 14px;
    }
    .footer-input:focus {
        border-color: #84cc16; background: rgba(0,0,0,0.5);
        box-shadow: 0 0 10px rgba(132, 204, 22, 0.2);
    }
    
    footer { opacity: 1 !important; visibility: visible !important; }
</style>

<!-- Particle Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('footerCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width, height, particles = [];

        function resize() {
            width = canvas.width = canvas.parentElement.offsetWidth;
            height = canvas.height = canvas.parentElement.offsetHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5;
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2 + 1;
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;
                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = '#84cc16';
                ctx.fill();
            }
        }

        for (let i = 0; i < 40; i++) particles.push(new Particle());

        function animate() {
            ctx.clearRect(0, 0, width, height);
            particles.forEach((p, i) => {
                p.update();
                p.draw();
                particles.slice(i + 1).forEach(p2 => {
                    const dx = p.x - p2.x;
                    const dy = p.y - p2.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 100) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(132, 204, 22, ${1 - dist / 100})`;
                        ctx.lineWidth = 0.3;
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();
                    }
                });
            });
            requestAnimationFrame(animate);
        }
        animate();
    }

    // Scroll to Top
    const scrollBtn = document.getElementById('scrollToTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollBtn.classList.remove('translate-y-20', 'opacity-0');
        } else {
            scrollBtn.classList.add('translate-y-20', 'opacity-0');
        }
    });
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>

<!-- AOS Init -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>if (typeof AOS !== 'undefined') AOS.init({ duration: 800, once: true });</script>

</body>
</html>