<?php
// backend/api/cancel_order_mobile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

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
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['user_id'] ?? null;
    $orderId = $data['order_id'] ?? null;

    if (!$userId || !$orderId) {
        echo json_encode(['success' => false, 'message' => 'User ID and Order ID are required.']);
        exit;
    }

    $result = $orderService->cancelOrder($userId, $orderId);
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
