<?php
/**
 * QuickMed — One-Time Admin Seeder  (v2.0)
 * ============================================================================
 * HOW TO USE:
 *   1. Import database/quickmed.sql into your database first (phpMyAdmin).
 *   2. Upload the whole site, then open this file ONCE in your browser:
 *         InfinityFree : https://quickmed.free.nf/database/seed_admin.php
 *         Localhost    : http://localhost/quickmed/database/seed_admin.php
 *   3. It creates the admin account below (or shows it if it already exists).
 *   4. *** DELETE THIS FILE from the server immediately afterwards! ***
 * ============================================================================
 */

require_once __DIR__ . '/../config.php';

// ---- Change these BEFORE running (optional) ----
$ADMIN_EMAIL = 'admin@quickmed.com';
$ADMIN_PASS  = 'Admin@123';
$ADMIN_NAME  = 'System Administrator';
$ADMIN_PHONE = '09678-100100';
// ------------------------------------------------

header('Content-Type: text/html; charset=utf-8');

echo '<div style="font-family:sans-serif;max-width:640px;margin:40px auto;padding:24px;border:2px solid #065f46;border-radius:12px">';
echo '<h2 style="color:#065f46">QuickMed — Admin Seeder</h2>';

// Safety: roles table must exist (i.e. quickmed.sql was imported)
$check = $conn->query("SHOW TABLES LIKE 'roles'");
if (!$check || $check->num_rows === 0) {
    echo '<p style="color:#b91c1c"><b>Error:</b> `roles` table not found. Please import <code>database/quickmed.sql</code> first.</p></div>';
    exit;
}

// Get admin role id
$roleRow = $conn->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1")->fetch_assoc();
if (!$roleRow) {
    echo '<p style="color:#b91c1c"><b>Error:</b> admin role missing. Re-import <code>quickmed.sql</code>.</p></div>';
    exit;
}
$adminRoleId = (int)$roleRow['id'];

// Already exists?
$stmt = $conn->prepare("SELECT id, email, full_name FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $ADMIN_EMAIL);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    echo '<p style="color:#065f46"><b>Admin already exists</b> — no changes made.</p>';
    echo '<p>Email: <b>' . htmlspecialchars($existing['email']) . '</b><br>Name: <b>' . htmlspecialchars($existing['full_name']) . '</b></p>';
} else {
    $hash = password_hash($ADMIN_PASS, PASSWORD_DEFAULT);
    $username = 'admin' . rand(100, 999);
    $memberId = 'admin-' . rand(100, 999);
    $shopId = null;
    $points = 0;
    $stmt = $conn->prepare("INSERT INTO users (role_id, username, email, member_id, password_hash, full_name, phone, shop_id, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssii", $adminRoleId, $username, $ADMIN_EMAIL, $memberId, $hash, $ADMIN_NAME, $ADMIN_PHONE, $shopId, $points);
    if ($stmt->execute()) {
        echo '<p style="color:#065f46"><b>✔ Admin created successfully!</b></p>';
        echo '<p>Email: <b>' . htmlspecialchars($ADMIN_EMAIL) . '</b><br>Password: <b>' . htmlspecialchars($ADMIN_PASS) . '</b></p>';
        echo '<p><a href="' . SITE_URL . '/login.php">Go to Login →</a></p>';
    } else {
        echo '<p style="color:#b91c1c"><b>Failed:</b> ' . htmlspecialchars($stmt->error) . '</p>';
    }
}

echo '<hr><p style="color:#b91c1c"><b>IMPORTANT: Delete <code>database/seed_admin.php</code> from the server NOW.</b> Leaving it exposes your admin email.</p>';
echo '</div>';
