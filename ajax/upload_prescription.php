<?php
/**
 * Upload Prescription Image
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to upload prescription']);
    exit;
}

if (!isset($_FILES['prescription_image']) || $_FILES['prescription_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please select an image']);
    exit;
}

$notes = clean($_POST['notes'] ?? '');

// Upload file
$uploadResult = uploadFile($_FILES['prescription_image'], PRESCRIPTION_DIR, ['jpg', 'jpeg', 'png', 'pdf']);

if (!$uploadResult['success']) {
    echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
    exit;
}

$userId = $_SESSION['user_id'];
$imagePath = $uploadResult['filename'];

// Save to database
$insertQuery = "INSERT INTO prescriptions (user_id, image_path, notes, status) VALUES (?, ?, ?, 'pending')";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("iss", $userId, $imagePath, $notes);

if ($stmt->execute()) {
    logAudit('PRESCRIPTION_UPLOAD', 'prescriptions', $stmt->insert_id);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Prescription uploaded successfully! Our team will review it soon.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save prescription']);
}