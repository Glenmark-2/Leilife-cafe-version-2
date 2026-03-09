<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';

$orderId = $_GET['order_id'] ?? null;
$userId = $_GET['user_id'] ?? null;

if (!$orderId || !$userId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'order_id and user_id are required.',
    ]);
    exit;
}

try {
    $db = (new Database())->getConnection();
    $orderRepo = new OrderRepository($db);
    $order = $orderRepo->findById($orderId);

    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Order not found.',
        ]);
        exit;
    }

    if ((string)$order->user_id !== (string)$userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Forbidden.',
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => (string)$order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}

