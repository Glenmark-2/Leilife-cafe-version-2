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

if (!empty($data->orderId) && !empty($data->status)) {
    try {
        $result = $orderRepo->updateStatus($data->orderId, $data->status);

        // Update order items with the same status
        $orderRepo->updateItemsStatusByOrderId($data->orderId, $data->status);

        if ($result) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Order status updated."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update order status."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
