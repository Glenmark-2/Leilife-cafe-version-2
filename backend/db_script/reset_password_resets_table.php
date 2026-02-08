<?php
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getConnection();

$sql = "DROP TABLE IF EXISTS password_resets;
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $db->exec($sql);
    echo "Table 'password_resets' reset successfully.";
} catch (PDOException $e) {
    echo "Error resetting table: " . $e->getMessage();
}
