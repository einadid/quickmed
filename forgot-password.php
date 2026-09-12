<?php
/**
 * QuickMed — Forgot Password (no email server needed)
 * ---------------------------------------------------
 * InfinityFree free hosting has no reliable mail(), so reset works by
 * verifying Email + Phone + Member ID, then letting the user set a
 * new password directly.
 */

require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Reset Password - QuickMed';
$step = 1; // 1 = verify identity, 2 = set new password
$verifiedUserId = 0;

// ---- Step 1: verify identity ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_identity'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Security validation failed. Please try again.';
    } else {
        $email = clean($_POST['email'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $memberId = clean($_POST['member_id'] ?? '');

        if ($email === '' || $phone === '') {
            $_SESSION['error'] = 'Please enter your email and phone number.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND phone = ? AND is_active = 1 AND is_banned = 0 LIMIT 1");
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $found = $stmt->get_result()->fetch_assoc();

            // Optional extra check: member id (only if the user typed it)
            $memberOk = true;
            if ($memberId !== '' && $found) {
                $mStmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND member_id = ? LIMIT 1");
                $mStmt->bind_param("is", $found['id'], $memberId);
                $mStmt->execute();
                $memberOk = ($mStmt->get_result()->num_rows === 1);
            }

            if ($found && $memberOk) {
                $_SESSION['pwd_reset_user'] = (int)$found['id'];
                $_SESSION['pwd_reset_time'] = time();
                $step = 2;
                $verifiedUserId = (int)$found['id'];
            } else {
                $_SESSION['error'] = 'No account matches those details. Check email, phone and member ID.';
            }
        }
    }
}

// ---- Step 2: set new password ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Security validation failed. Please try again.';
        $step = 1;
    } else {
        $uid = (int)($_SESSION['pwd_reset_user'] ?? 0);
        $when = (int)($_SESSION['pwd_reset_time'] ?? 0);

        // Token valid for 15 minutes
        if ($uid <= 0 || (time() - $when) > 900) {
            unset($_SESSION['pwd_reset_user'], $_SESSION['pwd_reset_time']);
            $_SESSION['error'] = 'Verification expired. Please verify again.';
            $step = 1;
        } else {
            $p1 = $_POST['new_password'] ?? '';
            $p2 = $_POST['confirm_password'] ?? '';
            if (strlen($p1) < MIN_PASSWORD_LENGTH) {
                $_SESSION['error'] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
                $step = 2;
                $verifiedUserId = $uid;
            } elseif ($p1 !== $p2) {
                $_SESSION['error'] = 'Passwords do not match.';
                $step = 2;
                $verifiedUserId = $uid;
            } else {
                $hash = password_hash($p1, PASSWORD_DEFAULT);
                $uStmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $uStmt->bind_param("si", $hash, $uid);
                if ($uStmt->execute()) {
                    unset($_SESSION['pwd_reset_user'], $_SESSION['pwd_reset_time']);
                    logAudit('PASSWORD_RESET', 'users', $uid);
                    $_SESSION['success'] = 'Password reset successful! Please login with your new password.';
                    redirect('login.php');
                } else {
                    $_SESSION['error'] = 'Could not update password. Please try again.';
                    $step = 2;
                    $verifiedUserId = $uid;
                }
            }
        }
    }
}

// If session already verified (e.g. after an error on step 2), stay on step 2
if ($step === 1 && !empty($_SESSION['pwd_reset_user']) && (time() - (int)$_SESSION['pwd_reset_time']) <= 900 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // fresh GET with valid session — start over for safety
    unset($_SESSION['pwd_reset_user'], $_SESSION['pwd_reset_time']);
}

include 'includes/header.php';
qm_hero('Reset Password', 'Verify your identity, then choose a new password.', 'Account Recovery', '🔑');
?>

<section class="container mx-auto px-4 py-12 min-h-[50vh] flex items-start justify-center">
    <div class="w-full max-w-md">
        <div class="card card-pad-lg" data-aos="zoom-in">
            <?php if ($step === 1): ?>
                <h2 class="text-xl font-bold text-[#065f46] mb-1 font-display">STEP 1 — VERIFY IDENTITY</h2>
                <p class="text-sm text-gray-500 mb-6">Enter the email & phone you registered with.</p>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div>
                        <label class="label">📧 Email *</label>
                        <input type="email" name="email" class="input" required placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="label">📱 Phone *</label>
                        <input type="tel" name="phone" class="input" required placeholder="01XXXXXXXXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="label">🪪 Member ID <span class="text-gray-400 font-normal">(optional, if you know it)</span></label>
                        <input type="text" name="member_id" class="input" placeholder="e.g. rahim-123">
                    </div>
                    <button type="submit" name="verify_identity" class="btn btn-primary btn-block">Verify →</button>
                </form>
            <?php else: ?>
                <h2 class="text-xl font-bold text-[#065f46] mb-1 font-display">STEP 2 — NEW PASSWORD</h2>
                <p class="text-sm text-gray-500 mb-6">Identity verified ✔ — set your new password.</p>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div>
                        <label class="label">🔒 New Password *</label>
                        <input type="password" name="new_password" class="input" required minlength="<?= MIN_PASSWORD_LENGTH ?>" placeholder="Min <?= MIN_PASSWORD_LENGTH ?> characters">
                    </div>
                    <div>
                        <label class="label">🔐 Confirm Password *</label>
                        <input type="password" name="confirm_password" class="input" required placeholder="Re-enter password">
                    </div>
                    <button type="submit" name="set_password" class="btn btn-lime btn-block">🔑 Set New Password</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-8 pt-6 border-t-2 border-dashed border-gray-200">
                <a href="<?= SITE_URL ?>/login.php" class="font-bold text-[#065f46] hover:underline">← Back to Login</a>
                <p class="text-xs text-gray-400 mt-3">Still stuck? Call our hotline <b>09678-100100</b></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
