<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$hospital_id = $data['hospital_id'] ?? null;

if (!$hospital_id) {
    echo json_encode(['success' => false, 'message' => 'Hospital ID required']);
    exit();
}

include_once __DIR__ . '/../../database/config.php';

try {
    $stmt = $pdo->prepare("UPDATE hospitals SET is_verified = 1 WHERE hospital_id = ?");
    $stmt->execute([$hospital_id]);

    echo json_encode(['success' => true, 'message' => 'Hospital verified successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
