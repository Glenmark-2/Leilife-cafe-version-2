<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/UserRegistrationRepository.php';
require_once __DIR__ . '/../repositories/PasswordResetRepository.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();

$userRepository = new UserRepository($db);
$userRegistrationRepository = new UserRegistrationRepository($db);
$passwordResetRepository = new PasswordResetRepository($db);
$authService = new AuthService($userRepository, $userRegistrationRepository, $passwordResetRepository);
$authController = new AuthController($authService);

$authController->forgotPasswordReset();
