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
        $name = trim($data['name']);
        $address = trim($data['address']);
        $city = trim($data['city']);
        
        // For now using default coordinates (0,0) as GPS isn't implemented in UI yet
        $stmt = $pdo->prepare("INSERT INTO blood_centers (name, address, city, gps_coordinates) VALUES (?, ?, ?, POINT(0, 0))");
        $stmt->execute([$name, $address, $city]);
        
        echo json_encode(['success' => true, 'message' => 'Center added successfully']);

    } elseif ($action === 'delete') {
        $id = $data['center_id'];
        $stmt = $pdo->prepare("DELETE FROM blood_centers WHERE center_id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Center deleted successfully']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
