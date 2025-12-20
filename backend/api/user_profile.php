<?php
// Headers to handle CORS and JSON content
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET"); // Added GET for findById type operations

// --- 1. Include Necessary Files ---
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php'; 
require_once __DIR__ . '/../services/AuthService.php';   
require_once __DIR__ . '/../controllers/AuthController.php';  
// --- 2. Instantiate DB & Connect ---
$database = new Database();
$db = $database->getConnection();

// --- 3. Instantiate Layers (Bottom-Up Dependency Injection) ---

// 3a. Instantiate the Repository (needs $db)
// Assuming your repository is called UserRepository and handles data access.
$userRepository = new UserRepository($db); 

// 3b. Instantiate the Service (needs $userRepository)
$authService = new AuthService($userRepository);

// 3c. Instantiate the Controller (needs $authService)
// This is the object you were missing!
$authController = new AuthController($authService); 


// --- 4. Call the Appropriate Controller Method ---

// The Controller's job is to handle the request, e.g., fetching a profile.
// The actual method name should reflect the action, not the data access pattern.

// Example: Handling a request to view a user profile by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // You would define a 'profile' method in AuthController
    $authController->profile($_GET['id']);
} else {
    // Or, if you intended to use 'findById' as a temporary endpoint name:
    // This assumes you defined a method called findUserById in your AuthController
    // $authController->findUserById(); 
    
    // Default error or method not supported response
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed or Missing ID.']);
}