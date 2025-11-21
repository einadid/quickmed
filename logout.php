<?php
/**
 * QuickMed - Logout
 */

require_once 'config.php';

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    
    // Log audit
    logAudit('USER_LOGOUT', 'users', $userId);
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        // Delete from database
        $deleteToken = "DELETE FROM sessions WHERE token = ?";
        $stmt = $conn->prepare($deleteToken);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        // Delete cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    // Start new session for message
    session_start();
    $_SESSION['success'] = 'Logged out successfully!';
}

redirect('index.php');