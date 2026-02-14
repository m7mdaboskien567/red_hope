<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

include_once __DIR__ . '/../../database/config.php';

try {
    if ($action === 'create') {
        
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            throw new Exception("All fields are required");
        }

        
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            throw new Exception("Email already exists");
        }

        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone, password_hash, role, gender, date_of_birth, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $password_hash,
            $data['role'],
            $data['gender'],
            $data['date_of_birth']
        ]);

        echo json_encode(['success' => true, 'message' => 'User created successfully']);

    } elseif ($action === 'update') {
        $user_id = $data['user_id'];
        
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, role = ? WHERE user_id = ?";
        $params = [
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['role'],
            $user_id
        ];

        
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, role = ?, password_hash = ? WHERE user_id = ?";
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['role'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $user_id
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'User updated successfully']);

    } elseif ($action === 'delete') {
        $user_id = $data['user_id'];
        
        if ($user_id == $_SESSION['user_id']) {
            throw new Exception("You cannot delete yourself");
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
