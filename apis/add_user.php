<?php
session_start();
include_once __DIR__ . '/../database/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['userType'] ?? '';
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $bloodType = $_POST['bloodType'] ?? null;
    $password = $_POST['password'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password) || empty($userType)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit();
    }

    $role = ($userType === 'hospital') ? 'Hospital Admin' : 'Donor';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, role, gender, date_of_birth, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$firstName, $lastName, $email, $phone, $passwordHash, $role, ucfirst($gender), $dob]);
        $userId = $pdo->lastInsertId();

        if ($role === 'Donor' && !empty($bloodType)) {
            $stmtDonor = $pdo->prepare("INSERT INTO donor_profiles (donor_id, blood_type, weight_kg) VALUES (?, ?, ?)");
            
            $stmtDonor->execute([$userId, $bloodType, 0]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Email or phone number already registered.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

