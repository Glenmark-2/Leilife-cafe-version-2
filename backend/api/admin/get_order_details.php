<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);

$orderId = $_GET['orderId'] ?? null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Order ID is required."]);
    exit;
}

try {
    $order = $orderRepo->findById($orderId);
    if ($order) {
        $items = $orderRepo->getOrderItems($orderId);

        // Prepare items array
        $itemsArray = [];
        foreach ($items as $item) {
            $itemsArray[] = [
                'id' => $item->id,
                'status' => $item->status,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal
            ];
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => [
                "order_number" => $order->order_number,
                "total_amount" => $order->total_amount,
                "delivery_fee" => $order->delivery_fee,
                "items" => $itemsArray
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Order not found."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
