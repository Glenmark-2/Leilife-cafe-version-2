<?php
$host = 'localhost';
$db   = 'leilife_v2';
$user = 'root';
$pass = 'Mysqlpassword123!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $pdo->exec("ALTER TABLE user_registrations ADD COLUMN otp_code VARCHAR(6) AFTER verification_token");
     echo "Successfully added otp_code column to user_registrations table.\n";
} catch (\PDOException $e) {
     if ($e->getCode() == '42S21') {
         echo "Column otp_code already exists.\n";
     } else {
         echo "Error: " . $e->getMessage() . "\n";
     }
}
?>
