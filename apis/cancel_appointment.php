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

if (empty($appointment_id)) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
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
        echo json_encode(['success' => false, 'message' => 'Appointment not found or cannot be cancelled']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);

    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
