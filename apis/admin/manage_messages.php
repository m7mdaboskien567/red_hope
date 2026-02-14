<?php
session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include_once __DIR__ . '/../../database/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$message_id = $data['message_id'] ?? null;

if ($action === 'delete') {
    if (!$message_id) {
        echo json_encode(['success' => false, 'message' => 'Message ID required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([$message_id]);
        echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
