<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$pdo = getDB();

try {
    $stmt = $pdo->prepare("SELECT session_id, title, created_at FROM ai_chat_sessions WHERE user_id = ? ORDER BY updated_at DESC");
    $stmt->execute([$userId]);
    $sessions = $stmt->fetchAll();
    echo json_encode(['success' => true, 'sessions' => $sessions]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>