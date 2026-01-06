<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../services/RealtimeService.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->orderId)) {
    try {
        $orderId = $data->orderId;
        $order = $orderRepo->findById($orderId);

        if (!$order) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Order not found."]);
            exit();
        }

        // Update payment status to 'paid'
        $result = $orderRepo->updatePaymentStatus($orderId, 'paid');

        if ($result) {
            // Trigger Real-time events to stop the timer on customer side
            RealtimeService::trigger('order-' . $orderId, 'status-updated', [
                'status' => $order->status,
                'payment_status' => 'paid'
            ]);

            // Trigger admin update to refresh UI
            RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId]);

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Order marked as paid."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update payment status."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
