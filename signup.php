<?php
/**
 * QuickMed - Signup Page (FIXED)
 */

require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Sign Up - QuickMed';

// Handle signup submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = clean($_POST['full_name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $verificationCode = clean($_POST['verification_code'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (strlen($password) < MIN_PASSWORD_LENGTH) $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    
    // Check if email exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
        $errors[] = 'Email already registered';
    }
    
    // If verification code provided, validate it
    $roleId = 1; // Default customer
    $shopId = null;
    $codeId = null;
    
    if (!empty($verificationCode)) {
        $codeQuery = "SELECT * FROM signup_codes 
                      WHERE code = ? AND is_used = 0 AND expires_at > NOW()";
        $codeStmt = $conn->prepare($codeQuery);
        $codeStmt->bind_param("s", $verificationCode);
        $codeStmt->execute();
        $codeResult = $codeStmt->get_result();
        
        if ($codeResult->num_rows === 1) {
            $codeData = $codeResult->fetch_assoc();
            $roleId = $codeData['role_id'];
            $shopId = $codeData['shop_id'];
            $codeId = $codeData['id'];
        } else {
            $errors[] = 'Invalid or expired verification code';
        }
    }
    
    if (empty($errors)) {
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // --- FIXED: Generate Member ID Logic ---
        
        // Generate Member ID from email prefix (e.g., 'john' from 'john@gmail.com')
        $emailPrefix = strtolower(explode('@', $email)[0]);
        // Remove any special characters to keep it clean
        $emailPrefix = preg_replace('/[^a-z0-9]/', '', $emailPrefix);
        
        // Check if this member_id exists to ensure uniqueness
        $checkId = $conn->prepare("SELECT id FROM users WHERE member_id = ?");
        $checkId->bind_param("s", $emailPrefix);
        $checkId->execute();
        
        if ($checkId->get_result()->num_rows > 0) {
            // Append random number if exists
            $memberId = $emailPrefix . '-' . rand(100, 999);
        } else {
            $memberId = $emailPrefix;
        }
        
        // Generate Username
        $username = strtolower(str_replace(' ', '', $fullName)) . rand(100, 999);
        
        // Calculate Points
        $points = $roleId == 1 ? SIGNUP_BONUS_POINTS : 0;
        
        // Insert user with member_id
        $insertQuery = "INSERT INTO users (role_id, username, email, member_id, password_hash, full_name, phone, shop_id, points) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertQuery);
        
        if ($stmt === false) {
            $errors[] = 'Database error: ' . $conn->error;
        } else {
            // Bind params: i=int, s=string. Total 9 params.
            // roleId(i), username(s), email(s), memberId(s), passwordHash(s), fullName(s), phone(s), shopId(i), points(i)
            $stmt->bind_param("issssssii", $roleId, $username, $email, $memberId, $passwordHash, $fullName, $phone, $shopId, $points);
            
            if ($stmt->execute()) {
                $userId = $stmt->insert_id;
                
                // Mark verification code as used
                if (!empty($verificationCode) && $codeId !== null) {
                    $updateCode = "UPDATE signup_codes SET is_used = 1, used_by = ?, used_at = NOW() WHERE id = ?";
                    $updateStmt = $conn->prepare($updateCode);
                    if ($updateStmt) {
                        $updateStmt->bind_param("ii", $userId, $codeId);
                        $updateStmt->execute();
                    }
                }
                
                // Add signup bonus for customers
                if ($roleId == 1 && $points > 0) {
                    $pointsQuery = "INSERT INTO points_log (user_id, points, type, description) 
                                    VALUES (?, ?, 'signup', 'Welcome bonus')";
                    $pointsStmt = $conn->prepare($pointsQuery);
                    
                    if ($pointsStmt) {
                        $pointsStmt->bind_param("ii", $userId, $points);
                        $pointsStmt->execute();
                    }
                }
                
                // Log audit
                logAudit('USER_SIGNUP', 'users', $userId, null, ['email' => $email, 'member_id' => $memberId, 'role_id' => $roleId]);
                
                $_SESSION['success'] = 'Registration successful! Your Member ID is ' . $memberId . '. You got ' . $points . ' bonus points. Please login.';
                redirect('login.php');
            } else {
                $errors[] = 'Registration failed: ' . $stmt->error;
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

include 'includes/header.php';
?>

<section class="min-h-screen py-16 relative overflow-hidden">
    <div class="absolute inset-0 animated-bg opacity-5"></div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-2xl mx-auto">
            <div class="card bg-white border-4 border-deep-green shadow-2xl" data-aos="zoom-in">
                <div class="card-header text-center bg-deep-green neon-border">
                    <h1 class="text-3xl font-mono">✍️ CREATE YOUR ACCOUNT</h1>
                    <p class="text-lime-accent mt-2">Join QuickMed Family Today!</p>
                </div>
                
                <form method="POST" action="" class="p-8">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block font-bold mb-2 text-deep-green text-lg">
                                👤 <?= __('full_name') ?> *
                            </label>
                            <input 
                                type="text" 
                                name="full_name" 
                                class="input border-4 border-deep-green focus:border-lime-accent transition-all duration-300" 
                                required 
                                placeholder="Enter your full name"
                                value="<?= $_POST['full_name'] ?? '' ?>"
                            >
                        </div>
                        
                        <div>
                            <label class="block font-bold mb-2 text-deep-green text-lg">
                                📧 <?= __('email') ?> *
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                class="input border-4 border-deep-green focus:border-lime-accent transition-all duration-300" 
                                required 
                                placeholder="your@email.com"
                                value="<?= $_POST['email'] ?? '' ?>"
                            >
                        </div>
                        
                        <div>
                            <label class="block font-bold mb-2 text-deep-green text-lg">
                                📱 <?= __('phone') ?> *
                            </label>
                            <input 
                                type="tel" 
                                name="phone" 
                                class="input border-4 border-deep-green focus:border-lime-accent transition-all duration-300" 
                                required 
                                placeholder="01XXXXXXXXX"
                                value="<?= $_POST['phone'] ?? '' ?>"
                            >
                        </div>
                        
                        <div>
                            <label class="block font-bold mb-2 text-deep-green text-lg">
                                🔒 <?= __('password') ?> *
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                class="input border-4 border-deep-green focus:border-lime-accent transition-all duration-300" 
                                required 
                                placeholder="Min <?= MIN_PASSWORD_LENGTH ?> characters"
                                minlength="<?= MIN_PASSWORD_LENGTH ?>"
                                id="password"
                            >
                            <div class="mt-2 text-sm">
                                <div id="passwordStrength" class="h-2 bg-gray-200 border-2 border-gray-300">
                                    <div id="passwordStrengthBar" class="h-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <p id="passwordStrengthText" class="mt-1 text-xs"></p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block font-bold mb-2 text-deep-green text-lg">
                                🔐 <?= __('confirm_password') ?> *
                            </label>
                            <input 
                                type="password" 
                                name="confirm_password" 
                                class="input border-4 border-deep-green focus:border-lime-accent transition-all duration-300" 
                                required 
                                placeholder="Re-enter password"
                                id="confirmPassword"
                            >
                            <p id="passwordMatch" class="mt-1 text-sm"></p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block font-bold mb-2 text-deep-green text-lg">
                                🎫 <?= __('verification_code') ?> (Optional - for staff only)
                            </label>
                            <input 
                                type="text" 
                                name="verification_code" 
                                class="input border-4 border-deep-green focus:border-lime-accent transition-all duration-300" 
                                placeholder="Enter code if you're staff member"
                                value="<?= $_POST['verification_code'] ?? '' ?>"
                            >
                            <p class="text-sm text-gray-600 mt-1">
                                💡 Leave blank if registering as customer
                            </p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full mt-8 text-xl py-4 transform transition-all duration-300 hover:scale-105 neon-border">
                        ✨ CREATE ACCOUNT & GET <?= SIGNUP_BONUS_POINTS ?> POINTS ✨
                    </button>
                    
                    <div class="text-center mt-6 pt-6 border-t-4 border-deep-green">
                        <p class="text-lg mb-2"><?= __('have_account') ?></p>
                        <a href="<?= SITE_URL ?>/login.php" class="text-deep-green hover:text-lime-accent font-bold text-2xl transition-colors duration-300">
                            <?= __('login') ?> →
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6 mt-8">
                <div class="card bg-lime-accent border-4 border-deep-green" data-aos="fade-right">
                    <h3 class="text-2xl font-bold text-deep-green mb-4 uppercase">🎁 Signup Benefits</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold">Get <?= SIGNUP_BONUS_POINTS ?> welcome bonus points</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold">Earn points on every order</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold">Track orders in real-time</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold">Upload prescriptions easily</span>
                        </li>
                    </ul>
                </div>
                
                <div class="card bg-white border-4 border-deep-green" data-aos="fade-left">
                    <h3 class="text-2xl font-bold text-deep-green mb-4 uppercase">🔒 Your Data is Safe</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">🛡️</span>
                            <span>Encrypted password storage</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">🔐</span>
                            <span>Secure payment methods</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">📱</span>
                            <span>SMS & Email notifications</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-2xl">✨</span>
                            <span>100% Privacy guaranteed</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Password Strength Checker
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('passwordStrengthBar');
const strengthText = document.getElementById('passwordStrengthText');

passwordInput?.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 15;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 10;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 40) {
        strengthBar.style.backgroundColor = '#ef4444';
        strengthText.textContent = '❌ Weak password';
        strengthText.className = 'mt-1 text-xs text-red-600';
    } else if (strength < 70) {
        strengthBar.style.backgroundColor = '#f59e0b';
        strengthText.textContent = '⚠️ Medium password';
        strengthText.className = 'mt-1 text-xs text-yellow-600';
    } else {
        strengthBar.style.backgroundColor = '#84cc16';
        strengthText.textContent = '✅ Strong password';
        strengthText.className = 'mt-1 text-xs text-green-600';
    }
});

// Password Match Checker
const confirmPasswordInput = document.getElementById('confirmPassword');
const passwordMatch = document.getElementById('passwordMatch');

confirmPasswordInput?.addEventListener('input', function() {
    if (this.value === '') {
        passwordMatch.textContent = '';
        return;
    }
    
    if (this.value === passwordInput.value) {
        passwordMatch.textContent = '✅ Passwords match';
        passwordMatch.className = 'mt-1 text-sm text-green-600 font-bold';
    } else {
        passwordMatch.textContent = '❌ Passwords do not match';
        passwordMatch.className = 'mt-1 text-sm text-red-600 font-bold';
    }
});
</script>

<?php include 'includes/footer.php'; ?>