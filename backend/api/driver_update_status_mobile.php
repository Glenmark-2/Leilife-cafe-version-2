<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/RealtimeService.php';

// --- NEW FIX: Helper function to send Expo Push Notification ---
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
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$orderId = $data['order_id'] ?? null;
$driverId = $data['driver_id'] ?? null;
$action = $data['action'] ?? null; // 'accept' or 'complete'

if (!$orderId || !$driverId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Order ID, Driver ID, and action are required']);
    exit;
}

$db = (new Database())->getConnection();

try {
    if ($action === 'accept') {
        $db->beginTransaction();

        // Fetched order_number and user_id for the notification
        $stmt = $db->prepare("SELECT status, assigned_driver_id, order_number, user_id FROM orders WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) throw new Exception("Order not found.");
        if ($order['assigned_driver_id'] !== null) throw new Exception("Order already assigned.");
        if ($order['status'] !== 'out_for_delivery') throw new Exception("Order not available.");

        $stmt = $db->prepare("UPDATE orders SET assigned_driver_id = :driver_id WHERE id = :id");
        $stmt->execute(['driver_id' => $driverId, 'id' => $orderId]);

        $db->commit();

        RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'out_for_delivery', 'driverId' => $driverId]);
        
        // --- PUSH NOTIFICATION: Notify Out for Delivery ---
        $stmt = $db->prepare("SELECT push_token FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $order['user_id']]);
        $token = $stmt->fetchColumn();

        if ($token) {
            sendExpoPushNotification(
                $token,
                "Order Update 🛵",
                "Your order #{$order['order_number']} is now out for delivery!",
                ["orderId" => $orderId, "status" => "out_for_delivery"]
            );
        }

        echo json_encode(['success' => true, 'message' => 'Order accepted successfully']);

    } elseif ($action === 'complete') {
        // Get order details and user token first
        $stmt = $db->prepare("SELECT o.id, o.order_number, o.user_id, u.push_token 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            WHERE o.id = :id AND o.assigned_driver_id = :driver_id AND o.status = 'out_for_delivery'");
        $stmt->execute(['id' => $orderId, 'driver_id' => $driverId]);
        $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderInfo) throw new Exception("Active order not found for this driver.");

        $stmt = $db->prepare("UPDATE orders SET status = 'delivered', payment_status = 'paid' WHERE id = :id");
        $stmt->execute(['id' => $orderId]);

        RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'delivered']);
        
        if ($orderInfo['user_id']) {
            RealtimeService::trigger('user-' . $orderInfo['user_id'], 'order-status-changed', ['orderId' => $orderId, 'status' => 'delivered']);

            // --- PUSH NOTIFICATION: Notify Delivered ---
            if (!empty($orderInfo['push_token'])) {
                sendExpoPushNotification(
                    $orderInfo['push_token'],
                    "Order Delivered ✅",
                    "Enjoy your meal! Your order #{$orderInfo['order_number']} has been delivered.",
                    ["orderId" => $orderId, "status" => "delivered"]
                );
            }
        }

        echo json_encode(['success' => true, 'message' => 'Delivery completed successfully']);
        
    } else {
        throw new Exception("Invalid action.");
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>