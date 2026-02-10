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

$hospital_id = $data['hospital_id'] ?? '';
$blood_type = $data['blood_type_required'] ?? '';
$units = $data['units_requested'] ?? 1;
$urgency = $data['urgency_level'] ?? 'Normal';
$patient_id = $data['patient_identifier'] ?? null;

if (empty($hospital_id) || empty($blood_type) || empty($urgency)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

$valid_blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'A', 'B', 'AB', 'O'];
if (!in_array($blood_type, $valid_blood_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid blood type']);
    exit();
}

$valid_urgency = ['Normal', 'Urgent', 'Emergency'];
if (!in_array($urgency, $valid_urgency)) {
    echo json_encode(['success' => false, 'message' => 'Invalid urgency level']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO blood_requests (hospital_id, blood_type_required, units_requested, urgency_level, patient_identifier, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'Open', NOW())
    ");
    $stmt->execute([$hospital_id, $blood_type, $units, $urgency, $patient_id]);

    echo json_encode(['success' => true, 'message' => 'Blood request submitted successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
