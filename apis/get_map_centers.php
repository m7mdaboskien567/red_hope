<?php
header('Content-Type: application/json');


$jsonFile = __DIR__ . '/../wp-private/blood_centers_data.json';

if (!file_exists($jsonFile)) {
    echo json_encode(['success' => false, 'message' => 'Data file not found']);
    exit();
}

$data = json_decode(file_get_contents($jsonFile), true);

if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

echo json_encode(['success' => true, 'centers' => $data]);
?>