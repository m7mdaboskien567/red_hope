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

try {
    $pdo->beginTransaction();

    if (isset($data['first_name']) || isset($data['email']) || isset($data['phone'])) {
        $updates = [];
        $params = [];

        if (!empty($data['first_name'])) {
            $updates[] = "first_name = ?";
            $params[] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $updates[] = "last_name = ?";
            $params[] = $data['last_name'];
        }
        if (!empty($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }
        if (!empty($data['phone'])) {
            $updates[] = "phone = ?";
            $params[] = $data['phone'];
        }

        if (!empty($updates)) {
            $params[] = $user_id;
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
    }

    if (isset($data['weight_kg']) || isset($data['medical_conditions']) || isset($data['is_anonymous'])) {
        $updates = [];
        $params = [];

        if (isset($data['weight_kg'])) {
            $updates[] = "weight_kg = ?";
            $params[] = $data['weight_kg'];
        }
        if (isset($data['medical_conditions'])) {
            $updates[] = "medical_conditions = ?";
            $params[] = $data['medical_conditions'];
        }
        if (isset($data['is_anonymous'])) {
            $updates[] = "is_anonymous = ?";
            $params[] = $data['is_anonymous'] ? 1 : 0;
        }

        if (!empty($updates)) {
            $params[] = $user_id;
            $sql = "UPDATE donor_profiles SET " . implode(", ", $updates) . " WHERE donor_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (PDOException $e) {
    $pdo->rollBack();
    
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Email or phone already in use']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
