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

<?php qm_hero('Contact Support', "We're here to help regarding your medicines & orders.", 'Agents Online Now', '📞'); ?>

<section class="container mx-auto px-4 py-12 relative z-20">
    <p class="text-center text-gray-500 font-mono mb-10">🕐 <span id="ctgClock">Loading...</span> (CTG Time)</p>
    
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="stat-card lime" data-aos="fade-up">
            <div class="stat-icon">📍</div>
            <div>
                <div class="stat-label">Head Office</div>
                <div class="font-bold text-[#065f46]">GEC Circle, Chattogram</div>
                <a href="#map" class="text-sm text-[#65a30d] font-bold hover:underline">View on Map →</a>
            </div>
        </div>

        <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-icon">📞</div>
            <div>
                <div class="stat-label">Call Us · 9AM–10PM</div>
                <a href="tel:09678100100" class="font-bold text-[#065f46] text-lg font-display hover:underline">09678-100100</a>
            </div>
        </div>

        <div class="stat-card blue" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-icon">📧</div>
            <div>
                <div class="stat-label">Email Us</div>
                <a href="mailto:support@quickmed.com" class="font-bold text-[#065f46] hover:underline break-all">support@quickmed.com</a>
                <p class="text-xs text-gray-400">Reply within ~2 hours</p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="card card-pad-lg" data-aos="fade-right">
                <div class="card-header">📩 Send Message <span class="text-3xl">✍️</span></div>

                <form method="POST" class="space-y-5">
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="label">Your Name</label>
                            <input type="text" name="name" class="input" required placeholder="e.g. Rahim Uddin">
                        </div>
                        <div>
                            <label class="label">Email Address</label>
                            <input type="email" name="email" class="input" required placeholder="rahim@example.com">
                        </div>
                    </div>

                    <div>
                        <label class="label">Topic</label>
                        <select name="subject" class="input">
                            <option value="Order Issue">📦 Order Status / Issue</option>
                            <option value="Prescription">📋 Prescription Help</option>
                            <option value="Product">💊 Medicine Inquiry</option>
                            <option value="Other">💬 General Feedback</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Message</label>
                        <textarea name="message" rows="5" class="input" required placeholder="Describe your issue..."></textarea>
                    </div>

                    <button type="submit" name="send_message" class="btn btn-primary btn-lg btn-block">
                        <span>🚀</span> Send Message →
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

            <div class="card text-center card-accent">
                <div class="text-4xl mb-2">💬</div>
                <h3 class="text-lg font-bold text-gray-800">Live Chat</h3>
                <p class="text-gray-500 text-sm mb-4">Chat with our pharmacist instantly.</p>
                <a href="tel:09678100100" class="btn btn-primary btn-block">📞 Call Hotline</a>
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