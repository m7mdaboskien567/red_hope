<?php
include_once __DIR__ . '/../database/config.php';

try {
    // 1. Drop existing foreign key
    $pdo->exec("ALTER TABLE messages DROP FOREIGN KEY fk_msg_sender");
    
    // 2. Modify column to be NULLABLE
    $pdo->exec("ALTER TABLE messages MODIFY sender_id INT NULL");
    
    // 3. Re-add foreign key with ON DELETE SET NULL
    $pdo->exec("ALTER TABLE messages ADD CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE SET NULL");
    
    echo "Schema fixed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
