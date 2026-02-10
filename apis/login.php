<?php
session_start();
include_once __DIR__ . '/../database/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];

            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$user['user_id']]);

            echo json_encode(['success' => true, 'message' => 'Login successful!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again later.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
