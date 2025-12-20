<?php
require_once __DIR__ . '/../config/Database.php'; 
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json");

try {
    $database = new Database();
    $dbConnection = $database->getConnection(); 

    $userService = new UserService($dbConnection); 
    $userController = new UserController($userService);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController->updateAddress();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
