<?php
header('Content-Type: application/json');


function getFinalUrl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $finalUrl;
}

$data = json_decode(file_get_contents('php://input'), true);
$url = $data['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'message' => 'No URL provided']);
    exit;
}


if (strpos($url, 'google.com/maps') === false && strpos($url, 'maps.app.goo.gl') === false && strpos($url, 'goo.gl/maps') === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid Google Maps URL']);
    exit;
}

$finalUrl = getFinalUrl($url);


$lat = null;
$lng = null;


if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
} elseif (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
} elseif (preg_match('/search\/(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
} elseif (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $finalUrl, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
}

if ($lat && $lng) {
    echo json_encode(['success' => true, 'lat' => $lat, 'lng' => $lng, 'final_url' => $finalUrl]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not extract coordinates from URL', 'debug_url' => $finalUrl]);
}
