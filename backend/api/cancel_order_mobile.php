<?php
// backend/api/cancel_order_mobile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
ini_set('display_errors', '0');
require_once __DIR__ . '/../helpers/SessionManager.php';

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../services/OrderService.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
$productRepo = new ProductRepository($db); 

$orderService = new OrderService($orderRepo, $productRepo, $cartRepo, $transactionRepo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SessionManager::startSession();
    $data = json_decode(file_get_contents("php://input"), true);
    $sessionUserId = SessionManager::get('user_id');
    $requestedUserId = $data['user_id'] ?? null;
    $orderId = $data['order_id'] ?? null;

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

    if (!$orderId) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
        exit;
    }

    $result = $orderService->cancelOrder($sessionUserId, $orderId);
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
