<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../controllers/OrderController.php';

// Instantiate DB & Connect
$database = new Database();
$db = $database->getConnection();

require_once __DIR__ . '/../repositories/CartRepository.php';

// Instantiate Dependencies
$orderRepo = new OrderRepository($db);
$cartRepo = new CartRepository($db);
$productRepo = new ProductRepository(); 

$orderService = new OrderService($orderRepo, $productRepo, $cartRepo);
$orderController = new OrderController($orderService);

// Route Request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $orderController->getUserOrders();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
