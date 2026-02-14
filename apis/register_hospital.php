<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital Admin') {
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

$name = trim($data['name'] ?? '');
$address = trim($data['address'] ?? '');
$city = trim($data['city'] ?? '');
$contact_number = trim($data['contact_number'] ?? '');
$email = trim($data['email'] ?? '');

if (empty($name) || empty($address) || empty($city) || empty($contact_number)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

try {
    
    $stmt = $pdo->prepare("SELECT * FROM hospitals WHERE admin_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'You have already registered a hospital']);
        exit();
    }

    
    $stmt = $pdo->prepare("
        INSERT INTO hospitals (name, address, city, contact_number, email, admin_id, is_verified, created_at)
        VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())
    ");
    $stmt->execute([$name, $address, $city, $contact_number, $email ?: null, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Hospital registered successfully. Pending admin approval.']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'A hospital with this email already exists']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
