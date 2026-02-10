<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'] ?? null;
$donor_id = $_SESSION['user_id'];

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required.']);
    exit();
}

try {
    // 1. Check if the request exists, is in progress, and belongs to this donor
    $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE request_id = ? AND status = 'In Progress' AND donor_id = ?");
    $stmt->execute([$request_id, $donor_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or you are not authorized to complete it.']);
        exit();
    }

    // 2. Update Request Status to 'Fulfilled'
    $stmt = $pdo->prepare("UPDATE blood_requests SET status = 'Fulfilled' WHERE request_id = ?");
    $stmt->execute([$request_id]);

    // 3. Create a Corresponding Donation Record (so stats update)
    // We assume 450ml for a standard donation
    $stmt = $pdo->prepare("
        INSERT INTO donations (donor_id, center_id, volume_ml, hemoglobin_level, status, donated_at)
        SELECT ?, 1, 450, 13.5, 'Approved', NOW()
    ");
    $stmt->execute([$donor_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Request marked as Fulfilled. Your donation has been recorded!'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
