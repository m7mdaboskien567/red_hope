<?php
include __DIR__ . '/../database/config.php';
$pdo = getDB();

try {
    // 1. Alter table to use lat/lng instead of POINT
    $pdo->exec("ALTER TABLE blood_centers DROP COLUMN IF EXISTS gps_coordinates");
    $pdo->exec("ALTER TABLE blood_centers ADD COLUMN IF NOT EXISTS lat DECIMAL(10, 8) AFTER city, ADD COLUMN IF NOT EXISTS lng DECIMAL(11, 8) AFTER lat");
    echo "Table schema updated.\n";

    // 2. Load JSON data
    $jsonFile = __DIR__ . '/../wp-private/blood_centers_data.json';
    if (!file_exists($jsonFile)) {
        die("JSON file not found at $jsonFile\n");
    }
    $centers = json_decode(file_get_contents($jsonFile), true);

    if (!$centers) {
        die("Invalid JSON data or empty file.\n");
    }

    // 3. Sync to DB
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DELETE FROM blood_centers");

    $stmt = $pdo->prepare("INSERT INTO blood_centers (center_id, name, address, city, lat, lng, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($centers as $center) {
        $stmt->execute([
            $center['center_id'],
            $center['name'],
            $center['address'],
            $center['city'],
            $center['lat'],
            $center['lng'],
            $center['contact_number']
        ]);
        echo "Inserted/Updated: " . $center['name'] . "\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Sync completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>