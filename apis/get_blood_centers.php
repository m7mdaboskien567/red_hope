<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include_once __DIR__ . '/../database/config.php';

try {
    $stmt = $pdo->query("SELECT center_id, name, address, city, contact_number FROM blood_centers ORDER BY name");
    $centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $centers]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
