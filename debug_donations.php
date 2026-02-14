<?php
include __DIR__ . '/database/config.php';

echo "--- USERS (Limit 5) ---\n";
$stmt = $pdo->query("SELECT user_id, first_name, last_name, email, role FROM users LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- DONOR PROFILES ---\n";
$stmt = $pdo->query("SELECT * FROM donor_profiles LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- DONATIONS ---\n";
$stmt = $pdo->query("SELECT * FROM donations");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>