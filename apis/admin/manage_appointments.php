<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

include_once __DIR__ . '/../../database/config.php';

try {
    if ($action === 'approve') {
        $id = $data['appointment_id'];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Allowed' WHERE appointment_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Appointment approved successfully']);

    } elseif ($action === 'reject') {
        $id = $data['appointment_id'];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Appointment rejected successfully']);

    } elseif ($action === 'complete') {
        $id = $data['appointment_id'];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Appointment marked as completed']);

    } elseif ($action === 'reset') {
        $id = $data['appointment_id'];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Pending' WHERE appointment_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Appointment reset to pending']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
