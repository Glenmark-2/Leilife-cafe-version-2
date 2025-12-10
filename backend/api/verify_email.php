<?php
// Headers to handle CORS and JSON content
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: POST, GET"); // GET is fine for link clicking

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/UserRegistrationRepository.php';
require_once __DIR__ . '/../services/AuthService.php';

// Instantiate DB & Connect
$database = new Database();
$db = $database->getConnection();

// Instantiate Repository, Service
$userRepository = new UserRepository($db);
$userRegistrationRepository = new UserRegistrationRepository($db);
$authService = new AuthService($userRepository, $userRegistrationRepository);

// Get Token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token is missing.']);
    exit;
}

// Verify
$result = $authService->verifyEmail($token);

echo json_encode($result);
