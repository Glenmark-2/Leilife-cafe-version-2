<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/UserRepository.php';

echo "Testing User Fetching...\n";

try {
    $db = (new Database())->getConnection();
    $repo = new UserRepository($db);
    
    // We'll try to fetch a user. We might not know a valid ID, so we'll try to query one first.
    // This is just a test script, so we'll do a quick raw query to get an ID.
    $stmt = $db->query("SELECT id FROM users LIMIT 1");
    $id = $stmt->fetchColumn();

    if ($id) {
        echo "Found user ID: $id\n";
        $user = $repo->findById($id);
        if ($user) {
            echo "Successfully fetched user: " . $user->first_name . " " . $user->last_name . "\n";
            echo "Email: " . $user->email . "\n";
        } else {
            echo "Failed to fetch user with ID $id via Repository.\n";
        }
    } else {
        echo "No users found in database to test with.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
