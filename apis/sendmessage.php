<?php
session_start();
include_once __DIR__ . '/../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $sender_id = $_SESSION['user_id'] ?? null; 
    $receiver_id = 1; 
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject_raw = $_POST['subject'] ?? '';
    $subject = "Message from contact: " . $subject_raw;
    $message_content = $_POST['message'] ?? '';
    
    $full_message = "Name: $name\nEmail: $email\nPhone: $phone\n\nContent:\n$message_content";

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message_content, sent_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$sender_id, $receiver_id, $subject, $full_message])) {
            header("Location: ../?status=success#contact");
            exit();
        } else {
            header("Location: ../?status=error&msg=Failed to save message. Please try again.#contact");
            exit();
        }
    } catch (PDOException $e) {
        $error_msg = urlencode("Database error: " . $e->getMessage());
        header("Location: ../?status=error&msg=$error_msg#contact");
        exit();
    }
} else {
    header("Location: ../");
    exit();
}
?>
