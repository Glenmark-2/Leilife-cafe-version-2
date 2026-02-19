<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../repositories/ProductRepository.php';
require_once __DIR__ . '/../../repositories/CartRepository.php';
require_once __DIR__ . '/../../repositories/TransactionRepository.php';
require_once __DIR__ . '/../../services/OrderService.php';
require_once __DIR__ . '/../../services/RealtimeService.php';

// --- Helper Function for Expo Push Notifications ---
function sendExpoPushNotification($token, $title, $body, $data = []) {
    if (empty($token) || strpos($token, 'ExponentPushToken') === false) return false;
    $payload = [
        "to" => $token,
        "title" => $title,
        "body" => $body,
        "data" => $data,
        "sound" => "default"
    ];
    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

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
        // Fetch order first to get details and user push token
        $stmt = $db->prepare("SELECT o.order_number, o.user_id, u.push_token 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            WHERE o.id = :id");
        $stmt->execute(['id' => $data->orderId]);
        $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Order not found."]);
            exit();
        }

        if ($data->status === 'cancelled') {
            $result = $orderService->cancelOrder($orderData['user_id'], $data->orderId);
            
            if ($result['success']) {
                // --- PUSH: Notify Cancelled ---
                if ($orderData['push_token']) {
                    sendExpoPushNotification(
                        $orderData['push_token'],
                        "Order Cancelled ❌",
                        "Your order #{$orderData['order_number']} has been cancelled.",
                        ["orderId" => $data->orderId, "status" => "cancelled"]
                    );
                }
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $result['message']]);
            }
        } else {
            // Standard status update
            $result = $orderRepo->updateStatus($data->orderId, $data->status);
            
            // Map order status to item status
            $itemStatus = $data->status;
            if (in_array($data->status, ['ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered'])) {
                $itemStatus = 'finished';
            }
            $orderRepo->updateItemsStatusByOrderId($data->orderId, $itemStatus);

            // --- NEW: Trigger Real-time events SAFELY ---
            try {
                RealtimeService::trigger('order-' . $data->orderId, 'status-updated', ['status' => $data->status]);
                RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $data->orderId, 'status' => $data->status]);
                RealtimeService::trigger('user-' . $orderData['user_id'], 'order-status-changed', ['orderId' => $data->orderId, 'status' => $data->status]);
            } catch (Throwable $e) {
                // If Pusher class is missing, we just ignore it and proceed
            }

            // --- PUSH: Dynamic Message Mapping ---
            if ($orderData['push_token']) {
                $messages = [
                    'preparing' => "We are now preparing your order #{$orderData['order_number']}! ☕",
                    'ready_for_pickup' => "Your order #{$orderData['order_number']} is ready for pickup! 🛍️",
                    'out_for_delivery' => "Order #{$orderData['order_number']} is on the way! 🛵",
                    'delivered' => "Order #{$orderData['order_number']} has been delivered! Enjoy! ✅",
                ];
                $body = $messages[$data->status] ?? "Your order #{$orderData['order_number']} status is now: " . ucfirst($data->status);
                
                sendExpoPushNotification(
                    $orderData['push_token'],
                    "LeiLife Order Update",
                    $body,
                    ["orderId" => $data->orderId, "status" => $data->status]
                );
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
?>