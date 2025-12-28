<?php
// Find DB config from .env
$env = file_get_contents(__DIR__ . '/../../.env');
preg_match('/DB_NAME=(.*)/', $env, $m1);
preg_match('/DB_USER=(.*)/', $env, $m2);
preg_match('/DB_PASS=(.*)/', $env, $m3);

$db_name = trim($m1[1] ?? 'leilife_v2');
$db_user = trim($m2[1] ?? 'root');
$db_pass = trim($m3[1] ?? '');

try {
    // Force 127.0.0.1 to avoid socket issues on Mac CLI
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE orders ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) NULL;";
    $pdo->exec($sql);

    echo "Migration successful: contact_number added to orders table.\n";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
