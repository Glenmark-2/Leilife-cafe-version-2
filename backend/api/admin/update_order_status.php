<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
ini_set('display_errors', '0');

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../repositories/ProductRepository.php';
require_once __DIR__ . '/../../repositories/CartRepository.php';
require_once __DIR__ . '/../../repositories/TransactionRepository.php';
require_once __DIR__ . '/../../services/OrderService.php';
require_once __DIR__ . '/../../services/RealtimeService.php';
require_once __DIR__ . '/../../helpers/NotificationHelper.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);
$cartRepo = new CartRepository($db);
$transactionRepo = new TransactionRepository($db);
$productRepo = new ProductRepository($db); 

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
                // Send Push Notification
                $userQuery = "SELECT push_token FROM users WHERE id = ?";
                $userStmt = $db->prepare($userQuery);
                $userStmt->execute([$order->user_id]);
                $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userRow && !empty($userRow['push_token'])) {
                    NotificationHelper::sendPush(
                        $userRow['push_token'],
                        "Order Cancelled",
                        "Your order #{$order->order_number} has been cancelled.",
                        ["order_id" => $data->orderId, "status" => "cancelled"]
                    );
                }

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

            // Send Push Notification
            $userQuery = "SELECT push_token FROM users WHERE id = ?";
            $userStmt = $db->prepare($userQuery);
            $userStmt->execute([$order->user_id]);
            $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userRow && !empty($userRow['push_token'])) {
                $statusLabels = [
                    'pending' => 'is now pending',
                    'preparing' => 'is being prepared',
                    'ready_for_pickup' => 'is ready for pickup',
                    'out_for_delivery' => 'is out for delivery',
                    'delivered' => 'has been delivered',
                    'picked_up' => 'has been picked up'
                ];
                $statusText = $statusLabels[$data->status] ?? "status has been updated to {$data->status}";
                
                NotificationHelper::sendPush(
                    $userRow['push_token'],
                    "Order Update",
                    "Your order #{$order->order_number} {$statusText}.",
                    ["order_id" => $data->orderId, "status" => $data->status]
                );
            }

            // --- NEW: Notify Drivers if order is ready for delivery ---
            if ($data->status === 'out_for_delivery' && $order->delivery_method === 'delivery') {
                try {
                    $driverTokens = NotificationHelper::getTokensByRole('driver', $db);
                    if (!empty($driverTokens)) {
                        NotificationHelper::sendPushToMany(
                            $driverTokens,
                            "New Delivery Available 🛵",
                            "Order #{$order->order_number} is ready for dispatch.",
                            ["orderId" => $data->orderId, "type" => "available_delivery"]
                        );
                    }
                } catch (Exception $e) {
                    error_log("Driver Notif Error: " . $e->getMessage());
                }
            }

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
