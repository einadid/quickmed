<?php
/**
 * Submit Review Handler
 */
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $orderId = intval($_POST['order_id']);
    $rating = intval($_POST['rating']);
    $text = clean($_POST['review_text']);
    
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = 'Invalid rating';
        redirect('my-orders.php');
    }
    
    // Check if order belongs to user
    $checkOrder = $conn->query("SELECT id FROM orders WHERE id = $orderId AND user_id = $userId");
    if ($checkOrder->num_rows === 0) {
        $_SESSION['error'] = 'Invalid order';
        redirect('my-orders.php');
    }
    
    // Insert Review
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, order_id, rating, review_text, is_approved) VALUES (?, ?, ?, ?, 0)"); // Approval required
    $stmt->bind_param("iiis", $userId, $orderId, $rating, $text);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Review submitted! Pending approval.';
    } else {
        $_SESSION['error'] = 'Failed to submit review';
    }
    
    redirect('my-orders.php');
}
?>