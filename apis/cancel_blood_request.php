<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$request_id = $data['request_id'] ?? '';

if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit();
}

try {
    
    $stmt = $pdo->prepare("
        SELECT * FROM blood_requests 
        WHERE request_id = ? AND status = 'Open'
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or cannot be cancelled']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE blood_requests SET status = 'Cancelled' WHERE request_id = ?");
    $stmt->execute([$request_id]);

    echo json_encode(['success' => true, 'message' => 'Blood request cancelled successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
