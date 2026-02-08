<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/RealtimeService.php';

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

        $stmt = $db->prepare("SELECT status, assigned_driver_id FROM orders WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) throw new Exception("Order not found.");
        if ($order['assigned_driver_id'] !== null) throw new Exception("Order already assigned.");
        if ($order['status'] !== 'out_for_delivery') throw new Exception("Order not available.");

        $stmt = $db->prepare("UPDATE orders SET assigned_driver_id = :driver_id WHERE id = :id");
        $stmt->execute(['driver_id' => $driverId, 'id' => $orderId]);

        $db->commit();

        RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'out_for_delivery', 'driverId' => $driverId]);
        
        echo json_encode(['success' => true, 'message' => 'Order accepted successfully']);

    } elseif ($action === 'complete') {
        $stmt = $db->prepare("SELECT id FROM orders WHERE id = :id AND assigned_driver_id = :driver_id AND status = 'out_for_delivery'");
        $stmt->execute(['id' => $orderId, 'driver_id' => $driverId]);
        if (!$stmt->fetch()) throw new Exception("Active order not found for this driver.");

        $stmt = $db->prepare("UPDATE orders SET status = 'delivered', payment_status = 'paid' WHERE id = :id");
        $stmt->execute(['id' => $orderId]);

        RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'delivered']);
        
        $stmt = $db->prepare("SELECT user_id FROM orders WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        $userId = $stmt->fetchColumn();
        if ($userId) {
            RealtimeService::trigger('user-' . $userId, 'order-status-changed', ['orderId' => $orderId, 'status' => 'delivered']);
        }

        echo json_encode(['success' => true, 'message' => 'Delivery completed successfully']);
        
    } else {
        throw new Exception("Invalid action.");
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
