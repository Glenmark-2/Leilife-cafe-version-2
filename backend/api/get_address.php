<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
ini_set('display_errors', '0');
require_once __DIR__ . '/../helpers/SessionManager.php';

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

$database = new Database();
$db = $database->getConnection();
$userRepository = new UserRepository($db);

$requestedUserId = $_GET['user_id'] ?? null;
SessionManager::startSession();
$sessionUserId = SessionManager::get('user_id');

if (!$sessionUserId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($requestedUserId !== null && strval($requestedUserId) !== strval($sessionUserId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: user mismatch.']);
    exit;
}

$user = $userRepository->findById($sessionUserId);

if ($user) {
    $address = [
        'street' => $user->street ?? null,
        'barangay' => $user->barangay ?? null,
        'city' => $user->city ?? null,
        'province' => $user->province ?? null,
        'region' => $user->region ?? null,
        'latitude' => $user->latitude ?? null,
        'longitude' => $user->longitude ?? null
    ];

    $hasAddressData = false;
    foreach ($address as $value) {
        if ($value === null) {
            continue;
        }

        $text = trim((string)$value);
        if ($text !== '' && strtolower($text) !== 'null') {
            $hasAddressData = true;
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'address' => $hasAddressData ? $address : null
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}
