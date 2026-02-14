<?php
session_start();
header('Content-Type: application/json');
include_once __DIR__ . '/../../database/config.php';
$pdo = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$jsonFile = __DIR__ . '/../../wp-private/blood_centers_data.json';

if (!file_exists($jsonFile)) {
    echo json_encode(['success' => false, 'message' => 'Data file not found']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

$paramData = json_decode(file_get_contents($jsonFile), true) ?? [];

try {
    if ($action === 'create') {
        $maxId = 0;
        foreach ($paramData as $center) {
            if (isset($center['center_id']) && $center['center_id'] > $maxId) {
                $maxId = $center['center_id'];
            }
        }
        $newCenter = [
            'center_id' => $maxId + 1,
            'name' => htmlspecialchars($data['name'] ?? ''),
            'address' => htmlspecialchars($data['address'] ?? ''),
            'city' => htmlspecialchars($data['city'] ?? ''),
            'lat' => (float) ($data['lat'] ?? 0),
            'lng' => (float) ($data['lng'] ?? 0),
            'contact_number' => htmlspecialchars($data['contact_number'] ?? '')
        ];
        $paramData[] = $newCenter;

        // Sync to DB
        $stmt = $pdo->prepare("INSERT INTO blood_centers (center_id, name, address, city, lat, lng, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $newCenter['center_id'],
            $newCenter['name'],
            $newCenter['address'],
            $newCenter['city'],
            $newCenter['lat'],
            $newCenter['lng'],
            $newCenter['contact_number']
        ]);

        $msg = 'Center added successfully';

    } elseif ($action === 'update') {
        $id = (int) ($data['center_id'] ?? 0);
        $found = false;
        foreach ($paramData as &$center) {
            if ($center['center_id'] == $id) {
                $center['name'] = htmlspecialchars($data['name'] ?? $center['name']);
                $center['address'] = htmlspecialchars($data['address'] ?? $center['address']);
                $center['city'] = htmlspecialchars($data['city'] ?? $center['city']);
                $center['lat'] = (float) ($data['lat'] ?? $center['lat']);
                $center['lng'] = (float) ($data['lng'] ?? $center['lng']);
                $center['contact_number'] = htmlspecialchars($data['contact_number'] ?? $center['contact_number']);
                $found = true;

                // Sync to DB
                $stmt = $pdo->prepare("UPDATE blood_centers SET name = ?, address = ?, city = ?, lat = ?, lng = ?, contact_number = ? WHERE center_id = ?");
                $stmt->execute([
                    $center['name'],
                    $center['address'],
                    $center['city'],
                    $center['lat'],
                    $center['lng'],
                    $center['contact_number'],
                    $id
                ]);

                break;
            }
        }
        if (!$found)
            throw new Exception("Center not found");
        $msg = 'Center updated successfully';

    } elseif ($action === 'delete') {
        $id = (int) ($data['center_id'] ?? 0);
        $paramData = array_filter($paramData, function ($c) use ($id) {
            return $c['center_id'] != $id;
        });
        $paramData = array_values($paramData); // Re-index

        // Sync to DB
        $stmt = $pdo->prepare("DELETE FROM blood_centers WHERE center_id = ?");
        $stmt->execute([$id]);

        $msg = 'Center deleted successfully';

    } else {
        throw new Exception("Invalid action");
    }

    if (file_put_contents($jsonFile, json_encode($paramData, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        throw new Exception("Failed to write to data file");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>