<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

$appointment_id = $data['appointment_id'] ?? '';
$appointment_date = $data['appointment_date'] ?? '';
$appointment_time = $data['appointment_time'] ?? '';

if (empty($appointment_id) || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$scheduled_datetime = $appointment_date . ' ' . $appointment_time . ':00';

if (strtotime($scheduled_datetime) <= time()) {
    echo json_encode(['success' => false, 'message' => 'New appointment must be in the future']);
    exit();
}

try {
    
    $stmt = $pdo->prepare("
        SELECT * FROM appointments 
        WHERE appointment_id = ? AND donor_id = ? AND status IN ('Pending', 'Allowed')
    ");
    $stmt->execute([$appointment_id, $user_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found or cannot be rescheduled']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE appointments SET scheduled_time = ? WHERE appointment_id = ?");
    $stmt->execute([$scheduled_datetime, $appointment_id]);

    echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
