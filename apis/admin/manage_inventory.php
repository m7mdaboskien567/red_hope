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
        if (empty($data['donation_id']) || empty($data['blood_type']) || empty($data['expiry_date']) || empty($data['current_location_id'])) {
            throw new Exception("All fields are required");
        }

        $stmt = $pdo->prepare("
            INSERT INTO blood_inventory (donation_id, blood_type, expiry_date, current_location_id, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['donation_id'],
            $data['blood_type'],
            $data['expiry_date'],
            $data['current_location_id'],
            $data['status'] ?? 'Available'
        ]);

        echo json_encode(['success' => true, 'message' => 'Inventory item created successfully']);

    } elseif ($action === 'update') {
        $id = $data['inventory_id'];

        $stmt = $pdo->prepare("
            UPDATE blood_inventory 
            SET blood_type = ?, expiry_date = ?, current_location_id = ?, status = ?
            WHERE inventory_id = ?
        ");
        $stmt->execute([
            $data['blood_type'],
            $data['expiry_date'],
            $data['current_location_id'],
            $data['status'],
            $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Inventory item updated successfully']);

    } elseif ($action === 'delete') {
        $id = $data['inventory_id'];
        $stmt = $pdo->prepare("DELETE FROM blood_inventory WHERE inventory_id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Inventory item deleted successfully']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
