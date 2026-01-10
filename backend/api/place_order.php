<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../controllers/OrderController.php';

// Instantiate DB & Connect
$database = new Database();
$db = $database->getConnection();

require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';

// Instantiate Dependencies
$orderRepo = new OrderRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
// ProductRepository constructor inside ProductRepository.php creates its own DB connection internally? 
// Let's check ProductRepository.php.
// Checking file... It says: public function __construct() { $database = new Database(); $this->conn = $database->getConnection(); }
// So it doesn't take $db in constructor.
$productRepo = new ProductRepository($db);

$orderService = new OrderService($orderRepo, $productRepo, $cartRepo, $transactionRepo);
$orderController = new OrderController($orderService);

// Route Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if store is open
    require_once __DIR__ . '/../repositories/SettingsRepository.php';
    $settingsRepo = new SettingsRepository($db);
    $settings = $settingsRepo->getSettings();
    
    if (!$settings['is_store_open']) {
         echo json_encode(['success' => false, 'message' => 'Store is currently closed. Cannot place order.']);
         exit;
    }

    $orderController->placeOrder();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
