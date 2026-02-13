<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['sessionId'] ?? null;
$userId = $_SESSION['user_id'];

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Missing session ID']);
    exit();
}

$pdo = getDB();

try {
    // Verify ownership
    $check = $pdo->prepare("SELECT user_id FROM ai_chat_sessions WHERE session_id = ?");
    $check->execute([$sessionId]);
    $session = $check->fetch();

    if (!$session || $session['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }

    // Delete messages first (foreign key), then session
    $pdo->prepare("DELETE FROM ai_chat_messages WHERE session_id = ?")->execute([$sessionId]);
    $pdo->prepare("DELETE FROM ai_chat_sessions WHERE session_id = ?")->execute([$sessionId]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>