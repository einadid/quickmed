<?php
/**
 * QuickMed - Login Page (FIXED)
 */

require_once 'config.php';

// Helper function to handle redirection safely
if (!function_exists('safeRedirect')) {
    function safeRedirect($path) {
        if (!headers_sent()) {
            header("Location: " . SITE_URL . "/" . ltrim($path, '/'));
            exit();
        } else {
            echo "<script>window.location.href='" . SITE_URL . "/" . ltrim($path, '/') . "';</script>";
            exit();
        }
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    // Default to customer if role is missing, otherwise go to specific role dashboard
    $role = $user['role_name'] ?? 'customer';
    safeRedirect("views/$role/dashboard.php");
}

$pageTitle = 'Login - QuickMed';

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check (Assuming you have this helper)
    if (isset($_POST['csrf_token']) && !verifyCSRFToken($_POST['csrf_token'])) {
         $_SESSION['error'] = 'Security validation failed. Please try again.';
    } else {
        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember_me']);
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please enter both email and password';
        } else {
            // Check user credentials - Join with Roles table
            $query = "SELECT u.*, r.name as role_name 
                      FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      WHERE u.email = ? AND u.is_active = 1 AND u.is_banned = 0";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password_hash'])) {
                    
                    // --- CRITICAL FIX: SESSION SETUP ---
                    session_regenerate_id(true); // Prevent Session Fixation
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role_name'] = $user['role_name']; // Matches database role (e.g., 'shop_manager')
                    $_SESSION['username'] = $user['username'];
                    
                    // Store full user array for easy access to shop_id/email/etc in other files
                    $_SESSION['user'] = $user; 
                    
                    // Update last login timestamp
                    $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    
                    // Handle "Remember Me"
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        // 30 Days expiration
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days')); 
                        
                        // Save token to database
                        $tokenQuery = "INSERT INTO sessions (user_id, token, user_agent, ip_address, expires_at) 
                                       VALUES (?, ?, ?, ?, ?)";
                        $tokenStmt = $conn->prepare($tokenQuery);
                        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                        $ipAddress = $_SERVER['REMOTE_ADDR'];
                        $tokenStmt->bind_param("issss", $user['id'], $token, $userAgent, $ipAddress, $expires);
                        $tokenStmt->execute();
                        
                        // Set secure cookie
                        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
                    }
                    
                    // Log audit (Optional if function exists)
                    if(function_exists('logAudit')) {
                        logAudit('USER_LOGIN', 'users', $user['id']);
                    }
                    
                    $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!';
                    
                    // --- DYNAMIC REDIRECT FIX ---
                    $redirectUrl = 'views/' . $user['role_name'] . '/dashboard.php';
                    
                    // Debugging (Optional - remove in production)
                    // error_log("Redirecting user " . $user['id'] . " to " . $redirectUrl);
                    
                    header("Location: " . SITE_URL . "/" . $redirectUrl);
                    exit();

                } else {
                    $_SESSION['error'] = 'Invalid email or password';
                }
            } else {
                $_SESSION['error'] = 'Invalid email or password';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
    <div class="absolute top-20 left-10 text-9xl opacity-5 transform -rotate-12">🔐</div>
    <div class="absolute bottom-20 right-10 text-9xl opacity-5 transform rotate-12">💊</div>
</div>

<?php qm_hero('Welcome Back', 'Login to access your dashboard and orders.', 'Member Login', '🔐'); ?>

<section class="container mx-auto px-4 py-12 flex items-start justify-center">
    <div class="w-full max-w-md">

        <div class="card card-pad-lg" data-aos="zoom-in">
            
          <form method="POST" action="">
    <?php if(function_exists('generateCSRFToken')): ?>
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    <?php endif; ?>
    
    <div class="mb-5">
        <label class="label">📧 <?= __('email') ?> *</label>
        <input
            type="email"
            name="email"
            class="input"
            required
            placeholder="your@email.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        >
    </div>

    <div class="mb-5">
        <label class="label">🔑 <?= __('password') ?> *</label>
        <div class="relative">
            <input
                type="password"
                name="password"
                id="passwordInput"
                class="input pr-12"
                required
                placeholder="••••••••"
            >
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-2.5 text-gray-500 hover:text-[#065f46] focus:outline-none" title="Show/Hide">
                <span id="eyeIcon">👁️</span>
            </button>
        </div>
    </div>
    
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <input 
                type="checkbox" 
                name="remember_me" 
                id="rememberMe" 
                class="w-4 h-4 text-lime-600 border-2 border-gray-300 rounded focus:ring-lime-500"
            >
            <label for="rememberMe" class="ml-2 text-sm font-bold text-gray-600 cursor-pointer hover:text-deep-green">
                <?= __('remember_me') ?>
            </label>
        </div>
        <a href="<?= SITE_URL ?>/forgot-password.php" class="text-sm font-bold text-lime-600 hover:text-deep-green hover:underline">
            Forgot Password?
        </a>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block btn-lg">
        <span>🚀</span> Access Account
    </button>

    <div class="text-center mt-8 pt-6 border-t-2 border-dashed border-gray-200">
        <p class="text-gray-500 text-sm mb-2">New to QuickMed?</p>
        <a href="<?= SITE_URL ?>/signup.php" class="inline-block font-bold text-[#065f46] border-b-2 border-[#84cc16] hover:bg-[#84cc16] px-1 transition-all">
            Create New Account →
        </a>
    </div>
</form>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.textContent = '🙈'; // চোখ বন্ধ আইকন (Hidden mode off)
    } else {
        passwordInput.type = 'password';
        eyeIcon.textContent = '👁️'; // চোখ খোলা আইকন (Hidden mode on)
    }
}
</script>
        </div>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>