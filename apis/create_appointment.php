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

$center_id = $data['center_id'] ?? '';
$appointment_date = $data['appointment_date'] ?? '';
$appointment_time = $data['appointment_time'] ?? '';
$notes = $data['notes'] ?? '';

if (empty($center_id) || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

$scheduled_datetime = $appointment_date . ' ' . $appointment_time . ':00';

if (strtotime($scheduled_datetime) <= time()) {
    echo json_encode(['success' => false, 'message' => 'Appointment must be in the future']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT last_donation_date FROM donor_profiles WHERE donor_id = ?");
    $stmt->execute([$user_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($donor && $donor['last_donation_date']) {
        $last_donation = new DateTime($donor['last_donation_date']);
        $now = new DateTime();
        $diff = $now->diff($last_donation);
        if ($diff->days < 56) {
            echo json_encode(['success' => false, 'message' => 'You must wait 56 days between donations']);
            exit();
        }
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM appointments 
        WHERE donor_id = ? AND status = 'Pending' AND scheduled_time > NOW()
    ");
    $stmt->execute([$user_id]);
    $pending = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pending['count'] >= 3) {
        echo json_encode(['success' => false, 'message' => 'You can only have 3 pending appointments']);
        exit();
    }

    $stmt = $pdo->prepare("
        INSERT INTO appointments (donor_id, center_id, scheduled_time, status, notes)
        VALUES (?, ?, ?, 'Pending', ?)
    ");
    $stmt->execute([$user_id, $center_id, $scheduled_datetime, $notes]);

    echo json_encode(['success' => true, 'message' => 'Appointment scheduled successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
