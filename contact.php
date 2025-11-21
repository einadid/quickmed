<?php
/**
 * Contact Us - Live Dynamic Page
 * Location: Chittagong, Bangladesh
 */
require_once 'config.php';
$pageTitle = 'Contact Us - QuickMed';

// Handle Message Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $subject = clean($_POST['subject']);
    $msg = clean($_POST['message']);
    
    if (!empty($name) && !empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
        $fullMsg = "Subject: $subject\n\n$msg";
        $stmt->bind_param("sss", $name, $email, $fullMsg);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Message Sent Successfully! We will reply shortly.';
        } else {
            $_SESSION['error'] = 'Failed to send message.';
        }
        // Prevent resubmission
        header("Location: contact.php");
        exit;
    }
}

include 'includes/header.php';
?>

<style>
    /* Floating Animation */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    .floating-icon { animation: float 6s ease-in-out infinite; }
    .delay-1 { animation-delay: 1s; }
    .delay-2 { animation-delay: 2s; }
</style>

<section class="relative h-[45vh] bg-gradient-to-r from-deep-green to-emerald-900 flex items-center justify-center overflow-hidden">
    <div class="absolute top-10 left-10 text-6xl opacity-10 text-white floating-icon">📍</div>
    <div class="absolute bottom-10 right-10 text-6xl opacity-10 text-white floating-icon delay-1">📞</div>
    <div class="absolute top-20 right-1/4 text-4xl opacity-10 text-white floating-icon delay-2">📧</div>

    <div class="text-center relative z-10 text-white px-4" data-aos="zoom-in">
        <div class="inline-flex items-center gap-2 bg-lime-accent text-deep-green px-4 py-1 rounded-full font-bold text-xs uppercase tracking-widest mb-4 shadow-lg animate-pulse">
            <span class="w-2 h-2 bg-deep-green rounded-full"></span> Agents Online Now
        </div>
        <h1 class="text-5xl md:text-6xl font-bold font-mono mb-4">Contact Support</h1>
        <p class="text-gray-200 text-lg">We're here to help regarding your medicines & orders.</p>
        
        <div class="mt-6 text-lime-accent font-mono bg-white/10 inline-block px-6 py-2 rounded-lg backdrop-blur-sm border border-white/20">
            <span id="ctgClock">Loading Time...</span> (CTG Time)
        </div>
    </div>
</section>

<section class="container mx-auto px-4 py-16 -mt-24 relative z-20">
    
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="card bg-white border-l-8 border-lime-accent p-8 shadow-xl hover:-translate-y-2 transition-all group" data-aos="fade-up">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center text-3xl mb-4 group-hover:rotate-12 transition-transform">📍</div>
            <h3 class="font-bold text-deep-green text-xl">Head Office</h3>
            <p class="text-gray-600 mt-2 leading-relaxed">
                GEC Circle, CDA Avenue,<br>
                Chattogram, Bangladesh
            </p>
            <a href="#map" class="text-lime-600 font-bold text-sm mt-4 inline-block hover:underline">View on Map →</a>
        </div>

        <div class="card bg-white border-l-8 border-deep-green p-8 shadow-xl hover:-translate-y-2 transition-all group" data-aos="fade-up" data-aos-delay="100">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center text-3xl mb-4 group-hover:rotate-12 transition-transform">📞</div>
            <h3 class="font-bold text-deep-green text-xl">Call Us</h3>
            <a href="tel:09678100100" class="text-2xl font-mono font-bold text-gray-700 mt-2 block hover:text-deep-green transition">09678-100100</a>
            <p class="text-xs text-gray-400 mt-1">Available 9:00 AM - 10:00 PM</p>
        </div>

        <div class="card bg-white border-l-8 border-blue-500 p-8 shadow-xl hover:-translate-y-2 transition-all group" data-aos="fade-up" data-aos-delay="200">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-3xl mb-4 group-hover:rotate-12 transition-transform">📧</div>
            <h3 class="font-bold text-deep-green text-xl">Email Us</h3>
            <a href="mailto:support@quickmed.com" class="text-gray-600 mt-2 block hover:text-blue-600 transition">support@quickmed.com</a>
            <p class="text-xs text-gray-400 mt-1">Usually reply within 2 hours</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="card bg-white border-4 border-deep-green p-8 shadow-2xl" data-aos="fade-right">
                <div class="flex items-center justify-between mb-6 border-b-2 border-gray-100 pb-4">
                    <h2 class="text-3xl font-bold text-deep-green">📩 Send Message</h2>
                    <span class="text-4xl animate-bounce">✍️</span>
                </div>
                
                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="form-control">
                            <label class="label text-deep-green font-bold">Your Name</label>
                            <input type="text" name="name" class="input w-full border-2 border-gray-200 focus:border-lime-accent transition bg-gray-50 focus:bg-white" required placeholder="e.g. Rahim Uddin">
                        </div>
                        <div class="form-control">
                            <label class="label text-deep-green font-bold">Email Address</label>
                            <input type="email" name="email" class="input w-full border-2 border-gray-200 focus:border-lime-accent transition bg-gray-50 focus:bg-white" required placeholder="rahim@example.com">
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label text-deep-green font-bold">Topic</label>
                        <select name="subject" class="input w-full border-2 border-gray-200 focus:border-lime-accent bg-gray-50">
                            <option value="Order Issue">📦 Order Status / Issue</option>
                            <option value="Prescription">📋 Prescription Help</option>
                            <option value="Product">💊 Medicine Inquiry</option>
                            <option value="Other">💬 General Feedback</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label text-deep-green font-bold">Message</label>
                        <textarea name="message" rows="5" class="input w-full border-2 border-gray-200 focus:border-lime-accent transition bg-gray-50 focus:bg-white resize-none" required placeholder="Describe your issue..."></textarea>
                    </div>

                    <button type="submit" name="send_message" class="btn btn-primary w-full py-4 text-lg font-bold shadow-lg transform hover:scale-105 transition-all flex items-center justify-center gap-2 group">
                        <span>🚀</span> Send Message <span class="group-hover:translate-x-2 transition-transform">→</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6" data-aos="fade-left">
            <div class="bg-deep-green text-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold mb-4">❓ Quick FAQ</h3>
                <div class="space-y-3">
                    <details class="group bg-white/10 rounded-lg p-3 cursor-pointer">
                        <summary class="font-bold text-lime-accent flex justify-between items-center list-none">
                            How to order? <span>+</span>
                        </summary>
                        <p class="text-sm mt-2 text-gray-200">Simply search for medicine, add to cart & checkout. Or upload a prescription.</p>
                    </details>
                    <details class="group bg-white/10 rounded-lg p-3 cursor-pointer">
                        <summary class="font-bold text-lime-accent flex justify-between items-center list-none">
                            Delivery time? <span>+</span>
                        </summary>
                        <p class="text-sm mt-2 text-gray-200">Inside Chattogram: 2-4 Hours. Nationwide: 24-48 Hours.</p>
                    </details>
                    <details class="group bg-white/10 rounded-lg p-3 cursor-pointer">
                        <summary class="font-bold text-lime-accent flex justify-between items-center list-none">
                            Delivery Charge? <span>+</span>
                        </summary>
                        <p class="text-sm mt-2 text-gray-200">Free delivery on orders above 500৳. Standard charge 60৳.</p>
                    </details>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500 text-center">
                <div class="text-4xl mb-2">💬</div>
                <h3 class="text-lg font-bold text-gray-800">Live Chat</h3>
                <p class="text-gray-500 text-sm mb-4">Chat with our pharmacist instantly.</p>
                <a href="https://wa.me/8801XXXXXXXXX" target="_blank" class="btn bg-green-500 text-white w-full hover:bg-green-600">WhatsApp Chat</a>
            </div>
        </div>
    </div>
</section>

<section id="map" class="relative h-[450px] w-full border-t-8 border-deep-green mt-8 group">
    <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10 bg-white px-6 py-2 rounded-full shadow-xl border-2 border-deep-green pointer-events-none">
        <span class="font-bold text-deep-green">📍 Find Us in Chattogram</span>
    </div>
    
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3689.8629312764367!2d91.8204572148869!3d22.358806046419635!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30acd883600229d5%3A0x5e16923e563176d7!2sGEC%20Circle%2C%20Chittagong!5e0!3m2!1sen!2sbd!4v1677654321234!5m2!1sen!2sbd" 
        width="100%" 
        height="100%" 
        style="border:0; filter: grayscale(20%) contrast(1.2);" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade"
        class="group-hover:filter-none transition-all duration-500">
    </iframe>
</section>

<script>
    // Live Chittagong Clock
    function updateClock() {
        const now = new Date();
        const options = { 
            timeZone: 'Asia/Dhaka', 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            hour12: true 
        };
        const timeString = new Intl.DateTimeFormat('en-US', options).format(now);
        document.getElementById('ctgClock').innerText = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // SweetAlert for Success/Error
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Thank You!',
            text: '<?= $_SESSION['success'] ?>',
            confirmButtonColor: '#065f46'
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= $_SESSION['error'] ?>',
            confirmButtonColor: '#ef4444'
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>