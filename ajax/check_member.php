<?php
/**
 * Check Member ID for POS
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$memberId = clean($_GET['member_id'] ?? '');

if (empty($memberId)) {
    echo json_encode(['success' => false, 'message' => 'Member ID required']);
    exit;
}

// Check member
$query = "SELECT id, full_name, email, phone, points FROM users WHERE member_id = ? AND role_id = 1 AND is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $memberId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $member = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'member' => [
            'id' => $member['id'],
            'full_name' => $member['full_name'],
            'email' => $member['email'],
            'phone' => $member['phone'],
            'points' => (int)$member['points']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Member not found']);
}