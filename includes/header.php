<?php
/**
 * QuickMed - Ultra Dynamic Header (FIXED & ENHANCED)
 */

// 1. SITE_URL Definition
if (!defined('SITE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    define('SITE_URL', $protocol . "://" . $host); 
}

if (!isset($conn)) {
    if(file_exists(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/../config.php';
    } else {
        require_once __DIR__ . '/config.php'; 
    }
}

// Helper Functions
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }
}
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

$currentUser = getCurrentUser();
$cartCount = 0;

// Cart Logic
if (isLoggedIn() && isset($_SESSION['user_id'])) {
    if(isset($conn)) {
        $cartQuery = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($cartQuery);
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $cartResult = $stmt->get_result()->fetch_assoc();
            $cartCount = $cartResult['total'] ?? 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $pageTitle ?? 'QuickMed - Digital Pharmacy' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=VT323&family=Inter:wght@300;400;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= time() ?>">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'deep-green': '#065f46',
                        'lime-accent': '#84cc16',
                        'retro-black': '#0f172a',
                    },
                    fontFamily: {
                        'retro': ['"VT323"', 'monospace'],
                        'mono': ['"IBM Plex Mono"', 'monospace'],
                        'sans': ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'retro-white': '4px 4px 0px #ffffff',
                        'retro-lime': '4px 4px 0px #84cc16',
                    }
                }
            }
        }
    </script>
    
    <style>
        /* PRELOADER */
        #preloader { position: fixed; inset: 0; z-index: 99999; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; transition: opacity 0.3s ease-out; }
        .medicine-spin { font-size: 4rem; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* MARQUEE */
        .marquee-container { overflow: hidden; white-space: nowrap; background: #022c22; color: #ecfccb; font-size: 0.85rem; padding: 8px 0; border-bottom: 1px solid #84cc16; }
        .marquee-content { display: inline-block; padding-left: 100%; animation: marquee 40s linear infinite; }
        @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }

        /* NAVBAR ANIMATION (MEDICINES) */
        .nav-bg-anim { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: -1; opacity: 0.1; }
        .float-item { position: absolute; font-size: 1.5rem; animation: floatAround 15s infinite linear; }
        .float-item:nth-child(1) { top: 10%; left: 10%; animation-duration: 20s; }
        .float-item:nth-child(2) { top: 60%; left: 80%; animation-duration: 25s; animation-delay: -5s; }
        .float-item:nth-child(3) { top: 30%; left: 40%; animation-duration: 18s; animation-delay: -2s; }
        .float-item:nth-child(4) { top: 80%; left: 20%; animation-duration: 22s; animation-delay: -10s; }
        
        @keyframes floatAround {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(20px, 20px) rotate(90deg); }
            50% { transform: translate(0, 40px) rotate(180deg); }
            75% { transform: translate(-20px, 20px) rotate(270deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        .neon-border-bottom { border-bottom: 3px solid #84cc16; box-shadow: 0 4px 15px -5px rgba(132, 204, 22, 0.5); }
        .mobile-bottom-nav { display: none !important; }
        @media (max-width: 768px) {
            .mobile-bottom-nav { display: flex !important; }
            body { padding-bottom: 80px; }
        }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="bg-gray-50 font-sans flex flex-col min-h-screen selection:bg-lime-accent selection:text-deep-green">

    <div id="preloader"><div class="medicine-spin">💊</div></div>
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                const loader = document.getElementById('preloader');
                if(loader) { loader.style.opacity = '0'; setTimeout(() => loader.remove(), 300); }
            }, 500);
        });
    </script>

    <div class="marquee-container">
        <div class="marquee-content">
            🏥 HOTLINE: 09678-100100 &nbsp;&nbsp; • &nbsp;&nbsp; 🚚 FREE DELIVERY ON ORDERS ABOVE 1000৳ &nbsp;&nbsp; • &nbsp;&nbsp; ✅ 100% GENUINE MEDICINES
        </div>
    </div>

    <nav class="sticky top-0 z-50 bg-deep-green/95 backdrop-blur-md shadow-xl neon-border-bottom relative">
        
        <div class="nav-bg-anim">
            <span class="float-item">💊</span>
            <span class="float-item">🧬</span>
            <span class="float-item">🩺</span>
            <span class="float-item">🧪</span>
        </div>

        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-2">
                
                <a href="<?= SITE_URL ?>/index.php" class="inline-flex items-center gap-3 group z-10">
                    <div class="w-12 h-12 bg-[#84cc16] text-[#065f46] rounded-xl flex items-center justify-center text-2xl font-bold shadow-[2px_2px_0px_white] group-hover:rotate-6 transition-transform duration-300">QM</div>
                    <div class="hidden md:block">
                        <h2 class="text-2xl font-mono font-bold tracking-tighter text-white leading-none group-hover:text-lime-accent transition-colors">QuickMed</h2>
                        <p class="text-[10px] text-[#84cc16] tracking-widest uppercase font-bold">Digital Pharmacy</p>
                    </div>
                    <span class="md:hidden text-xl font-mono font-bold text-white">QuickMed</span>
                </a>
                
                <button onclick="openMobileSearch()" class="md:hidden text-lime-accent p-2 border border-lime-accent/50 rounded-lg hover:bg-lime-accent/10 transition z-10">🔍</button>
                
                <div class="hidden md:flex items-center gap-6 text-sm font-bold text-white z-10">
                    
                    <div class="bg-black/40 px-3 py-1.5 rounded-lg border border-lime-accent/30 font-mono text-lime-accent flex items-center gap-2 shadow-inner">
                        <span class="animate-pulse">●</span> <span id="navClock">00:00:00</span>
                    </div>

                    <div class="flex gap-6 tracking-wide font-mono text-gray-200 items-center">
                        <a href="<?= SITE_URL ?>/index.php" class="hover:text-lime-accent hover:-translate-y-0.5 transition-all">HOME</a>
                        <a href="<?= SITE_URL ?>/shop.php" class="hover:text-lime-accent hover:-translate-y-0.5 transition-all">SHOP</a>
                        <a href="<?= SITE_URL ?>/about.php" class="hover:text-lime-accent hover:-translate-y-0.5 transition-all">ABOUT</a>
                        <a href="<?= SITE_URL ?>/contact.php" class="hover:text-lime-accent hover:-translate-y-0.5 transition-all">CONTACT</a>
                    </div>
                    
                    <div class="h-6 w-px bg-white/20 mx-2"></div>

                    <?php if (isLoggedIn() && $currentUser): ?>
                        
                        <?php if (isset($currentUser['role_name']) && $currentUser['role_name'] === 'customer'): ?>
                            <a href="<?= SITE_URL ?>/cart.php" class="hover:text-lime-accent relative group transition-transform hover:scale-110">
                                <span class="text-xl">🛒</span>
                                <?php if ($cartCount > 0): ?>
                                    <span class="absolute -top-2 -right-3 bg-red-500 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-deep-green animate-bounce">
                                        <?= $cartCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <?php 
                            // --- FIXED DASHBOARD LOGIC ---
                            $dashboardLink = SITE_URL . "/views/customer/index.php"; // Default
                            $dashLabel = "DASHBOARD";

                            if(isset($currentUser['role_name'])) {
                                $role = $currentUser['role_name'];
                                if($role === 'admin') {
                                    $dashboardLink = SITE_URL . "/views/admin/dashboard.php";
                                    $dashLabel = "ADMIN";
                                } 
                                elseif($role === 'shop_manager') {
                                    $dashboardLink = SITE_URL . "/views/shop_manager/dashboard.php";
                                    $dashLabel = "MANAGER";
                                }
                                elseif($role === 'doctor') {
                                    $dashboardLink = SITE_URL . "/views/doctor/prescriptions.php";
                                    $dashLabel = "DOCTOR";
                                }
                                elseif($role === 'salesman') {
                                    $dashboardLink = SITE_URL . "/views/salesman/pos.php";
                                    $dashLabel = "POS";
                                }
                            }
                        ?>

                        <a href="<?= $dashboardLink ?>" class="bg-lime-accent text-deep-green px-5 py-2 rounded-lg font-bold border-2 border-lime-accent shadow-[3px_3px_0px_#000] hover:shadow-none hover:translate-x-[3px] hover:translate-y-[3px] transition-all uppercase flex items-center gap-2">
                            <span>⚡</span> <?= $dashLabel ?>
                        </a>
                        
                        <a href="<?= SITE_URL ?>/profile.php" class="hover:text-lime-accent transition-all flex items-center gap-1 font-mono">
                            <span>👤</span> PROFILE
                        </a>
                        
                        <a href="<?= SITE_URL ?>/logout.php" class="text-red-300 hover:text-white px-2 py-1 rounded transition hover:bg-red-500/20">✖</a>
                    
                    <?php else: ?>
                        
                        <a href="<?= SITE_URL ?>/login.php" class="font-mono text-lime-accent border-b-2 border-transparent hover:border-lime-accent hover:text-white transition-all pb-0.5">
                            🔐 LOGIN
                        </a>
                        
                        <a href="<?= SITE_URL ?>/signup.php" class="bg-white text-deep-green px-5 py-2 rounded-lg font-bold border-2 border-white shadow-[4px_4px_0px_#84cc16] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px] transition-all flex items-center gap-2">
                            <span>✍️</span> SIGN UP
                        </a>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-deep-green border-t-4 border-lime-accent z-50 flex justify-around items-center h-16 pb-safe text-white shadow-[0_-5px_20px_rgba(0,0,0,0.3)] hidden">
        <?php if (isLoggedIn()): ?>
            <?php 
                // --- MOBILE DASHBOARD LOGIC (FIXED) ---
                $mDashLink = SITE_URL . "/views/customer/index.php";
                $mLabel = "DASH";
                $mIcon = "📊";
                
                if (isset($currentUser['role_name'])) {
                    $role = $currentUser['role_name'];
                    if ($role === 'salesman') { 
                        $mLabel = "POS"; $mIcon = "🧾"; $mDashLink = SITE_URL . "/views/salesman/pos.php"; 
                    } elseif ($role === 'doctor') { 
                        $mLabel = "RX"; $mIcon = "📋"; $mDashLink = SITE_URL . "/views/doctor/prescriptions.php"; 
                    } elseif ($role === 'shop_manager') { 
                        $mLabel = "MGR"; $mIcon = "💼"; $mDashLink = SITE_URL . "/views/shop_manager/dashboard.php"; 
                    } elseif ($role === 'admin') { 
                        $mLabel = "ADMIN"; $mIcon = "⚡"; $mDashLink = SITE_URL . "/views/admin/dashboard.php"; 
                    }
                }
            ?>
            
            <a href="<?= $mDashLink ?>" class="flex flex-col items-center text-lime-100/70 hover:text-lime-accent active:scale-95 transition">
                <span class="text-xl mb-0.5"><?= $mIcon ?></span>
                <span class="text-[10px] font-bold tracking-wide"><?= $mLabel ?></span>
            </a>

            <a href="<?= SITE_URL ?>/shop.php" class="flex flex-col items-center text-lime-100/70 hover:text-lime-accent active:scale-95 transition">
                <span class="text-xl mb-0.5">🛍️</span>
                <span class="text-[10px] font-bold tracking-wide">SHOP</span>
            </a>

            <?php if (isset($currentUser['role_name']) && $currentUser['role_name'] === 'customer'): ?>
                <a href="<?= SITE_URL ?>/cart.php" class="flex flex-col items-center relative -mt-8 group">
                    <div class="bg-lime-accent p-3.5 rounded-full border-4 border-deep-green shadow-[0_0_15px_rgba(132,204,22,0.6)] group-active:scale-95 transition">
                        <span class="text-deep-green text-xl font-bold">🛒</span>
                    </div>
                    <span class="text-[10px] font-bold mt-1 text-lime-accent">CART</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute top-0 right-0 bg-red-600 text-white text-[9px] w-4 h-4 flex items-center justify-center rounded-full border border-white shadow-sm">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/profile.php" class="flex flex-col items-center relative -mt-8 group">
                    <div class="bg-lime-accent p-3.5 rounded-full border-4 border-deep-green shadow-[0_0_15px_rgba(132,204,22,0.6)] group-active:scale-95 transition">
                         <span class="text-deep-green text-xl font-bold">👤</span>
                    </div>
                    <span class="text-[10px] font-bold mt-1 text-lime-accent">ME</span>
                </a>
            <?php endif; ?>

            <?php if (isset($currentUser['role_name']) && $currentUser['role_name'] === 'customer'): ?>
            <a href="<?= SITE_URL ?>/profile.php" class="flex flex-col items-center text-lime-100/70 hover:text-lime-accent active:scale-95 transition">
                <span class="text-xl mb-0.5">👤</span>
                <span class="text-[10px] font-bold tracking-wide">ME</span>
            </a>
            <?php endif; ?>

            <button onclick="toggleMobileMenu()" class="flex flex-col items-center text-lime-100/70 hover:text-lime-accent active:scale-95 transition">
                <span class="text-xl mb-0.5">☰</span>
                <span class="text-[10px] font-bold tracking-wide">MENU</span>
            </button>

        <?php else: ?>
            <a href="<?= SITE_URL ?>/index.php" class="flex flex-col items-center text-lime-100 hover:text-lime-accent"><span class="text-xl">🏠</span><span class="text-[10px]">Home</span></a>
            <a href="<?= SITE_URL ?>/shop.php" class="flex flex-col items-center text-lime-100 hover:text-lime-accent"><span class="text-xl">🛍️</span><span class="text-[10px]">Shop</span></a>
            <a href="<?= SITE_URL ?>/login.php" class="flex flex-col items-center text-lime-100 hover:text-lime-accent"><span class="text-xl">🔐</span><span class="text-[10px]">Login</span></a>
            <a href="<?= SITE_URL ?>/signup.php" class="flex flex-col items-center text-lime-100 hover:text-lime-accent"><span class="text-xl">✍️</span><span class="text-[10px]">Join</span></a>
        <?php endif; ?>
    </div>

    <div id="mobileMenuOverlay" class="fixed inset-0 bg-deep-green/95 z-[60] hidden backdrop-blur-xl transition-opacity duration-300 flex flex-col justify-center items-center">
        <button onclick="toggleMobileMenu()" class="absolute top-6 right-6 text-4xl text-lime-accent hover:rotate-90 transition-transform">&times;</button>
        
        <?php if (isLoggedIn()): ?>
            <div class="text-center mb-8">
                <div class="w-24 h-24 bg-lime-accent rounded-full flex items-center justify-center text-4xl font-bold text-deep-green mx-auto mb-4 border-4 border-white shadow-[4px_4px_0px_rgba(0,0,0,0.2)]">
                    <?= isset($currentUser['full_name']) ? strtoupper(substr($currentUser['full_name'], 0, 1)) : 'U' ?>
                </div>
                <h3 class="text-2xl font-bold text-white font-mono"><?= htmlspecialchars($currentUser['full_name'] ?? 'User') ?></h3>
                <span class="bg-white/20 text-lime-accent px-3 py-1 rounded-full text-xs uppercase tracking-widest mt-2 inline-block border border-white/10"><?= $currentUser['role_name'] ?? 'Member' ?></span>
            </div>
            
            <div class="grid grid-cols-1 gap-4 w-64 text-center text-lg font-bold text-white font-mono">
                <a href="<?= SITE_URL ?>/profile.php" class="py-2 border-b border-white/10 hover:text-lime-accent hover:translate-x-2 transition-all">👤 MY PROFILE</a>
                <a href="<?= SITE_URL ?>/about.php" class="py-2 border-b border-white/10 hover:text-lime-accent hover:translate-x-2 transition-all">ℹ️ ABOUT US</a>
                <a href="<?= SITE_URL ?>/contact.php" class="py-2 border-b border-white/10 hover:text-lime-accent hover:translate-x-2 transition-all">📞 CONTACT</a>
                <a href="<?= SITE_URL ?>/logout.php" class="bg-red-500/80 py-3 rounded-xl hover:bg-red-600 transition mt-4 shadow-[4px_4px_0px_#000] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px]">🚪 LOGOUT</a>
            </div>
        <?php endif; ?>
    </div>

    <div id="mobileSearchModal" class="hidden fixed inset-0 bg-black/95 z-[99999] p-4 flex flex-col backdrop-blur-md">
        <div class="flex justify-between items-center mb-6 border-b border-lime-accent/30 pb-4">
            <h3 class="text-lime-accent text-xl font-mono font-bold tracking-widest">SEARCH DATABASE</h3>
            <button onclick="closeMobileSearch()" class="text-white text-4xl hover:text-red-500 transition">&times;</button>
        </div>
        <input type="text" id="mobileSearchInput" class="w-full bg-gray-900 border-2 border-lime-accent text-lime-accent p-4 rounded-xl text-xl focus:outline-none focus:shadow-[0_0_20px_rgba(132,204,22,0.3)] font-mono placeholder-gray-600" placeholder="MEDICINE NAME..." autocomplete="off">
        <div id="mobileSearchResults" class="mt-4 flex-1 overflow-y-auto font-mono text-white space-y-2"></div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const el = document.getElementById('navClock');
            if(el) el.textContent = now.toLocaleTimeString('en-US', { hour12: true });
        }
        setInterval(updateClock, 1000); updateClock();

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenuOverlay');
            menu.classList.toggle('hidden');
            document.body.style.overflow = menu.classList.contains('hidden') ? '' : 'hidden';
        }

        function openMobileSearch() {
            document.getElementById('mobileSearchModal').classList.remove('hidden');
            document.getElementById('mobileSearchInput').focus();
        }
        function closeMobileSearch() {
            document.getElementById('mobileSearchModal').classList.add('hidden');
        }
    </script>

    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <script>
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            background: '#065f46', color: '#fff', iconColor: '#84cc16'
        });
        <?php if (isset($_SESSION['success'])): ?>
            Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            Toast.fire({ icon: 'error', title: '<?= $_SESSION['error'] ?>', background: '#ef4444' });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>