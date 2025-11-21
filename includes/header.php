<?php
/**
 * QuickMed - Professional Header
 * Features: Glitch Preloader, Marquee, Live Clock, Expanded Menu
 */
if (!isset($conn)) {
    require_once __DIR__ . '/../config.php';
}

$currentUser = getCurrentUser();
$cartCount = 0;

// Get cart count for logged-in customers
if (isLoggedIn() && $currentUser && isset($currentUser['role_name']) && $currentUser['role_name'] === 'customer') {
    $cartQuery = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($cartQuery);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $cartResult = $stmt->get_result()->fetch_assoc();
    $cartCount = $cartResult['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $pageTitle ?? 'QuickMed - Your Trusted Online Pharmacy' ?></title>
    <meta name="description" content="<?= $pageDescription ?? 'QuickMed - Buy genuine medicines online with home delivery across Bangladesh' ?>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'deep-green': '#065f46',
                        'lime-accent': '#84cc16',
                        'off-white': '#f8fafc',
                        'light-green': '#ecfccb',
                    },
                    fontFamily: {
                        'mono': ['IBM Plex Mono', 'Courier New', 'monospace'],
                        'sans': ['Inter', 'Arial', 'sans-serif'],
                    },
                    animation: {
                        'marquee': 'marquee 25s linear infinite',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(100%)' },
                            '100%': { transform: 'translateX(-100%)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">
    
    <style>
        /* Mobile Bottom Navigation */
        .mobile-bottom-nav { display: none; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }

        @media (max-width: 768px) {
            .mobile-bottom-nav { display: flex; }
            body { padding-bottom: 90px; }
        }
        
        .mobile-nav-item { transition: all 0.3s ease; }
        
        /* Fade In Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }

        /* Glitch Effect for Preloader */
        .glitch-text {
            position: relative;
            animation: glitch-skew 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite both;
        }
        .glitch-text::before, .glitch-text::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .glitch-text::before {
            left: 2px;
            text-shadow: -2px 0 #ff00c1;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim 5s infinite linear alternate-reverse;
        }
        .glitch-text::after {
            left: -2px;
            text-shadow: -2px 0 #00fff9;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim 5s infinite linear alternate-reverse;
        }
        @keyframes glitch-anim {
            0% { clip: rect(12px, 9999px, 32px, 0); }
            100% { clip: rect(54px, 9999px, 12px, 0); }
        }
        @keyframes glitch-skew {
            0% { transform: skew(0deg); }
            20% { transform: skew(-2deg); }
            40% { transform: skew(2deg); }
            100% { transform: skew(0deg); }
        }
    </style>
</head>
<body class="retro-texture bg-gray-50">

    <div id="preloader" class="fixed inset-0 bg-[#0a0a0a] z-[99999] flex flex-col items-center justify-center transition-opacity duration-700">
        <div class="relative mb-8">
            <div class="w-32 h-32 border-4 border-lime-accent/20 border-t-lime-accent rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center animate-bounce">
                <span class="text-6xl filter drop-shadow-[0_0_10px_rgba(132,204,22,0.8)]">💊</span>
            </div>
        </div>
        <h2 class="text-lime-accent text-3xl font-mono font-bold tracking-widest glitch-text" data-text="SYSTEM INITIALIZING...">
            SYSTEM INITIALIZING...
        </h2>
        <div class="w-64 h-1 bg-gray-800 mt-4 rounded overflow-hidden">
            <div class="h-full bg-lime-accent animate-[width_2s_ease-in-out_forwards]" style="width: 0%"></div>
        </div>
    </div>
    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('preloader');
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => loader.remove(), 700);
            }, 1200);
        });
    </script>

    <div class="hidden md:flex bg-deep-green text-white h-10 text-sm overflow-hidden relative z-50 items-center">
        
        <div class="flex-1 overflow-hidden relative h-full flex items-center bg-black/20">
            <div class="whitespace-nowrap animate-marquee hover:[animation-play-state:paused] flex gap-8 items-center px-4">
                <span class="font-bold text-lime-accent">⚡ LATEST UPDATES:</span>
                <span>📞 Hotline: 09678-100100 (24/7)</span>
                <span>🚚 <span class="text-yellow-300">FREE DELIVERY</span> on orders above 1000৳</span>
                <span>💊 100% Genuine Medicines Guaranteed</span>
                <span>📧 Email: support@quickmed.com</span>
            </div>
        </div>

        <div class="flex items-center gap-4 px-4 bg-deep-green h-full border-l border-white/10 z-10 shadow-[-5px_0_10px_rgba(0,0,0,0.2)]">
            
            <div class="flex items-center gap-2 font-mono text-lime-accent bg-black/20 px-2 py-0.5 rounded">
                <span>🕒</span>
                <span id="liveHeaderClock">00:00:00 AM</span>
            </div>

            <a href="?lang=<?= getOppositeLang() ?>" class="hover:text-lime-accent transition-transform hover:scale-110">
                🌐 <?= getOppositeLangName() ?>
            </a>
            
            <?php if (isLoggedIn() && $currentUser): ?>
                <span class="font-bold text-white"><?= htmlspecialchars($currentUser['full_name']) ?></span>
                <?php if (isset($currentUser['role_name']) && $currentUser['role_name'] === 'customer'): ?>
                    <span class="bg-lime-accent text-deep-green text-xs px-2 py-0.5 font-bold rounded">
                        <?= $currentUser['points'] ?> pts
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <nav class="sticky top-0 z-50 bg-white shadow-md border-b-2 border-lime-accent hidden md:block">
        <div class="container mx-auto px-4 flex justify-between items-center py-3">
            
            <a href="<?= SITE_URL ?>/index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-deep-green rounded flex items-center justify-center text-white font-bold text-xl shadow-md group-hover:rotate-12 transition-transform">QM</div>
                <div class="flex flex-col">
                    <span class="text-2xl font-bold text-deep-green tracking-tighter leading-none">QuickMed</span>
                    <span class="text-[10px] text-gray-500 tracking-widest font-mono">ONLINE PHARMACY</span>
                </div>
            </a>
            
            <div class="flex items-center gap-6 text-sm font-bold text-gray-600">
                <a href="<?= SITE_URL ?>/index.php" class="nav-item hover:text-deep-green transition-colors relative group">
                    HOME
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-deep-green transition-all group-hover:w-full"></span>
                </a>
                <a href="<?= SITE_URL ?>/shop.php" class="nav-item hover:text-deep-green transition-colors relative group">
                    SHOP
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-deep-green transition-all group-hover:w-full"></span>
                </a>
                
                <a href="<?= SITE_URL ?>/about.php" class="nav-item hover:text-deep-green transition-colors relative group">
                    ABOUT US
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-deep-green transition-all group-hover:w-full"></span>
                </a>
                <a href="<?= SITE_URL ?>/blog.php" class="nav-item hover:text-deep-green transition-colors relative group">
                    BLOG
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-deep-green transition-all group-hover:w-full"></span>
                </a>
                <a href="<?= SITE_URL ?>/contact.php" class="nav-item hover:text-deep-green transition-colors relative group">
                    CONTACT
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-deep-green transition-all group-hover:w-full"></span>
                </a>
                
                <?php if (isLoggedIn() && $currentUser): ?>
                    <div class="h-5 w-px bg-gray-300 mx-2"></div> <?php if ($currentUser['role_name'] === 'customer'): ?>
                        <a href="<?= SITE_URL ?>/cart.php" class="hover:text-deep-green relative group">
                            <span class="text-lg">🛒</span> CART
                            <?php if ($cartCount > 0): ?>
                                <span class="absolute -top-3 -right-3 bg-lime-accent text-deep-green text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white shadow-sm animate-bounce">
                                    <?= $cartCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <a href="<?= SITE_URL ?>/views/<?= $currentUser['role_name'] ?>/dashboard.php" class="hover:text-deep-green uppercase border-2 border-transparent hover:border-deep-green px-3 py-1 rounded transition-all">
                        <?= ($currentUser['role_name'] === 'admin') ? 'ADMIN PANEL' : 'DASHBOARD' ?>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/profile.php" class="hover:text-deep-green">PROFILE</a>
                    <a href="<?= SITE_URL ?>/logout.php" class="text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1 rounded transition-colors">LOGOUT</a>
                
                <?php else: ?>
                    <div class="h-5 w-px bg-gray-300 mx-2"></div> <a href="<?= SITE_URL ?>/login.php" class="hover:text-deep-green">LOGIN</a>
                    <a href="<?= SITE_URL ?>/signup.php" class="bg-deep-green text-white px-5 py-2 rounded hover:bg-lime-accent hover:text-deep-green transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">SIGN UP</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="md:hidden sticky top-0 z-50 bg-white shadow-sm border-b-2 border-lime-accent">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="<?= SITE_URL ?>/index.php" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-deep-green rounded flex items-center justify-center text-white font-bold text-lg">QM</div>
                <span class="text-xl font-bold text-deep-green">QuickMed</span>
            </a>
            <div class="flex gap-3 items-center">
                <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded" id="mobileClock">00:00</span>
                <button onclick="openMobileSearch()" class="text-2xl text-deep-green">
                    🔍
                </button>
            </div>
        </div>
    </div>

    <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t shadow-[0_-4px_10px_rgba(0,0,0,0.05)] z-50 flex justify-around items-center h-16 pb-safe md:hidden">
        <?php if (isLoggedIn()): ?>
            <?php 
            $dashLink = SITE_URL . "/views/" . $currentUser['role_name'] . "/dashboard.php";
            $roleLabel = "Dash";
            $roleIcon = "📊";
            if ($currentUser['role_name'] === 'salesman') { $roleLabel = "POS"; $roleIcon = "🧾"; $dashLink = SITE_URL . "/views/salesman/pos.php"; }
            elseif ($currentUser['role_name'] === 'shop_manager') { $roleLabel = "Stock"; $roleIcon = "📦"; $dashLink = SITE_URL . "/views/shop_manager/inventory.php"; }
            elseif ($currentUser['role_name'] === 'doctor') { $roleLabel = "Rx"; $roleIcon = "📋"; $dashLink = SITE_URL . "/views/doctor/prescriptions.php"; }
            ?>

            <a href="<?= $dashLink ?>" class="flex flex-col items-center text-gray-600 hover:text-deep-green">
                <span class="text-xl"><?= $roleIcon ?></span>
                <span class="text-[10px] font-bold uppercase"><?= $roleLabel ?></span>
            </a>

            <a href="<?= SITE_URL ?>/shop.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green">
                <span class="text-xl">🛍️</span>
                <span class="text-[10px] font-bold uppercase">Shop</span>
            </a>

            <?php if ($currentUser['role_name'] === 'customer'): ?>
                <a href="<?= SITE_URL ?>/cart.php" class="flex flex-col items-center relative text-gray-600 hover:text-deep-green">
                    <div class="absolute -top-5 bg-deep-green p-2.5 rounded-full border-4 border-white shadow-lg transform active:scale-95 transition">
                        <span class="text-white text-lg">🛒</span>
                    </div>
                    <span class="mt-6 text-[10px] font-bold uppercase">Cart</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute top-[-15px] right-[-5px] bg-lime-accent text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full border border-white">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/profile.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green">
                    <span class="text-xl">👤</span>
                    <span class="text-[10px] font-bold uppercase">Profile</span>
                </a>
            <?php endif; ?>

            <button onclick="toggleMobileMenu()" class="flex flex-col items-center text-gray-600 hover:text-deep-green">
                <span class="text-xl">☰</span>
                <span class="text-[10px] font-bold uppercase">Menu</span>
            </button>

        <?php else: ?>
            <a href="<?= SITE_URL ?>/index.php" class="flex flex-col items-center"><span class="text-xl">🏠</span><span class="text-[10px]">Home</span></a>
            <a href="<?= SITE_URL ?>/shop.php" class="flex flex-col items-center"><span class="text-xl">🛍️</span><span class="text-[10px]">Shop</span></a>
            <a href="<?= SITE_URL ?>/login.php" class="flex flex-col items-center"><span class="text-xl">🔐</span><span class="text-[10px]">Login</span></a>
            <a href="<?= SITE_URL ?>/signup.php" class="flex flex-col items-center"><span class="text-xl">✍️</span><span class="text-[10px]">Join</span></a>
        <?php endif; ?>
    </div>

    <div id="mobileMenuOverlay" class="fixed inset-0 bg-deep-green bg-opacity-95 z-[60] hidden transition-opacity duration-300 overflow-y-auto">
        <div class="min-h-screen flex flex-col items-center justify-center py-10 relative">
            <button onclick="toggleMobileMenu()" class="absolute top-6 right-6 text-4xl text-lime-accent hover:rotate-90 transition-transform">&times;</button>
            
            <?php if (isLoggedIn()): ?>
                <div class="text-center mb-8 animate-fade-in">
                    <div class="w-24 h-24 bg-lime-accent rounded-full flex items-center justify-center text-4xl font-bold text-deep-green mx-auto mb-4 border-4 border-white shadow-lg">
                        <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                    </div>
                    <h3 class="text-2xl font-bold text-white"><?= htmlspecialchars($currentUser['full_name']) ?></h3>
                    <p class="text-sm text-lime-200 uppercase tracking-widest mt-1 border-b border-lime-200/30 pb-1 inline-block">
                        <?= ucfirst(str_replace('_', ' ', $currentUser['role_name'])) ?>
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 w-full max-w-sm px-6 mb-8 animate-fade-in">
                    <a href="<?= SITE_URL ?>/profile.php" class="menu-card"><span class="icon">👤</span> Profile</a>
                    <?php if ($currentUser['role_name'] === 'customer'): ?>
                        <a href="<?= SITE_URL ?>/shop.php" class="menu-card"><span class="icon">🛍️</span> Shop</a>
                        <a href="<?= SITE_URL ?>/my-orders.php" class="menu-card"><span class="icon">📦</span> Orders</a>
                        <a href="<?= SITE_URL ?>/prescription-upload.php" class="menu-card"><span class="icon">📋</span> Upload Rx</a>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/about.php" class="menu-card"><span class="icon">ℹ️</span> About</a>
                    <a href="<?= SITE_URL ?>/contact.php" class="menu-card"><span class="icon">📞</span> Contact</a>
                </div>
                
                <a href="<?= SITE_URL ?>/logout.php" class="w-full max-w-xs bg-red-500/20 border border-red-500/50 text-red-200 py-3 rounded-xl font-bold hover:bg-red-500 hover:text-white transition flex items-center justify-center gap-2">
                    <span>🚪</span> LOGOUT
                </a>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4 w-full max-w-xs px-6">
                    <a href="<?= SITE_URL ?>/index.php" class="menu-card"><span class="icon">🏠</span> Home</a>
                    <a href="<?= SITE_URL ?>/shop.php" class="menu-card"><span class="icon">🛍️</span> Shop</a>
                    <a href="<?= SITE_URL ?>/contact.php" class="menu-card"><span class="icon">📞</span> Contact</a>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <a href="<?= SITE_URL ?>/login.php" class="bg-lime-accent text-deep-green py-3 rounded-xl font-bold text-center shadow-lg">Login</a>
                        <a href="<?= SITE_URL ?>/signup.php" class="bg-white text-deep-green py-3 rounded-xl font-bold text-center shadow-lg">Sign Up</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="mobileSearchModal" class="hidden fixed inset-0 bg-black/90 z-[99999] p-4 flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-white text-xl font-bold font-mono">SEARCH DATABASE</h3>
            <button onclick="closeMobileSearch()" class="text-white text-4xl">&times;</button>
        </div>
        <input type="text" id="mobileSearchInput" class="w-full bg-gray-800 border-2 border-lime-accent text-white rounded-lg p-4 text-lg focus:outline-none focus:shadow-[0_0_15px_rgba(132,204,22,0.5)]" placeholder="Type medicine name..." autocomplete="off">
        <div id="mobileSearchResults" class="mt-4 overflow-y-auto flex-1 space-y-2"></div>
    </div>

    <script>
        // Live Clock Logic
        function updateLiveClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: true });
            const deskClock = document.getElementById('liveHeaderClock');
            const mobClock = document.getElementById('mobileClock');
            
            if(deskClock) deskClock.innerText = timeString;
            if(mobClock) mobClock.innerText = timeString;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // Mobile Menu Logic
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenuOverlay');
            menu.classList.toggle('hidden');
            document.body.style.overflow = menu.classList.contains('hidden') ? '' : 'hidden';
        }

        // Mobile Search Logic
        function openMobileSearch() {
            document.getElementById('mobileSearchModal').classList.remove('hidden');
            document.getElementById('mobileSearchInput').focus();
        }
        function closeMobileSearch() {
            document.getElementById('mobileSearchModal').classList.add('hidden');
            document.getElementById('mobileSearchInput').value = '';
            document.getElementById('mobileSearchResults').innerHTML = '';
        }

        // Search AJAX
        let mobileSearchTimeout;
        const mobileSearchInput = document.getElementById('mobileSearchInput');
        if (mobileSearchInput) {
            mobileSearchInput.addEventListener('input', function() {
                clearTimeout(mobileSearchTimeout);
                const query = this.value.trim();
                if (query.length < 2) {
                    document.getElementById('mobileSearchResults').innerHTML = '';
                    return;
                }
                document.getElementById('mobileSearchResults').innerHTML = '<div class="text-white text-center">Searching...</div>';
                mobileSearchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch('<?= SITE_URL ?>/ajax/search_medicine.php?q=' + encodeURIComponent(query));
                        const results = await response.json();
                        let html = '';
                        if (results.length === 0) html = '<div class="text-gray-400 text-center">No medicines found</div>';
                        else {
                            results.forEach(item => {
                                html += `
                                    <div class="bg-gray-800 p-3 rounded flex justify-between items-center text-white border border-gray-700" onclick="window.location.href='<?= SITE_URL ?>/shop.php?search=${item.name}'">
                                        <div>
                                            <p class="font-bold text-lime-accent">${item.name}</p>
                                            <p class="text-xs text-gray-400">${item.power}</p>
                                        </div>
                                        <span class="font-bold">৳${item.price}</span>
                                    </div>
                                `;
                            });
                        }
                        document.getElementById('mobileSearchResults').innerHTML = html;
                    } catch (e) { console.error(e); }
                }, 300);
            });
        }

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 4000);

        // Toast Notifications
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        <?php if (isset($_SESSION['success'])): ?>
            Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            Toast.fire({ icon: 'error', title: '<?= $_SESSION['error'] ?>' });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>