<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/UserRegistrationRepository.php';
require_once __DIR__ . '/../services/AuthService.php';

$database = new Database();
$db = $database->getConnection();

$userRepository = new UserRepository($db);
$userRegistrationRepository = new UserRegistrationRepository($db);
$authService = new AuthService($userRepository, $userRegistrationRepository);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email)) {
    $result = $authService->resendOTP($data->email);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
}
?>
