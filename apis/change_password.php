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

$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All password fields are required']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit();
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit();
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->execute([$new_hash, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
