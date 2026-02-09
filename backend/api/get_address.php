<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

$database = new Database();
$db = $database->getConnection();
$userRepository = new UserRepository($db);

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

$user = $userRepository->findById($userId);

if ($user) {
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'address' => [
            'street' => $user->street,
            'barangay' => $user->barangay,
            'city' => $user->city,
            'province' => $user->province,
            'region' => $user->region,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}
