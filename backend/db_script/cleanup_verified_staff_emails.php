<?php
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getConnection();

// Delete verified emails older than 1 hour (they should have been used by then)
$sql = "DELETE FROM verified_staff_emails WHERE verified_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "Cleaned up $count old verified email(s).";
} catch (PDOException $e) {
    echo "Error cleaning up: " . $e->getMessage();
}
