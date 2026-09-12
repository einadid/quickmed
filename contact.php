<?php
require_once 'config.php';
$pageTitle = 'Contact Us - QuickMed';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = clean($_POST['name']); $email = clean($_POST['email']); $subject = clean($_POST['subject']); $msg = clean($_POST['message']);
    if (!empty($name) && !empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
        $fullMsg = "Subject: $subject\n\n$msg";
        $stmt->bind_param("sss", $name, $email, $fullMsg);
        if ($stmt->execute()) { $_SESSION['success'] = 'Message Sent Successfully! We will reply shortly.'; } else { $_SESSION['error'] = 'Failed to send message.'; }
        header("Location: contact.php"); exit;
    }
}
include 'includes/header.php';
?>
<style>@keyframes float{0%{transform:translateY(0)}50%{transform:translateY(-12px)}100%{transform:translateY(0)}}.floating-icon{animation:float 5s ease-in-out infinite}</style>
<?php qm_hero('Contact Support', "We're here to help regarding your medicines & orders.", 'Agents Online Now', '📞'); ?>
<section class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 relative z-20">
    <p class="text-center text-gray-500 font-mono mb-6 text-[0.75rem] sm:text-[0.82rem]">🕐 <span id="ctgClock">Loading...</span> (CTG Time)</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div class="stat-card lime p-3 sm:p-4" data-aos="fade-up"><div class="stat-icon w-10 h-10 text-lg">📍</div><div><div class="stat-label text-[10px]">Head Office</div><div class="font-bold text-[#065f46] text-[0.82rem] sm:text-sm">GEC Circle, Chattogram</div><a href="#map" class="text-[11px] text-[#65a30d] font-bold hover:underline">View on Map →</a></div></div>
        <div class="stat-card p-3 sm:p-4" data-aos="fade-up" data-aos-delay="80"><div class="stat-icon w-10 h-10 text-lg">📞</div><div><div class="stat-label text-[10px]">Call Us · 9AM–10PM</div><a href="tel:09678100100" class="font-bold text-[#065f46] text-[0.95rem] sm:text-base font-display hover:underline">09678-100100</a></div></div>
        <div class="stat-card blue p-3 sm:p-4" data-aos="fade-up" data-aos-delay="160"><div class="stat-icon w-10 h-10 text-lg">📧</div><div><div class="stat-label text-[10px]">Email Us</div><a href="mailto:support@quickmed.com" class="font-bold text-[#065f46] text-[0.78rem] sm:text-sm hover:underline break-all">support@quickmed.com</a><p class="text-[10px] text-gray-400">Reply within ~2 hours</p></div></div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="lg:col-span-2">
            <div class="card p-3 sm:p-5" data-aos="fade-right">
                <div class="card-header text-[0.85rem] sm:text-sm">📩 Send Message <span class="text-lg">✍️</span></div>
                <form method="POST" class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <div><label class="label text-[0.78rem]">Your Name</label><input type="text" name="name" class="input text-[0.82rem] py-2" required placeholder="e.g. Rahim Uddin"></div>
                        <div><label class="label text-[0.78rem]">Email Address</label><input type="email" name="email" class="input text-[0.82rem] py-2" required placeholder="rahim@example.com"></div>
                    </div>
                    <div><label class="label text-[0.78rem]">Topic</label><select name="subject" class="input text-[0.82rem] py-2"><option value="Order Issue">📦 Order Status / Issue</option><option value="Prescription">📋 Prescription Help</option><option value="Product">💊 Medicine Inquiry</option><option value="Other">💬 General Feedback</option></select></div>
                    <div><label class="label text-[0.78rem]">Message</label><textarea name="message" rows="4" class="input text-[0.82rem]" required placeholder="Describe your issue..."></textarea></div>
                    <button type="submit" name="send_message" class="btn btn-primary btn-lg btn-block text-[0.82rem] py-2.5">🚀 Send Message →</button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-1 space-y-4" data-aos="fade-left">
            <div class="bg-deep-green text-white p-4 sm:p-5 rounded-xl shadow-md">
                <h3 class="text-[1rem] sm:text-lg font-bold mb-3">❓ Quick FAQ</h3>
                <div class="space-y-2">
                    <details class="group bg-white/10 rounded-lg p-2.5 cursor-pointer"><summary class="font-bold text-lime-accent flex justify-between items-center list-none text-[0.78rem] sm:text-[0.82rem]">How to order? <span>+</span></summary><p class="text-[11px] sm:text-xs mt-1.5 text-gray-200">Search for medicine, add to cart & checkout. Or upload prescription.</p></details>
                    <details class="group bg-white/10 rounded-lg p-2.5 cursor-pointer"><summary class="font-bold text-lime-accent flex justify-between items-center list-none text-[0.78rem] sm:text-[0.82rem]">Delivery time? <span>+</span></summary><p class="text-[11px] sm:text-xs mt-1.5 text-gray-200">Inside Chattogram: 2-4 Hours. Nationwide: 24-48 Hours.</p></details>
                    <details class="group bg-white/10 rounded-lg p-2.5 cursor-pointer"><summary class="font-bold text-lime-accent flex justify-between items-center list-none text-[0.78rem] sm:text-[0.82rem]">Delivery Charge? <span>+</span></summary><p class="text-[11px] sm:text-xs mt-1.5 text-gray-200">Free delivery on orders above 500৳. Standard charge 60৳.</p></details>
                </div>
            </div>
            <div class="card text-center card-accent p-4"><div class="text-3xl mb-1">💬</div><h3 class="text-[0.9rem] font-bold text-gray-800">Live Chat</h3><p class="text-gray-500 text-[11px] mb-3">Chat with our pharmacist instantly.</p><a href="tel:09678100100" class="btn btn-primary btn-block btn-sm text-[0.75rem]">📞 Call Hotline</a></div>
        </div>
    </div>
</section>
<section id="map" class="relative h-[300px] sm:h-[380px] w-full border-t-4 border-deep-green mt-4 sm:mt-6 group">
    <div class="absolute top-3 left-1/2 -translate-x-1/2 z-10 bg-white px-4 py-1.5 rounded-full shadow-md border-[1.5px] border-deep-green pointer-events-none"><span class="font-bold text-deep-green text-[11px] sm:text-xs">📍 Find Us in Chattogram</span></div>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3689.8629312764367!2d91.8204572148869!3d22.358806046419635!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30acd883600229d5%3A0x5e16923e563176d7!2sGEC%20Circle%2C%20Chittagong!5e0!3m2!1sen!2sbd!4v1677654321234!5m2!1sen!2sbd" width="100%" height="100%" style="border:0; filter: grayscale(20%) contrast(1.1);" allowfullscreen="" loading="lazy" class="group-hover:filter-none transition-all duration-500"></iframe>
</section>
<script>
function updateClock(){const now=new Date();const options={timeZone:'Asia/Dhaka',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true};document.getElementById('ctgClock').innerText=new Intl.DateTimeFormat('en-US',options).format(now);}setInterval(updateClock,1000);updateClock();
</script>
<?php include 'includes/footer.php'; ?>
