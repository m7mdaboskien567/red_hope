<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$sender_id = $_SESSION['user_id'];
$receiver_id = $data['receiver_id'] ?? null;
$subject = trim($data['subject'] ?? '');
$message_content = trim($data['message_content'] ?? '');

if (!$receiver_id || empty($message_content)) {
    echo json_encode(['success' => false, 'message' => 'Receiver and message content are required']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

try {
    // Validate receiver exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Receiver not found']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message_content, sent_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$sender_id, $receiver_id, $subject, $message_content]);

    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
