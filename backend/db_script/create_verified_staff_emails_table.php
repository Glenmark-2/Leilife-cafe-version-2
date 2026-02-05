<?php
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS verified_staff_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
)";

try {
    $db->exec($sql);
    echo "Table 'verified_staff_emails' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
