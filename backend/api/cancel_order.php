<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';

// Instantiate DB & Connect (if needed by repositories internally)
// However, Repositories usually take DB in constructor
$database = new Database();
$db = $database->getConnection();

// Instantiate Dependencies
$orderRepo = new OrderRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
$productRepo = new ProductRepository(); 

$orderService = new OrderService($orderRepo, $productRepo, $cartRepo, $transactionRepo);
$orderController = new OrderController($orderService);

// Route Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderController->cancelOrder();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
