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

if (!empty($data->itemId) && !empty($data->status)) {
    try {
        if ($data->status === 'cancelled') {
            // Use the advanced cancellation/refund logic
            // Since this is Admin, we pass a system user ID or fetch order user ID
            $item = $orderRepo->getOrderItemById($data->itemId);
            if (!$item) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Item not found."]);
                exit();
            }
            $order = $orderRepo->findById($item['order_id']);
            $result = $orderService->cancelOrderItem($order->user_id, $data->itemId, 'requested_by_customer');
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $result['message']]);
            }
        } else {
            // Standard status update
            $result = $orderRepo->updateOrderItemStatus($data->itemId, $data->status);
            if ($result) {
                // Trigger Real-time event (Optional: Items might need specific channel)
                $item = $orderRepo->getOrderItemById($data->itemId);
                if ($item) {
                    RealtimeService::trigger('order-' . $item['order_id'], 'item-updated', [
                        'itemId' => $data->itemId,
                        'status' => $data->status
                    ]);
                    // Also trigger admin dashboard refresh if needed
                    RealtimeService::trigger('admin-orders', 'item-updated', ['orderId' => $item['order_id']]);
                }

                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Order item status updated."]);
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
