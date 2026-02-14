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
    if ($action === 'create') {
        
        if (empty($data['name']) || empty($data['address']) || empty($data['city']) || empty($data['contact_number'])) {
            throw new Exception("All fields are required");
        }

        
        $stmt = $pdo->prepare("SELECT hospital_id FROM hospitals WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            throw new Exception("Email already exists");
        }

        
        $stmt = $pdo->prepare("
            INSERT INTO hospitals (name, address, city, contact_number, email, is_verified, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $data['name'],
            $data['address'],
            $data['city'],
            $data['contact_number'],
            $data['email']
        ]);

        echo json_encode(['success' => true, 'message' => 'Hospital created successfully']);

    } elseif ($action === 'update') {
        $hospital_id = $data['hospital_id'];
        
        $stmt = $pdo->prepare("
            UPDATE hospitals SET name = ?, address = ?, city = ?, contact_number = ?, email = ?
            WHERE hospital_id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['address'],
            $data['city'],
            $data['contact_number'],
            $data['email'],
            $hospital_id
        ]);

        echo json_encode(['success' => true, 'message' => 'Hospital updated successfully']);

    } elseif ($action === 'delete') {
        $hospital_id = $data['hospital_id'];

        $stmt = $pdo->prepare("DELETE FROM hospitals WHERE hospital_id = ?");
        $stmt->execute([$hospital_id]);

        echo json_encode(['success' => true, 'message' => 'Hospital deleted successfully']);

    } elseif ($action === 'verify') {
        $hospital_id = $data['hospital_id'];

        $stmt = $pdo->prepare("UPDATE hospitals SET is_verified = 1 WHERE hospital_id = ?");
        $stmt->execute([$hospital_id]);

        echo json_encode(['success' => true, 'message' => 'Hospital verified successfully']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
