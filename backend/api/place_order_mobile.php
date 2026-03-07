<?php
// backend/api/place_order_mobile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../repositories/SettingsRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../services/DeliveryQuoteService.php';
require_once __DIR__ . '/../helpers/NotificationHelper.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);
$productRepo = new ProductRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
$settingsRepo = new SettingsRepository($db);
$userRepo = new UserRepository($db);

$orderService = new OrderService($orderRepo, $productRepo, $cartRepo, $transactionRepo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required for mobile orders.']);
        exit;
    }

    $settings = $settingsRepo->getSettings();
    if (!$settings['is_store_open']) {
        echo json_encode(['success' => false, 'message' => 'Store is currently closed. Cannot place order.']);
        exit;
    }

    // Mobile-only authoritative delivery fee and ETA computation.
    $deliveryMethod = $data['delivery_method'] ?? 'delivery';
    if ($deliveryMethod !== 'pickup') {
        $storeCoords = DeliveryQuoteService::getStoreCoordsFromSettings($settings);
        if (!$storeCoords) {
            echo json_encode([
                'success' => false,
                'message' => 'Store coordinates are not configured. Please set them in Admin > Settings > Delivery.'
            ]);
            exit;
        }

        $customerLat = DeliveryQuoteService::parseCoord($data['latitude'] ?? null);
        $customerLng = DeliveryQuoteService::parseCoord($data['longitude'] ?? null);
        if ($customerLat === null || $customerLng === null) {
            $user = $userRepo->findById($userId);
            $customerLat = DeliveryQuoteService::parseCoord($user->latitude ?? null);
            $customerLng = DeliveryQuoteService::parseCoord($user->longitude ?? null);
        }
        if ($customerLat === null || $customerLng === null) {
            echo json_encode([
                'success' => false,
                'message' => 'Customer address coordinates are missing. Please update your delivery address.'
            ]);
            exit;
        }

        $distanceKm = DeliveryQuoteService::haversineKm(
            $storeCoords['latitude'],
            $storeCoords['longitude'],
            $customerLat,
            $customerLng
        );
        $deliveryFee = DeliveryQuoteService::calculateFee($distanceKm);
        $eta = DeliveryQuoteService::estimateWindow($distanceKm);

        // Override client fee for consistency and security.
        $data['delivery_fee'] = $deliveryFee;
        $data['estimated_eta_min'] = $eta['min'];
        $data['estimated_eta_max'] = $eta['max'];
        $data['delivery_distance_km'] = round($distanceKm, 2);
    } else {
        $data['delivery_fee'] = 0;
    }

    $data['platform'] = 'mobile';
    $result = $orderService->placeOrder($userId, $data);
    
    if ($result['success']) {
        http_response_code(201);
        
        // --- NEW: Notify Admins via Push ---
        try {
            $adminTokens = NotificationHelper::getTokensByRole('admin', $db);
            if (!empty($adminTokens)) {
                $orderNum = $result['order_number'] ?? 'New';
                NotificationHelper::sendPushToMany(
                    $adminTokens,
                    "New Order 🔔",
                    "A new order (#$orderNum) has been placed.",
                    ["orderId" => $result['order_id'] ?? null, "type" => "new_order"]
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send admin notification: " . $e->getMessage());
        }
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
