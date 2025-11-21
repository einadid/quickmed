<?php
/**
 * QuickMed - Ultra Dynamic Live Footer (FIXED VISIBILITY)
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
                        title: 'Sent!',
                        text: 'We will contact you soon.',
                        confirmButtonColor: '#065f46'
                    });
                });
            </script>";
        }
    }
}
?>

<!-- Spacer to prevent content overlap -->
<div class="h-20"></div>

<!-- Footer Container -->
<footer class="relative bg-[#022c22] text-white pt-20 pb-24 overflow-hidden border-t-4 border-[#84cc16] z-10">
    
    <!-- 🌌 LIVE CANVAS BACKGROUND -->
    <canvas id="footerCanvas" class="absolute inset-0 w-full h-full z-0 opacity-30 pointer-events-none"></canvas>
    
    <!-- Decorative Gradient -->
    <div class="absolute inset-0 bg-gradient-to-t from-[#065f46] to-transparent opacity-80 z-0 pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12">
            
            <!-- 🏥 Brand & Info (Col-4) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="inline-flex items-center gap-3 border-b-4 border-[#84cc16] pb-2">
                    <div class="w-12 h-12 bg-[#84cc16] text-[#065f46] rounded-lg flex items-center justify-center text-2xl font-bold animate-pulse">QM</div>
                    <h2 class="text-4xl font-mono font-bold tracking-tighter text-white">QuickMed</h2>
                </div>
                <p class="text-gray-300 text-lg leading-relaxed">
                    Your digital healthcare partner. Genuine medicines, expert care, and lightning-fast delivery.
                </p>
                
                <!-- Social Icons -->
                <div class="flex gap-4 pt-4">
                    <a href="#" class="w-10 h-10 border-2 border-[#84cc16] text-[#84cc16] flex items-center justify-center hover:bg-[#84cc16] hover:text-[#065f46] transition-all font-bold">FB</a>
                    <a href="#" class="w-10 h-10 border-2 border-[#84cc16] text-[#84cc16] flex items-center justify-center hover:bg-[#84cc16] hover:text-[#065f46] transition-all font-bold">IG</a>
                    <a href="#" class="w-10 h-10 border-2 border-[#84cc16] text-[#84cc16] flex items-center justify-center hover:bg-[#84cc16] hover:text-[#065f46] transition-all font-bold">TW</a>
                </div>
            </div>

            <!-- 🔗 Quick Links (Col-3) -->
            <div class="lg:col-span-3 pt-4">
                <h3 class="text-2xl font-bold text-[#84cc16] mb-6 uppercase tracking-widest">Explore</h3>
                <ul class="space-y-4 font-mono text-lg">
                    <li><a href="<?= SITE_URL ?>/shop.php" class="hover:text-[#84cc16] hover:pl-2 transition-all flex items-center gap-2">➜ Shop Medicines</a></li>
                    <li><a href="<?= SITE_URL ?>/my-orders.php" class="hover:text-[#84cc16] hover:pl-2 transition-all flex items-center gap-2">➜ Track Order</a></li>
                    <li><a href="<?= SITE_URL ?>/prescription-upload.php" class="hover:text-[#84cc16] hover:pl-2 transition-all flex items-center gap-2">➜ Upload Rx</a></li>
                    <li><a href="<?= SITE_URL ?>/profile.php" class="hover:text-[#84cc16] hover:pl-2 transition-all flex items-center gap-2">➜ My Profile</a></li>
                </ul>
            </div>

            <!-- 💬 LIVE MESSAGE BOX (Col-5) -->
            <div class="lg:col-span-5">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-8 rounded-xl relative overflow-hidden group hover:border-[#84cc16] transition-colors">
                    <!-- Neon Glow Line -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-[#84cc16] to-transparent animate-shimmer"></div>
                    
                    <h3 class="text-2xl font-bold text-white mb-2 flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#84cc16] rounded-full animate-ping"></span>
                        Contact Admin
                    </h3>
                    
                    <form method="POST" class="space-y-4 mt-6">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="name" placeholder="Your Name" class="w-full bg-black/30 border-2 border-[#065f46] text-[#ecfccb] p-3 focus:border-[#84cc16] focus:outline-none transition-colors" required>
                            <input type="email" name="email" placeholder="Email" class="w-full bg-black/30 border-2 border-[#065f46] text-[#ecfccb] p-3 focus:border-[#84cc16] focus:outline-none transition-colors" required>
                        </div>
                        <textarea name="message" rows="3" placeholder="Type your message..." class="w-full bg-black/30 border-2 border-[#065f46] text-[#ecfccb] p-3 focus:border-[#84cc16] focus:outline-none transition-colors" required></textarea>
                        
                        <button type="submit" name="send_message" class="w-full bg-[#84cc16] text-[#065f46] font-bold py-3 uppercase tracking-widest hover:bg-white hover:shadow-[0_0_20px_#84cc16] transition-all transform active:scale-95">
                            🚀 Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-white/10 mt-12 pt-8 text-center font-mono text-gray-400 text-sm">
            <p>&copy; <?= date('Y') ?> QuickMed System. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button id="scrollToTop" class="fixed bottom-24 right-6 bg-[#84cc16] text-[#065f46] p-3 rounded-full shadow-2xl transform translate-y-20 opacity-0 transition-all duration-500 hover:scale-110 z-40 border-4 border-white hidden md:block">
    ⬆️
</button>

<!-- CSS for Animations -->
<style>
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .animate-shimmer {
        animation: shimmer 3s infinite;
    }
    
    /* Ensure footer content is visible */
    footer {
        opacity: 1 !important;
        visibility: visible !important;
    }
</style>

<!-- JS for Canvas & Interactions -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- 1. SCROLL TO TOP ---
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

    // --- 2. PARTICLE ANIMATION ---
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

        for (let i = 0; i < 50; i++) particles.push(new Particle());

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
                        ctx.lineWidth = 0.5;
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
});
</script>

<!-- AOS Init (If used) -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Check if AOS is loaded before init
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
    }
</script>

</body>
</html>