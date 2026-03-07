<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/SettingsRepository.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';
require_once __DIR__ . '/../../services/DeliveryQuoteService.php';

$database = new Database();
$db = $database->getConnection();
$settingsRepo = new SettingsRepository($db);
$userRepo = new UserRepository($db);

try {
    $settings = $settingsRepo->getSettings();
    $storeCoords = DeliveryQuoteService::getStoreCoordsFromSettings($settings);

    if (!$storeCoords) {
        echo json_encode([
            'success' => false,
            'message' => 'Store coordinates are not configured in settings.'
        ]);
        exit;
    }

    $lat = DeliveryQuoteService::parseCoord($_GET['latitude'] ?? null);
    $lng = DeliveryQuoteService::parseCoord($_GET['longitude'] ?? null);

    if (($lat === null || $lng === null) && !empty($_GET['user_id'])) {
        $user = $userRepo->findById($_GET['user_id']);
        if ($user) {
            $lat = DeliveryQuoteService::parseCoord($user->latitude ?? null);
            $lng = DeliveryQuoteService::parseCoord($user->longitude ?? null);
        }
    }

    if ($lat === null || $lng === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Customer coordinates are missing.'
        ]);
        exit;
    }

    $distanceKm = DeliveryQuoteService::haversineKm(
        $storeCoords['latitude'],
        $storeCoords['longitude'],
        $lat,
        $lng
    );
    $deliveryFee = DeliveryQuoteService::calculateFee($distanceKm);
    $eta = DeliveryQuoteService::estimateWindow($distanceKm);

    echo json_encode([
        'success' => true,
        'data' => [
            'distance_km' => round($distanceKm, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'eta_min' => $eta['min'],
            'eta_max' => $eta['max'],
            'store_latitude' => $storeCoords['latitude'],
            'store_longitude' => $storeCoords['longitude'],
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}

