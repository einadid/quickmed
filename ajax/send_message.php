<?php
// ajax_submit_message.php
require_once 'db_connection.php'; // আপনার ডাটাবেস কানেকশন ফাইলটি এখানে ইনক্লুড করুন

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (empty($name) || empty($message)) {
        echo json_encode(['status' => 'error', 'msg' => 'Name and Message are required!']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'msg' => 'Message sent successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Something went wrong!']);
    }
    $stmt->close();
}
?>