<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.gender, u.date_of_birth,
               dp.blood_type, dp.weight_kg, dp.is_anonymous, dp.last_donation_date, dp.medical_conditions
        FROM users u
        LEFT JOIN donor_profiles dp ON u.user_id = dp.donor_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo json_encode(['success' => true, 'data' => $profile]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Profile not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
