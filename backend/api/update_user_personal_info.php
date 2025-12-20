<?php
// 1. Include dependencies
require_once __DIR__ . '/../config/Database.php'; 
require_once __DIR__ . '/../models/User.php';  // This should provide your $pdo variable
require_once __DIR__ . '/../services/UserService.php'; // The User class with your static SQL method
require_once __DIR__ . '/../controllers/UserController.php'; // Business logic (validation)

// 2. Start session to access $_SESSION['user_id']
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Set header for JSON response (crucial for your JS fetch)
header("Content-Type: application/json");

try {
    // 4. Initialize the Database and get the connection
    $database = new Database();
    $dbConnection = $database->getConnection(); 

    // 5. Manual Dependency Injection
    // Pass the actual connection ($dbConnection) into the Service
    $userService = new UserService($dbConnection); 
    $userController = new UserController($userService);

    // 6. Route the request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController->update();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}