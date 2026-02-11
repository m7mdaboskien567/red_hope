<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only Hospital Admins can fulfill requests.']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'] ?? null;
$admin_id = $_SESSION['user_id'];

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required.']);
    exit();
}

try {
    // 1. Fetch Hospital ID for this admin
    $stmt = $pdo->prepare("SELECT hospital_id FROM hospitals WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital) {
        echo json_encode(['success' => false, 'message' => 'Hospital not found for this administrator.']);
        exit();
    }
    $hospital_id = $hospital['hospital_id'];

    // 2. Check if the request exists, belongs to this hospital, and is 'In Progress'
    $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE request_id = ? AND hospital_id = ? AND status = 'In Progress'");
    $stmt->execute([$request_id, $hospital_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found, not in progress, or unauthorized.']);
        exit();
    }

    $donor_id = $request['donor_id'];
    if (!$donor_id) {
        echo json_encode(['success' => false, 'message' => 'No donor is assigned to this request yet.']);
        exit();
    }

    // 3. Update Request Status to 'Fulfilled'
    $stmt = $pdo->prepare("UPDATE blood_requests SET status = 'Fulfilled' WHERE request_id = ?");
    $stmt->execute([$request_id]);

    // 4. Create a Donation Record for the donor
    $stmt = $pdo->prepare("
        INSERT INTO donations (donor_id, hospital_id, volume_ml, hemoglobin_level, status, donated_at)
        VALUES (?, ?, 450, 13.5, 'Approved', NOW())
    ");
    $stmt->execute([$donor_id, $hospital_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Request successfully fulfilled and donation recorded!'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
