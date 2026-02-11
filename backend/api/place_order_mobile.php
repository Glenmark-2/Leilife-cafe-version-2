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
require_once __DIR__ . '/../services/OrderService.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);
$productRepo = new ProductRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
$settingsRepo = new SettingsRepository($db);

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

    $data['platform'] = 'mobile';
    $result = $orderService->placeOrder($userId, $data);
    
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
