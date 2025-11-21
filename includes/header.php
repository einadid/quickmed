<?php
/**
 * QuickMed - Global Header with Mobile Bottom Navbar - FIXED (Floating Cart & Menu)
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
        /* Mobile Bottom Navigation Styles */
        .mobile-bottom-nav {
            display: none;
        }
        
        /* PB-SAFE utility for iPhones with home indicator */
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom);
        }

        @media (max-width: 768px) {
            .mobile-bottom-nav {
                display: flex;
            }
            
            body {
                /* Increased padding to account for floating button space */
                padding-bottom: 90px;
            }
        }
        
        /* Remove old hover styles as we are using Tailwind classes in the new nav */
        .mobile-nav-item {
            transition: all 0.3s ease;
        }
        
        /* Fade In Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</head>
<body class="retro-texture">

    <div id="preloader" class="fixed inset-0 bg-[#065f46] z-[99999] flex flex-col items-center justify-center transition-opacity duration-700">
        <div class="relative">
            <div class="w-24 h-24 border-8 border-lime-accent/30 border-t-lime-accent rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center animate-pulse">
                <span class="text-5xl">💊</span>
            </div>
        </div>
        <h2 class="text-white text-2xl font-mono font-bold mt-6 tracking-[0.5em] animate-bounce">LOADING...</h2>
    </div>
    <script>
        // Hide Preloader after Page Load
        window.addEventListener('load', () => {
            const loader = document.getElementById('preloader');
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => loader.remove(), 700);
            }, 1500); // 1.5 Seconds Delay for Effect
        });
    </script>

    <div class="hidden md:block bg-deep-green text-white py-2 text-sm">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span class="animate-pulse">📞 Hotline: 09678-100100</span>
                <span>📧 support@quickmed.com</span>
                <span>🚚 Free Delivery on Orders Above 1000৳</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="?lang=<?= getOppositeLang() ?>" class="hover:text-lime-accent transition-colors duration-300 transform hover:scale-110">
                    🌐 <?= getOppositeLangName() ?>
                </a>
                
                <?php if (isLoggedIn() && $currentUser): ?>
                    <span><?= htmlspecialchars($currentUser['full_name']) ?></span>
                    <?php if (isset($currentUser['role_name']) && $currentUser['role_name'] === 'customer'): ?>
                        <span class="bg-lime-accent text-deep-green px-3 py-1 font-bold border-2 border-white glow-green">
                            ⭐ <?= $currentUser['points'] ?> Points
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="navbar sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="<?= SITE_URL ?>/index.php" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                    <div class="bg-lime-accent text-deep-green px-4 py-2 border-2 border-white font-bold text-2xl md:text-3xl">
                        QM💊
                    </div>
                    <span class="hidden md:inline text-white font-bold text-xl">QuickMed</span>
                </a>
                
                <button onclick="openMobileSearch()" class="md:hidden bg-lime-accent text-deep-green px-4 py-2 border-2 border-white font-bold">
                    🔍
                </button>
                
                <div class="hidden md:flex items-center gap-2">
                    <a href="<?= SITE_URL ?>/index.php" class="navbar-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <span class="text-xl">🏠</span> <span><?= __('home') ?></span>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/shop.php" class="navbar-link <?= basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : '' ?>">
                        <span class="text-xl">🛍️</span> <span><?= __('shop') ?></span>
                    </a>
                    
                    <?php if (isLoggedIn() && $currentUser): ?>
                        <?php if ($currentUser['role_name'] === 'customer'): ?>
                            <a href="<?= SITE_URL ?>/cart.php" class="navbar-link relative <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">
                                <span class="text-xl">🛒</span> <span><?= __('cart') ?></span>
                                <?php if ($cartCount > 0): ?>
                                    <span class="absolute -top-2 -right-2 bg-lime-accent text-deep-green text-xs font-bold px-2 py-1 border-2 border-white animate-bounce">
                                        <?= $cartCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            
                            <a href="<?= SITE_URL ?>/my-orders.php" class="navbar-link <?= basename($_SERVER['PHP_SELF']) == 'my-orders.php' ? 'active' : '' ?>">
                                <span class="text-xl">📦</span> <span><?= __('orders') ?></span>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= SITE_URL ?>/profile.php" class="navbar-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                            <span class="text-xl">👤</span> <span><?= __('profile') ?></span>
                        </a>
                        
                        <a href="<?= SITE_URL ?>/views/<?= $currentUser['role_name'] ?>/dashboard.php" class="navbar-link">
                            <span class="text-xl">📊</span> <span><?= __('dashboard') ?></span>
                        </a>
                        
                        <a href="<?= SITE_URL ?>/logout.php" class="navbar-link">
                            <span class="text-xl">🚪</span> <span><?= __('logout') ?></span>
                        </a>
                    
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/login.php" class="navbar-link <?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">
                            <span class="text-xl">🔐</span> <?= __('login') ?>
                        </a>
                        
                        <a href="<?= SITE_URL ?>/signup.php" class="navbar-link bg-lime-accent text-deep-green border-2 border-white <?= basename($_SERVER['PHP_SELF']) == 'signup.php' ? 'active' : '' ?>">
                            <span class="text-xl">✍️</span> <?= __('signup') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t shadow-[0_-4px_10px_rgba(0,0,0,0.05)] z-50 flex justify-around items-center h-16 pb-safe md:hidden">
        <?php if (isLoggedIn()): ?>
            
            <?php if (in_array($currentUser['role_name'], ['admin', 'shop_manager', 'salesman', 'doctor'])): ?>
                <a href="<?= SITE_URL ?>/views/<?= $currentUser['role_name'] ?>/dashboard.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-deep-green' : '' ?>">
                    <span class="text-xl">📊</span>
                    <span class="text-[10px] font-bold uppercase">Dash</span>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/index.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-deep-green' : '' ?>">
                    <span class="text-xl">🏠</span>
                    <span class="text-[10px] font-bold uppercase">Home</span>
                </a>
            <?php endif; ?>

            <?php if ($currentUser['role_name'] === 'salesman'): ?>
                <a href="<?= SITE_URL ?>/views/salesman/pos.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'pos.php' ? 'text-deep-green' : '' ?>">
                    <span class="text-xl">🧾</span>
                    <span class="text-[10px] font-bold uppercase">POS</span>
                </a>
            <?php elseif ($currentUser['role_name'] === 'shop_manager'): ?>
                <a href="<?= SITE_URL ?>/views/shop_manager/inventory.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'text-deep-green' : '' ?>">
                    <span class="text-xl">📦</span>
                    <span class="text-[10px] font-bold uppercase">Stock</span>
                </a>
            <?php elseif ($currentUser['role_name'] === 'doctor'): ?>
                <a href="<?= SITE_URL ?>/views/doctor/prescriptions.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'prescriptions.php' ? 'text-deep-green' : '' ?>">
                    <span class="text-xl">📋</span>
                    <span class="text-[10px] font-bold uppercase">Rx</span>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/shop.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'text-deep-green' : '' ?>">
                    <span class="text-xl">🛍️</span>
                    <span class="text-[10px] font-bold uppercase">Shop</span>
                </a>
            <?php endif; ?>

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
                <a href="<?= SITE_URL ?>/profile.php" class="flex flex-col items-center text-gray-600 hover:text-deep-green <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'text-deep-green' : '' ?>">
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

    <div id="mobileMenuOverlay" class="fixed inset-0 bg-deep-green bg-opacity-95 z-[60] hidden flex flex-col items-center justify-center text-white space-y-6 transition-opacity duration-300 backdrop-blur-sm">
        <button onclick="toggleMobileMenu()" class="absolute top-6 right-6 text-4xl text-lime-accent hover:rotate-90 transition-transform">&times;</button>
        
        <?php if (isLoggedIn()): ?>
            <div class="text-center animate-fade-in">
                <div class="w-20 h-20 bg-lime-accent rounded-full flex items-center justify-center text-3xl font-bold text-deep-green mx-auto mb-3 border-4 border-white">
                    <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                </div>
                <h3 class="text-xl font-bold"><?= htmlspecialchars($currentUser['full_name']) ?></h3>
                <p class="text-sm text-lime-200 uppercase tracking-widest"><?= $currentUser['role_name'] ?></p>
            </div>
            
            <div class="grid grid-cols-2 gap-6 w-full max-w-xs px-4">
                <a href="<?= SITE_URL ?>/profile.php" class="bg-white/10 p-4 rounded-xl text-center hover:bg-white/20 transition">
                    <span class="text-2xl block mb-1">👤</span> Profile
                </a>
                
                <?php if ($currentUser['role_name'] === 'customer'): ?>
                    <a href="<?= SITE_URL ?>/my-orders.php" class="bg-white/10 p-4 rounded-xl text-center hover:bg-white/20 transition">
                        <span class="text-2xl block mb-1">📦</span> Orders
                    </a>
                    <a href="<?= SITE_URL ?>/prescription-upload.php" class="bg-white/10 p-4 rounded-xl text-center hover:bg-white/20 transition">
                        <span class="text-2xl block mb-1">📋</span> Rx Upload
                    </a>
                <?php endif; ?>

                <?php if ($currentUser['role_name'] === 'admin'): ?>
                    <a href="<?= SITE_URL ?>/views/admin/reports.php" class="bg-white/10 p-4 rounded-xl text-center hover:bg-white/20 transition">
                        <span class="text-2xl block mb-1">📊</span> Reports
                    </a>
                <?php endif; ?>
                
                <a href="<?= SITE_URL ?>/logout.php" class="bg-red-500/80 p-4 rounded-xl text-center hover:bg-red-600 transition col-span-2">
                    <span class="text-xl block mb-1">🚪</span> Logout
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div id="mobileSearchModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-[99999]">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-white text-xl font-bold">Search Medicines</h3>
                <button onclick="closeMobileSearch()" class="text-white text-3xl">&times;</button>
            </div>
            
            <div class="relative">
                <input 
                    type="text" 
                    id="mobileSearchInput" 
                    class="w-full px-6 py-4 text-lg border-4 border-lime-accent focus:outline-none" 
                    placeholder="🔍 Search medicines..."
                    autocomplete="off"
                >
                
                <div id="mobileSearchResults" class="mt-2 bg-white max-h-[70vh] overflow-y-auto hidden">
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="alert alert-success animate__animated animate__fadeInDown" data-aos="fade-down">
                ✅ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="alert alert-error shake" data-aos="fade-down">
                ❌ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="alert alert-warning" data-aos="fade-down">
                ⚠️ <?= htmlspecialchars($_SESSION['warning']) ?>
            </div>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="alert alert-info" data-aos="fade-down">
                ℹ️ <?= htmlspecialchars($_SESSION['info']) ?>
            </div>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <script>
    // Toggle Mobile Menu Overlay
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenuOverlay');
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        } else {
            menu.classList.add('hidden');
            document.body.style.overflow = ''; // Enable scrolling
        }
    }

    // Mobile Search Functions
    function openMobileSearch() {
        document.getElementById('mobileSearchModal').classList.remove('hidden');
        document.getElementById('mobileSearchInput').focus();
    }

    function closeMobileSearch() {
        document.getElementById('mobileSearchModal').classList.add('hidden');
        document.getElementById('mobileSearchInput').value = '';
        document.getElementById('mobileSearchResults').classList.add('hidden');
    }

    // Mobile Search Functionality
    let mobileSearchTimeout;
    const mobileSearchInput = document.getElementById('mobileSearchInput');
    const mobileSearchResults = document.getElementById('mobileSearchResults');

    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('input', function() {
            clearTimeout(mobileSearchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                mobileSearchResults.classList.add('hidden');
                return;
            }

            mobileSearchResults.classList.remove('hidden');
            mobileSearchResults.innerHTML = '<div class="p-4 text-center">Searching...</div>';

            mobileSearchTimeout = setTimeout(async function() {
                try {
                    const siteUrl = window.location.origin + '/quickmed';
                    const response = await fetch(siteUrl + '/ajax/search_medicine.php?q=' + encodeURIComponent(query));
                    const results = await response.json();

                    if (results.length === 0) {
                        mobileSearchResults.innerHTML = '<div class="p-8 text-center text-gray-500">No medicines found</div>';
                        return;
                    }

                    let html = '';
                    results.forEach(function(item) {
                        const imagePath = item.image ? item.image : 'placeholder.png';
                        html += `
                            <div class="p-4 border-b-2 border-gray-200" onclick="addToCart(${item.id}, ${item.shop_id}, 1); closeMobileSearch();">
                                <div class="flex items-center gap-3">
                                    <img src="${siteUrl}/uploads/medicines/${imagePath}" class="w-16 h-16 object-contain border-2 border-deep-green">
                                    <div class="flex-1">
                                        <p class="font-bold text-deep-green">${item.name}</p>
                                        <p class="text-sm text-gray-600">${item.power}</p>
                                        <p class="text-lg font-bold text-lime-accent">৳${item.price}</p>
                                    </div>
                                    <button class="bg-deep-green text-white px-3 py-2 text-sm font-bold">Add</button>
                                </div>
                            </div>
                        `;
                    });

                    mobileSearchResults.innerHTML = html;

                } catch (error) {
                    console.error('Search error:', error);
                    mobileSearchResults.innerHTML = '<div class="p-8 text-center text-red-600">Search failed</div>';
                }
            }, 300);
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Global Toast Notification Function
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Check for PHP Session Messages
    <?php if (isset($_SESSION['success'])): ?>
        Toast.fire({
            icon: 'success',
            title: '<?= $_SESSION['success'] ?>'
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Toast.fire({
            icon: 'error',
            title: '<?= $_SESSION['error'] ?>'
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    </script>