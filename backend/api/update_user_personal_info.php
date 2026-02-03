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

// 3. Set header for JSON response
header("Content-Type: application/json");

try {
    // 4. Initialize the Database and get the connection
    $database = new Database();
    $dbConnection = $database->getConnection(); 

    // 5. Manual Dependency Injection
    $userService = new UserService($dbConnection); 
    $userController = new UserController($userService);

    // 6. Route the request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle both JSON and Form-Data
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        
        // Mobile-Friendly: if session isn't set (common in mobile fetch), use user_id from body
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? ($data['user_id'] ?? null);

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: User ID not found.']);
            return;
        }

        // We bypass the controller's internal update if we need to pass the explicit userId
        $result = $userService->updateProfile($userId, $data);
        echo json_encode($result);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}