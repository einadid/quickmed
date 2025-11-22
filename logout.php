<?php
/**
 * QuickMed - Secure Logout Handler
 */

// 1. Load Config (Auto Path Detection)
if (file_exists('config.php')) {
    require_once 'config.php';
} elseif (file_exists('../config.php')) {
    require_once '../config.php';
} elseif (file_exists('../../config.php')) {
    require_once '../../config.php';
} else {
    // Fallback if config not found
    session_start();
    $site_url = 'index.php';
}

$site_url = defined('SITE_URL') ? SITE_URL : 'index.php';

// 2. Log Audit if User Exists
if (function_exists('isLoggedIn') && isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    if (function_exists('logAudit')) {
        logAudit('USER_LOGOUT', 'users', $userId);
    }
}

// 3. Clear Cookies (Forcefully)
if (isset($_COOKIE['remember_token'])) {
    unset($_COOKIE['remember_token']);
    setcookie('remember_token', '', time() - 3600, '/'); // Clear from root
    setcookie('remember_token', '', time() - 3600, '/', $_SERVER['HTTP_HOST']); // Clear from domain
}

// 4. Destroy Session
$_SESSION = array(); // Clear session data

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // Destroy session completely

// 5. Safe Redirect (JS + PHP)
if (!headers_sent()) {
    header("Location: " . $site_url);
} else {
    echo "<script>window.location.href='" . $site_url . "';</script>";
    echo "<noscript><meta http-equiv='refresh' content='0;url=" . $site_url . "'></noscript>";
}
exit();
?>