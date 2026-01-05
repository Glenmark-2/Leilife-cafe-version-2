<?php
require_once __DIR__ . '/../backend/config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $db->exec("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid', 'paid', 'refunded', 'partially_refunded') DEFAULT 'unpaid'");
    echo "Orders table updated: partially_refunded added to payment_status.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
