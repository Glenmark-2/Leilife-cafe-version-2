<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Instantiate DB & Connect
$database = new Database();
$db = $database->getConnection();

// Instantiate
$userRepository = new UserRepository($db);
// We don't need UserRegistrationRepo here as Google Users are already verified
$authService = new AuthService($userRepository);

// Get POST data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->token)) {
    $result = $authService->loginWithGoogle($data->token);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'No token provided.']);
}
