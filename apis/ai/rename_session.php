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
$newTitle = trim($data['title'] ?? '');
$userId = $_SESSION['user_id'];

if (!$sessionId || !$newTitle) {
    echo json_encode(['success' => false, 'message' => 'Missing session ID or title']);
    exit();
}

// Limit title length
$newTitle = substr($newTitle, 0, 60);

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

    $stmt = $pdo->prepare("UPDATE ai_chat_sessions SET title = ? WHERE session_id = ?");
    $stmt->execute([$newTitle, $sessionId]);

    echo json_encode(['success' => true, 'title' => $newTitle]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>