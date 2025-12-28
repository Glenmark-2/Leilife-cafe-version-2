<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->itemId) && !empty($data->status)) {
    try {
        // Optional: Validate status against valid ENUM values
        $validStatuses = ['pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered', 'cancelled', 'payment_failed'];
        if (!in_array($data->status, $validStatuses)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid status value."]);
            exit();
        }

        $result = $orderRepo->updateOrderItemStatus($data->itemId, $data->status);

        if ($result) {
            // Check if this is the only item in the order
            $orderId = $orderRepo->getOrderIdByItemId($data->itemId);
            if ($orderId) {
                $itemCount = $orderRepo->getOrderItemCountByOrderId($orderId);
                if ($itemCount == 1) {
                    // Update the main order status as well
                    $orderRepo->updateStatus($orderId, $data->status);
                }
            }

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Order item status updated."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update order item status."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
