<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/config.php';

$envFile = __DIR__ . '/../../wp-private/.env';
$apiKey = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'AI_API_KEY') {
            $apiKey = trim($value);
            break;
        }
    }
}

if (!$apiKey) {
    file_put_contents(__DIR__ . '/models_list.json', json_encode(['error' => 'No API key']));
    exit();
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$output = [
    'http_code' => $httpCode,
    'response' => json_decode($response, true)
];

file_put_contents(__DIR__ . '/models_list.json', json_encode($output, JSON_PRETTY_PRINT));
echo "Done. Saved to models_list.json\n";
