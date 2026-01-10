<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

require_once __DIR__ . '/../backend/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color:green; font-weight:bold;'>Database Connected Successfully!</p>";
        echo "<p>Host: " . (getenv('DB_HOST') ?: 'Default') . "</p>";
        echo "<p>DB Name: " . (getenv('DB_NAME') ?: 'Default') . "</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>Database Connection Failed (returned null).</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>Exception: " . $e->getMessage() . "</p>";
}

echo "<h2>Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['test_count'])) {
    $_SESSION['test_count'] = 0;
    echo "<p>Session started. Count: 0 (Refresh to increment)</p>";
} else {
    $_SESSION['test_count']++;
    echo "<p style='color:green; font-weight:bold;'>Session Active! Count: " . $_SESSION['test_count'] . "</p>";
}
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Cookie Params: " . json_encode(session_get_cookie_params()) . "</p>";

echo "<h2>Environment Variables Check</h2>";
$envPath = __DIR__ . '/../.env';
echo "<p>Checking for .env at: $envPath</p>";

if (file_exists($envPath)) {
    echo "<p style='color:green;'>.env file FOUND.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>.env file NOT FOUND. Please upload it.</p>";
}
?>
