<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$sessionId = $_GET['sessionId'] ?? null;
$userId = $_SESSION['user_id'];
$pdo = getDB();

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Session ID required']);
    exit();
}

try {
    // Verify ownership
    $check = $pdo->prepare("SELECT user_id FROM ai_chat_sessions WHERE session_id = ?");
    $check->execute([$sessionId]);
    $session = $check->fetch();

    if (!$session || $session['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT sender, message_content, sent_at FROM ai_chat_messages WHERE session_id = ? ORDER BY sent_at ASC");
    $stmt->execute([$sessionId]);
    $messages = $stmt->fetchAll();

    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>