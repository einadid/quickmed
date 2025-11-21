<?php
/**
 * QuickMed - Login Page
 */

require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    redirect('views/' . $user['role_name'] . '/dashboard.php');
}

$pageTitle = 'Login - QuickMed';

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter both email and password';
    } else {
        // Check user credentials
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
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['username'] = $user['username'];
                
                // Update last login
                $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+' . REMEMBER_ME_DAYS . ' days'));
                    
                    // Save token to database
                    $tokenQuery = "INSERT INTO sessions (user_id, token, user_agent, ip_address, expires_at) 
                                   VALUES (?, ?, ?, ?, ?)";
                    $tokenStmt = $conn->prepare($tokenQuery);
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    $ipAddress = $_SERVER['REMOTE_ADDR'];
                    $tokenStmt->bind_param("issss", $user['id'], $token, $userAgent, $ipAddress, $expires);
                    $tokenStmt->execute();
                    
                    // Set cookie
                    setcookie('remember_token', $token, strtotime('+' . REMEMBER_ME_DAYS . ' days'), '/');
                }
                
                // Log audit
                logAudit('USER_LOGIN', 'users', $user['id']);
                
                $_SESSION['success'] = 'Welcome back, ' . $user['full_name'] . '!';
                
                // Redirect based on role
                redirect('views/' . $user['role_name'] . '/dashboard.php');
            } else {
                $_SESSION['error'] = 'Invalid email or password';
            }
        } else {
            $_SESSION['error'] = 'Invalid email or password';
        }
    }
}

include 'includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <div class="max-w-md mx-auto">
        <div class="card bg-white" data-aos="zoom-in">
            <div class="card-header text-center">
                <h1 class="text-2xl">🔐 <?= __('login') ?></h1>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <!-- Email -->
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-green"><?= __('email') ?> *</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="input" 
                        required 
                        placeholder="your@email.com"
                        value="<?= $_POST['email'] ?? '' ?>"
                    >
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label class="block font-bold mb-2 text-green"><?= __('password') ?> *</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="input" 
                        required 
                        placeholder="Enter your password"
                    >
                </div>
                
                <!-- Remember Me -->
                <div class="mb-4 flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember_me" 
                        id="rememberMe" 
                        class="mr-2"
                    >
                    <label for="rememberMe" class="text-sm">
                        <?= __('remember_me') ?>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-full mb-4">
                    🔓 <?= __('login') ?>
                </button>
                
                <!-- Links -->
                <div class="text-center text-sm">
                    <a href="<?= SITE_URL ?>/forgot-password.php" class="text-green hover:text-lime-accent font-bold">
                        <?= __('forgot_password') ?>
                    </a>
                </div>
                
                <div class="text-center text-sm mt-4 pt-4 border-t-2 border-green">
                    <p><?= __('no_account') ?></p>
                    <a href="<?= SITE_URL ?>/signup.php" class="text-green hover:text-lime-accent font-bold text-lg">
                        <?= __('signup') ?> →
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Quick Login Info (for demo) -->
        <div class="card bg-light-green mt-6" data-aos="fade-up">
            <h3 class="font-bold text-green mb-3 uppercase">🔑 Demo Login Credentials</h3>
            <div class="space-y-2 text-sm">
                <div class="bg-white border-2 border-green p-2">
                    <strong>Admin:</strong> admin@quickmed.com / Admin@123
                </div>
                <div class="bg-white border-2 border-green p-2">
                    <strong>Customer:</strong> customer@gmail.com / Customer@123
                </div>
                <div class="bg-white border-2 border-green p-2">
                    <strong>Salesman:</strong> salesman@quickmed.com / Staff@123
                </div>
                <div class="bg-white border-2 border-green p-2">
                    <strong>Manager:</strong> manager@quickmed.com / Staff@123
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>