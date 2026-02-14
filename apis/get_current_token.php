<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/jwt_helper.php';

session_start();


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

try {
    
    $token = JWTHelper::generate([
        'user_id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'exp' => time() + (86400 * 30) 
    ]);

    echo json_encode([
        'success' => true,
        'token' => $token
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error generating token.']);
}
