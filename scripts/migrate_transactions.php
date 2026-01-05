<?php
require_once __DIR__ . '/../backend/config/Database.php';

$database = new Database();
$db = $database->getConnection();

$columnsToAdd = [
    'transaction_type' => "ENUM('payment', 'refund', 'chargeback', 'reversal') DEFAULT 'payment' AFTER order_id",
    'description' => "TEXT NULL AFTER amount",
    'raw_response' => "TEXT NULL AFTER payment_method"
];

foreach ($columnsToAdd as $col => $definition) {
    try {
        $db->exec("ALTER TABLE transactions ADD $col $definition");
        echo "Column '$col' added.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false || strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column '$col' already exists.\n";
        } else {
            echo "Error adding '$col': " . $e->getMessage() . "\n";
        }
    }
}

try {
    $sqlEnum = "ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'success', 'failed', 'expired', 'refunded') NOT NULL";
    $db->exec($sqlEnum);
    echo "Status ENUM updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating ENUM: " . $e->getMessage() . "\n";
}
