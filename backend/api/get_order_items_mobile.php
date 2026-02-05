<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';

$database = new Database();
$db = $database->getConnection();
$orderRepo = new OrderRepository($db);

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    $orders = $orderRepo->findByUserId($userId);
    $items = [];
    $seenProducts = [];

    foreach ($orders as $order) {
        if (!isset($order->items) || !is_array($order->items)) continue;
        
        foreach ($order->items as $item) {
            $pId = $item->product_id;
            // Only add unique products to the list
            if (!in_array($pId, $seenProducts)) {
                $items[] = [
                    'product_id' => $pId,
                    'name' => $item->product_name,
                    'price' => (float)$item->price,
                    'image' => $item->product_image,
                    'ordered_at' => $order->created_at,
                    'order_id' => $order->id
                ];
                $seenProducts[] = $pId;
            }
        }
    }

    echo json_encode(['success' => true, 'data' => array_slice($items, 0, 5)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
