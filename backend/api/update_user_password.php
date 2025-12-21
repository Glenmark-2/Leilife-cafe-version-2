<?php
// 1. Include dependencies
require_once __DIR__ . '/../config/Database.php'; 
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../controllers/UserController.php';

// 2. Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Set header
header("Content-Type: application/json");

try {
    // 4. Init
    $database = new Database();
    $dbConnection = $database->getConnection(); 
    
    $userService = new UserService($dbConnection); 
    $userController = new UserController($userService);

    // 5. Route
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController->updatePassword();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
