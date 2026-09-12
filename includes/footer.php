</main>
<?php
// Handle Message Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $msg = clean($_POST['message']);
    if (!empty($name) && !empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $msg);
        if ($stmt->execute()) {
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'success', title: 'Message Sent!', text: 'We will contact you soon.', confirmButtonColor: '#065f46' }); });</script>";
        }
    }
}
?>

<div class="h-12 sm:h-16 md:h-20"></div>

<footer class="relative bg-[#022c22] text-white pt-10 sm:pt-14 pb-6 sm:pb-8 overflow-hidden border-t-[2.5px] border-[#84cc16] z-10">
    <canvas id="footerCanvas" class="absolute inset-0 w-full h-full z-0 opacity-[0.12] pointer-events-none"></canvas>
    <div class="absolute inset-0 bg-gradient-to-t from-[#065f46] via-transparent to-transparent opacity-80 z-0 pointer-events-none"></div>

    <div class="container mx-auto px-3 sm:px-4 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            <!-- Brand -->
            <div class="sm:col-span-2 lg:col-span-4 space-y-3 sm:space-y-4" data-aos="fade-right">
                <a href="<?= SITE_URL ?>/index.php" class="inline-flex items-center gap-2.5 group">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#84cc16] text-[#065f46] rounded-[0.6rem] flex items-center justify-center text-xl sm:text-2xl font-bold shadow-[2px_2px_0px_white] group-hover:rotate-6 transition-transform duration-300">QM</div>
                    <div>
                        <h2 class="text-[1.3rem] sm:text-xl font-mono font-bold tracking-tighter text-white leading-none">QuickMed</h2>
                        <p class="text-[9px] sm:text-[10px] text-[#84cc16] tracking-widest uppercase font-bold">Digital Pharmacy</p>
                    </div>
                </a>
                <p class="text-gray-300 text-[0.82rem] sm:text-[0.85rem] leading-relaxed font-light">QuickMed brings healthcare to your fingertips. We ensure <span class="text-[#84cc16] font-bold">100% genuine medicine</span>, fast delivery, and expert consultation.</p>
                <div class="flex gap-2.5 pt-1">
                    <a href="#" class="social-icon w-8 h-8 sm:w-9 sm:h-9 text-[11px] sm:text-xs">FB</a>
                    <a href="#" class="social-icon w-8 h-8 sm:w-9 sm:h-9 text-[11px] sm:text-xs">IG</a>
                    <a href="#" class="social-icon w-8 h-8 sm:w-9 sm:h-9 text-[11px] sm:text-xs">TW</a>
                    <a href="#" class="social-icon w-8 h-8 sm:w-9 sm:h-9 text-[11px] sm:text-xs">YT</a>
                </div>
            </div>

            <!-- Links -->
            <div class="lg:col-span-3 pt-1 space-y-5 sm:space-y-6" data-aos="fade-up">
                <div>
                    <h3 class="text-[0.85rem] sm:text-sm font-bold text-[#84cc16] mb-3 uppercase tracking-widest border-b border-[#84cc16] inline-block pb-0.5">Explore</h3>
                    <ul class="space-y-1.5 sm:space-y-2 font-mono text-[0.82rem] sm:text-[0.85rem]">
                        <li><a href="<?= SITE_URL ?>/about.php" class="footer-link text-[0.82rem]">➜ About Us</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php" class="footer-link text-[0.82rem]">➜ Contact Support</a></li>
                        <li><a href="<?= SITE_URL ?>/shop.php" class="footer-link text-[0.82rem]">➜ Shop Medicines</a></li>
                        <li><a href="<?= SITE_URL ?>/blog.php" class="footer-link text-[0.82rem]">➜ Health Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-[0.85rem] sm:text-sm font-bold text-[#84cc16] mb-2.5 uppercase tracking-widest">Legal</h3>
                    <ul class="space-y-1 text-[11px] sm:text-xs text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-white transition">Return Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Message Box - compact -->
            <div class="sm:col-span-2 lg:col-span-5" data-aos="fade-left">
                <div class="glass-box p-4 sm:p-5 rounded-xl relative overflow-hidden group hover:border-[#84cc16]/50 transition-colors duration-300">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-[#84cc16] to-transparent animate-shimmer"></div>
                    <h3 class="text-[0.95rem] sm:text-[1.05rem] font-bold text-white mb-1.5 flex items-center gap-2">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#84cc16] opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-[#84cc16]"></span></span>
                        Direct Message
                    </h3>
                    <p class="text-gray-400 text-[11px] sm:text-xs mb-3">Have a question? Send us a message.</p>
                    <form method="POST" class="space-y-2.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <input type="text" name="name" placeholder="Your Name" class="footer-input text-[0.8rem] py-2 px-3" required>
                            <input type="email" name="email" placeholder="Email" class="footer-input text-[0.8rem] py-2 px-3" required>
                        </div>
                        <textarea name="message" rows="2" placeholder="Type your message..." class="footer-input w-full text-[0.8rem] py-2 px-3" required></textarea>
                        <button type="submit" name="send_message" class="w-full bg-[#84cc16] text-[#065f46] font-bold py-2.5 text-[0.8rem] uppercase tracking-widest hover:bg-white hover:shadow-[0_0_15px_#84cc16] transition-all active:scale-[0.98] rounded-lg">Send Message</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 mt-8 sm:mt-10 pt-4 sm:pt-5 flex flex-col sm:flex-row justify-between items-center text-[11px] sm:text-xs text-gray-400 font-mono gap-2">
            <p>&copy; <?= date('Y') ?> QuickMed. Built with <a href="https://github.com/einadid" target="_blank" class="font-bold text-[#84cc16] hover:text-white transition-colors">einadid</a></p>
            <div><span class="text-[#84cc16] text-[10px] sm:text-xs">System v3.0 • Responsive</span></div>
        </div>
    </div>
</footer>

<button id="scrollToTop" class="fixed bottom-20 sm:bottom-6 right-3 sm:right-5 bg-[#84cc16] text-[#065f46] w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full shadow-xl transform translate-y-20 opacity-0 transition-all duration-300 hover:scale-110 z-40 border-2 border-white hidden md:flex text-sm">⬆️</button>

<style>
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .animate-shimmer { animation: shimmer 3s infinite; }
    .social-icon { display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(255,255,255,0.15); color: white; font-weight: bold; transition: all 0.25s; border-radius: 50%; }
    .social-icon:hover { background: #84cc16; border-color: #84cc16; color: #065f46; transform: translateY(-2px); }
    .footer-link { color: #d1d5db; transition: all 0.25s; display: flex; align-items: center; gap: 6px; }
    .footer-link:hover { color: #84cc16; padding-left: 4px; }
    .glass-box { background: rgba(255,255,255,0.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 12px 30px rgba(0,0,0,0.15); }
    .footer-input { width: 100%; background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); color: white; border-radius: 0.6rem; transition: all 0.25s; outline: none; }
    .footer-input:focus { border-color: #84cc16; background: rgba(0,0,0,0.4); box-shadow: 0 0 8px rgba(132,204,22,0.15); }
    .footer-input::placeholder { color: rgba(255,255,255,0.4); font-size: 0.8rem; }
    footer { opacity: 1 !important; visibility: visible !important; }
    @media (max-width: 640px) {
        footer .container { padding-left: 12px !important; padding-right: 12px !important; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('footerCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width, height, particles = [];
        function resize() { width = canvas.width = canvas.parentElement.offsetWidth; height = canvas.height = canvas.parentElement.offsetHeight; }
        window.addEventListener('resize', resize); resize();
        class Particle {
            constructor() { this.x = Math.random()*width; this.y = Math.random()*height; this.vx = (Math.random()-0.5)*0.4; this.vy = (Math.random()-0.5)*0.4; this.size = Math.random()*1.5+0.8; }
            update() { this.x+=this.vx; this.y+=this.vy; if(this.x<0||this.x>width) this.vx*=-1; if(this.y<0||this.y>height) this.vy*=-1; }
            draw() { ctx.beginPath(); ctx.arc(this.x,this.y,this.size,0,Math.PI*2); ctx.fillStyle='#84cc16'; ctx.fill(); }
        }
        for(let i=0;i<28;i++) particles.push(new Particle());
        function animate() {
            ctx.clearRect(0,0,width,height);
            particles.forEach((p,i)=>{
                p.update(); p.draw();
                particles.slice(i+1).forEach(p2=>{
                    const dx=p.x-p2.x, dy=p.y-p2.y, dist=Math.sqrt(dx*dx+dy*dy);
                    if(dist<90){ ctx.beginPath(); ctx.strokeStyle=`rgba(132,204,22,${1-dist/90})`; ctx.lineWidth=0.25; ctx.moveTo(p.x,p.y); ctx.lineTo(p2.x,p2.y); ctx.stroke(); }
                });
            });
            requestAnimationFrame(animate);
        }
        animate();
    }
    const scrollBtn = document.getElementById('scrollToTop');
    if(scrollBtn){
        window.addEventListener('scroll', () => {
            if(window.scrollY>250){ scrollBtn.classList.remove('translate-y-20','opacity-0'); } else { scrollBtn.classList.add('translate-y-20','opacity-0'); }
        });
        scrollBtn.addEventListener('click', () => { window.scrollTo({ top:0, behavior:'smooth' }); });
    }
});
</script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>if (typeof AOS !== 'undefined') AOS.init({ duration: 700, once: true, offset: 60 });</script>

</body>
</html>
