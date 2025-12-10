<?php
$start = microtime(true);

require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

$end = microtime(true);
echo "Database Connection Time: " . ($end - $start) . " seconds";
