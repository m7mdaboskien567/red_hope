<?php
session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$jsonFile = __DIR__ . '/../wp-private/blood_centers_data.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}


$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}


$required = ['name', 'address', 'city', 'lat', 'lng', 'contact_number'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit();
    }
}


$paramData = [];
if (file_exists($jsonFile)) {
    $fileContent = file_get_contents($jsonFile);
    $paramData = json_decode($fileContent, true) ?? [];
}


$newId = 1;
if (!empty($paramData)) {
    $maxId = 0;
    foreach ($paramData as $center) {
        if (isset($center['center_id']) && $center['center_id'] > $maxId) {
            $maxId = $center['center_id'];
        }
    }
    $newId = $maxId + 1;
}


$newCenter = [
    'center_id' => $newId,
    'name' => htmlspecialchars($data['name']),
    'address' => htmlspecialchars($data['address']),
    'city' => htmlspecialchars($data['city']),
    'lat' => (float) $data['lat'],
    'lng' => (float) $data['lng'],
    'contact_number' => htmlspecialchars($data['contact_number'])
];


$paramData[] = $newCenter;

if (file_put_contents($jsonFile, json_encode($paramData, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => 'Center added successfully', 'center' => $newCenter]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to write to data file']);
}
?>