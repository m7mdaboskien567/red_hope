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
    // 1. Check if the request exists and is open
    $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE request_id = ? AND status = 'Open'");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or already accepted.']);
        exit();
    }

    // 2. Update Request Status to 'In Progress' and assign Donor
    $stmt = $pdo->prepare("UPDATE blood_requests SET status = 'In Progress', donor_id = ? WHERE request_id = ?");
    $stmt->execute([$donor_id, $request_id]);

    // 3. Fetch Hospital Info for the donor
    $stmt = $pdo->prepare("SELECT name, address, city, contact_number, email FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$request['hospital_id']]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for committing to this donation!',
        'hospital' => $hospital
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
