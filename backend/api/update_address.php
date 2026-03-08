<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
require_once __DIR__ . '/../helpers/SessionManager.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

SessionManager::startSession();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$sessionUserId = SessionManager::get('user_id');
$requestedUserId = $data['user_id'] ?? ($_GET['user_id'] ?? null);

if ($requestedUserId !== null && strval($requestedUserId) !== strval($sessionUserId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: user mismatch']);
    exit;
}

$userId = $sessionUserId;

if (
    !isset($data['street']) || !isset($data['barangay']) || !isset($data['city']) ||
    !isset($data['province']) || !isset($data['region'])
) {
    echo json_encode(['success' => false, 'message' => 'Missing required address fields']);
    exit;
}
$street = $data['street'];
$barangay = $data['barangay'];
$city = $data['city'];
$province = $data['province'];
$region = $data['region'];
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

try {
    $database = new Database();
    $db = $database->getConnection();

    $success = User::updateUserAddress($db, $userId, $street, $barangay, $city, $province, $region, $latitude, $longitude);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Address saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save address']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
