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
        
        
        $stmt = $pdo->prepare("INSERT INTO blood_centers (name, address, city, gps_coordinates) VALUES (?, ?, ?, POINT(0, 0))");
        $stmt->execute([$name, $address, $city]);
        
        echo json_encode(['success' => true, 'message' => 'Center added successfully']);

    } elseif ($action === 'update') {
        $id = $data['center_id'];
        $name = trim($data['name']);
        $address = trim($data['address']);
        $city = trim($data['city']);
        $contact = trim($data['contact_number'] ?? '');

        $stmt = $pdo->prepare("UPDATE blood_centers SET name = ?, address = ?, city = ?, contact_number = ? WHERE center_id = ?");
        $stmt->execute([$name, $address, $city, $contact, $id]);

        echo json_encode(['success' => true, 'message' => 'Center updated successfully']);

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
