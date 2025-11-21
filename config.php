<?php
/**
 * QuickMed - Configuration File
 * Contains all database and application settings
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Dhaka');

// =============================================
// DATABASE CONFIGURATION
// =============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quickmed');

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for emoji and multilingual support
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// =============================================
// APPLICATION SETTINGS
// =============================================
define('SITE_NAME', 'QuickMed');
define('SITE_TAGLINE', 'Your Trusted Online Pharmacy | আপনার বিশ্বস্ত অনলাইন ফার্মেসি');
define('SITE_URL', 'http://localhost/quickmed');
define('ADMIN_EMAIL', 'admin@quickmed.com');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('PRESCRIPTION_DIR', UPLOAD_DIR . 'prescriptions/');
define('MEDICINE_DIR', UPLOAD_DIR . 'medicines/');
define('NEWS_DIR', UPLOAD_DIR . 'news/');

// Create upload directories if not exist
$dirs = [UPLOAD_DIR, PRESCRIPTION_DIR, MEDICINE_DIR, NEWS_DIR];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// =============================================
// BUSINESS RULES
// =============================================
define('HOME_DELIVERY_CHARGE', 100); // BDT
define('POINTS_PER_1000_BDT', 100); // 100 points for every 1000 BDT spent
define('POINTS_TO_BDT_RATIO', 0.1); // 100 points = 10 BDT
define('SIGNUP_BONUS_POINTS', 100);
define('MIN_ORDER_AMOUNT', 50); // Minimum order 50 BDT

// =============================================
// SECURITY SETTINGS
// =============================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 7200); // 2 hours
define('REMEMBER_ME_DAYS', 30);

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);

// =============================================
// PAGINATION
// =============================================
define('ITEMS_PER_PAGE', 20);

// =============================================
// HELPER FUNCTIONS
// =============================================

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize input
 */
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

/**
 * Redirect helper - Safe Version
 */
function redirect($url) {
    $target = SITE_URL . "/" . $url;
    
    if (!headers_sent()) {
        header("Location: " . $target);
    } else {
        echo "<script>window.location.href='" . $target . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . $target . "'></noscript>";
    }
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $query = "SELECT u.*, r.name as role_name FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id 
              WHERE u.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role_name'] === $role;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to continue.";
        redirect('login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = "Access denied. Insufficient permissions.";
        redirect('index.php');
    }
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return number_format($amount, 2) . ' ৳';
}

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'QM' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Generate unique parcel number
 */
function generateParcelNumber($orderId, $shopId) {
    return 'P' . $orderId . '-S' . $shopId . '-' . strtoupper(substr(uniqid(), -4));
}

/**
 * Log audit trail
 */
function logAudit($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    global $conn;
    
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
    $newValuesJson = $newValues ? json_encode($newValues) : null;
    
    $query = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ississss", $userId, $action, $tableName, $recordId, $oldValuesJson, $newValuesJson, $ipAddress, $userAgent);
    $stmt->execute();
}

/**
 * Upload file helper
 */
function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($fileSize > 5242880) { // 5MB
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $destination = $directory . $newFileName;
    
    if (move_uploaded_file($fileTmp, $destination)) {
        return ['success' => true, 'filename' => $newFileName];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return $difference . ' seconds ago';
    } elseif ($difference < 3600) {
        return floor($difference / 60) . ' minutes ago';
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . ' hours ago';
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . ' days ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

/**
 * Generate random verification code
 */
function generateVerificationCode($length = 8) {
    return strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, $length));
}

// =============================================
// AUTO-LOAD LANGUAGE FILE
// =============================================
$lang = $_SESSION['lang'] ?? 'en';
if (file_exists(__DIR__ . '/includes/lang.php')) {
    require_once __DIR__ . '/includes/lang.php';
}
?>