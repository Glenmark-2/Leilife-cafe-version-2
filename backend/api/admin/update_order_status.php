<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../repositories/ProductRepository.php';
require_once __DIR__ . '/../../repositories/CartRepository.php';
require_once __DIR__ . '/../../repositories/TransactionRepository.php';
require_once __DIR__ . '/../../services/OrderService.php';
require_once __DIR__ . '/../../services/RealtimeService.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
$productRepo = new ProductRepository(); 

$orderService = new OrderService($orderRepo, $productRepo, $cartRepo, $transactionRepo);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->orderId) && !empty($data->status)) {
    try {
        if ($data->status === 'cancelled') {
            // Use OrderService logic for cancellation (includes PayMongo refund)
            $order = $orderRepo->findById($data->orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Order not found."]);
                exit();
            }
            $result = $orderService->cancelOrder($order->user_id, $data->orderId);
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $result['message']]);
            }
        } else {
            // Fetch order to get user_id for notification
            $order = $orderRepo->findById($data->orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Order not found."]);
                exit();
            }

            // Standard status update
            $result = $orderRepo->updateStatus($data->orderId, $data->status);
            
            // Map order status to item status (pending, preparing, finished, cancelled)
            $itemStatus = $data->status;
            if (in_array($data->status, ['ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered'])) {
                $itemStatus = 'finished';
            }
            
            $orderRepo->updateItemsStatusByOrderId($data->orderId, $itemStatus);

            // Trigger Real-time events
            RealtimeService::trigger('order-' . $data->orderId, 'status-updated', ['status' => $data->status]);
            RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $data->orderId, 'status' => $data->status]);
            RealtimeService::trigger('user-' . $order->user_id, 'order-status-changed', ['orderId' => $data->orderId, 'status' => $data->status]);

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Order status updated."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Failed to update status."]);
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
