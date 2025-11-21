<?php
/**
 * Doctor Dashboard - QuickMed (Redesigned)
 * Features: Live Clock, Floating Emojis, Modern Cards
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('doctor');

$pageTitle = 'Doctor Dashboard - QuickMed';
$user = getCurrentUser();

// 1. Stats Queries
// Pending Prescriptions
$pendingRx = $conn->query("SELECT COUNT(*) as total FROM prescriptions WHERE status = 'pending'")->fetch_assoc()['total'];

// My Posts
$myPosts = $conn->query("SELECT COUNT(*) as total FROM health_posts WHERE author_id = " . $user['id'])->fetch_assoc()['total'];

// Approved Prescriptions (Added for extra stat)
$approvedRx = $conn->query("SELECT COUNT(*) as total FROM prescriptions WHERE status = 'approved' AND reviewed_by = " . $user['id'])->fetch_assoc()['total'];

include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* Background Animation */
    .bg-animate-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
        pointer-events: none;
    }

    .floating-emoji {
        position: absolute;
        bottom: -100px;
        font-size: 3rem;
        opacity: 0.15;
        animation: floatUp linear infinite;
    }

    @keyframes floatUp {
        0% { transform: translateY(0) rotate(0deg); opacity: 0; }
        20% { opacity: 0.2; }
        80% { opacity: 0.2; }
        100% { transform: translateY(-120vh) rotate(360deg); opacity: 0; }
    }

    .e1 { left: 10%; animation-duration: 15s; animation-delay: 0s; font-size: 4rem; }
    .e2 { left: 30%; animation-duration: 12s; animation-delay: 2s; font-size: 2rem; }
    .e3 { left: 50%; animation-duration: 18s; animation-delay: 4s; font-size: 5rem; }
    .e4 { left: 70%; animation-duration: 14s; animation-delay: 1s; font-size: 3rem; }
    .e5 { left: 90%; animation-duration: 20s; animation-delay: 3s; font-size: 4rem; }

    /* Glassmorphism Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
    }
</style>

<div class="min-h-screen bg-gray-50 relative">
    
    <!-- Animated Background -->
    <div class="bg-animate-container">
        <div class="floating-emoji e1">🩺</div>
        <div class="floating-emoji e2">💊</div>
        <div class="floating-emoji e3">🩻</div>
        <div class="floating-emoji e4">💉</div>
        <div class="floating-emoji e5">🧬</div>
    </div>

    <!-- Content Wrapper -->
    <div class="relative z-10 pb-20">
        
        <!-- Hero Header -->
        <div class="bg-gradient-to-r from-teal-700 to-deep-green text-white pt-24 pb-32 rounded-b-[3rem] shadow-xl">
            <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
                <div data-aos="fade-right">
                    <div class="flex items-center gap-2 mb-2 opacity-90">
                        <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                            Doctor Panel
                        </span>
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-mono">Live System</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-2 tracking-tight">
                        Dr. <?= htmlspecialchars(explode(' ', $user['full_name'])[1] ?? $user['full_name']) ?>
                    </h1>
                    <p class="text-lg text-gray-200 flex items-center gap-2">
                        🆔 <span class="font-mono bg-black/20 px-2 rounded"><?= htmlspecialchars($user['member_id']) ?></span>
                    </p>
                </div>
                
                <!-- Live Clock -->
                <div class="mt-8 md:mt-0 text-right" data-aos="fade-left">
                    <div class="text-6xl font-mono font-bold text-lime-accent drop-shadow-md" id="liveClock">00:00</div>
                    <div class="text-xl text-gray-200 font-medium" id="liveDate">Loading date...</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container mx-auto px-6 -mt-24">
            
            <!-- Stats Grid -->
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <!-- Pending Card -->
                <div class="glass-card p-8 rounded-2xl transform hover:-translate-y-2 transition-all duration-300 border-b-4 border-yellow-500 group" data-aos="fade-up" data-aos-delay="0">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-500 font-bold text-sm uppercase tracking-wide">Pending Reviews</h3>
                            <p class="text-5xl font-bold text-yellow-600 mt-2 group-hover:scale-110 transition-transform origin-left"><?= $pendingRx ?></p>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-full text-3xl text-yellow-600">⚠️</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Prescriptions waiting for approval</p>
                </div>

                <!-- Approved Card -->
                <div class="glass-card p-8 rounded-2xl transform hover:-translate-y-2 transition-all duration-300 border-b-4 border-green-500 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-500 font-bold text-sm uppercase tracking-wide">Approved by You</h3>
                            <p class="text-5xl font-bold text-green-600 mt-2 group-hover:scale-110 transition-transform origin-left"><?= $approvedRx ?></p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-full text-3xl text-green-600">✅</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Total patients helped</p>
                </div>

                <!-- Posts Card -->
                <div class="glass-card p-8 rounded-2xl transform hover:-translate-y-2 transition-all duration-300 border-b-4 border-blue-500 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-500 font-bold text-sm uppercase tracking-wide">Published Articles</h3>
                            <p class="text-5xl font-bold text-blue-600 mt-2 group-hover:scale-110 transition-transform origin-left"><?= $myPosts ?></p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-full text-3xl text-blue-600">✍️</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Health tips shared</p>
                </div>
            </div>

            <!-- Main Actions Area -->
            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Primary Action: Prescriptions -->
                <a href="prescriptions.php" class="col-span-2 glass-card p-8 rounded-2xl hover:shadow-2xl transition-all duration-300 group relative overflow-hidden" data-aos="zoom-in">
                    <div class="absolute top-0 right-0 bg-deep-green w-32 h-32 rounded-bl-full opacity-10 group-hover:scale-150 transition-transform duration-500"></div>
                    
                    <div class="relative z-10 flex items-center gap-6">
                        <div class="bg-green-50 p-6 rounded-2xl border-2 border-green-100">
                            <span class="text-6xl group-hover:rotate-12 transition-transform block">📋</span>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-deep-green mb-2">Review Prescriptions</h2>
                            <p class="text-gray-600 mb-4">Check uploaded prescriptions, verify medicines, and approve orders for patients.</p>
                            <span class="inline-flex items-center gap-2 text-deep-green font-bold uppercase tracking-wide text-sm border-b-2 border-deep-green pb-1 group-hover:border-lime-500 transition-colors">
                                Start Reviewing <span>→</span>
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Secondary Actions Stack -->
                <div class="space-y-6" data-aos="fade-left">
                    
                    <a href="create-post.php" class="glass-card p-6 rounded-2xl flex items-center gap-4 hover:bg-blue-50 transition-colors group border-l-4 border-transparent hover:border-blue-500">
                        <div class="text-4xl group-hover:scale-110 transition-transform">📢</div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Write Health Tip</h3>
                            <p class="text-sm text-gray-500">Publish new advice for patients</p>
                        </div>
                    </a>

                    <a href="my-posts.php" class="glass-card p-6 rounded-2xl flex items-center gap-4 hover:bg-purple-50 transition-colors group border-l-4 border-transparent hover:border-purple-500">
                        <div class="text-4xl group-hover:scale-110 transition-transform">📚</div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Manage Posts</h3>
                            <p class="text-sm text-gray-500">Edit or delete your articles</p>
                        </div>
                    </a>

                    <div class="glass-card p-6 rounded-2xl flex items-center gap-4 opacity-75">
                        <a href="add-news.php" class="card bg-white border-4 border-deep-green hover:bg-light-green p-8 text-center transition group">
    <span class="text-5xl mb-4 block group-hover:scale-110 transition">📰</span>
    <h3 class="text-xl font-bold text-deep-green">Publish News</h3>
    <p class="text-sm text-gray-500 mt-2">Share medical updates</p>
</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Live Clock Function
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        const dateString = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        document.getElementById('liveClock').innerText = timeString;
        document.getElementById('liveDate').innerText = dateString;
    }
    
    setInterval(updateTime, 1000);
    updateTime(); // Initial call
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>