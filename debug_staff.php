<?php
require_once __DIR__ . '/backend/config/Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed\n");
}

echo "--- Staffs Table Raw Data ---\n";
$query = "SELECT staff_id, full_name, role, status, is_archived FROM staffs";
$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "ID: " . $row['staff_id'] . " | Name: " . $row['full_name'] . " | Role: " . $row['role'] . " | Status: " . $row['status'] . " | Archived: " . $row['is_archived'] . "\n";
}

echo "\n--- Aggregated Stats (Dashboard Logic) ---\n";
$queryStats = "SELECT role, COUNT(*) as count FROM staffs WHERE status = 'Active' AND is_archived = 0 GROUP BY role";
$stmtStats = $db->prepare($queryStats);
$stmtStats->execute();
while ($row = $stmtStats->fetch(PDO::FETCH_ASSOC)) {
    echo "Role: " . $row['role'] . " | Count: " . $row['count'] . "\n";
}
