<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/jwt_helper.php';

session_start();

// Get the token from the request
$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'No token provided.']);
    exit();
}

$payload = JWTHelper::verify($token);

if ($payload && isset($payload['user_id'])) {
    // Check if token is expired
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        echo json_encode(['success' => false, 'message' => 'Token has expired.']);
        exit();
    }

    // Re-fetch user data to ensure the account is still valid/active
    try {
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, role FROM users WHERE user_id = ?");
        $stmt->execute([$payload['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];

            echo json_encode([
                'success' => true,
                'message' => 'Session restored.',
                'role' => $user['role']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User no longer exists.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid token.']);
}
