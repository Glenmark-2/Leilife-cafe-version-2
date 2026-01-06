<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../helpers/SessionManager.php';
require_once __DIR__ . '/../config/Database.php';

// In a real system, we'd check if role is 'driver'
// SessionManager::requireLogin();
// if (SessionManager::get('user_role') !== 'driver') { ... }

$driverId = SessionManager::get('driver_id') ?? 1; // Fallback for testing

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$orderId = $_POST['order_id'] ?? null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$db = (new Database())->getConnection();

try {
    $db->beginTransaction();

    // Check if order is available for dispatch
    $stmt = $db->prepare("SELECT status, assigned_driver_id FROM orders WHERE id = :id FOR UPDATE");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found.");
    }

    if ($order['assigned_driver_id'] !== null) {
        throw new Exception("Order already assigned to another driver.");
    }

    // Accept criteria: must be 'out_for_delivery' and unassigned
    if ($order['status'] !== 'out_for_delivery') {
        throw new Exception("Order is not available for dispatch.");
    }

    // Update order (stay in 'out_for_delivery' but assign driver)
    $stmt = $db->prepare("UPDATE orders SET assigned_driver_id = :driver_id WHERE id = :id");
    $stmt->execute([
        'driver_id' => $driverId,
        'id' => $orderId
    ]);

    $db->commit();

    // Trigger Real-time update for Admin and other drivers
    require_once __DIR__ . '/../services/RealtimeService.php';
    RealtimeService::trigger('admin-orders', 'status-updated', [
        'orderId' => $orderId, 
        'status' => 'out_for_delivery',
        'driverId' => $driverId
    ]);

    // Also notify the specific order tracking page
    RealtimeService::trigger('order-' . $orderId, 'status-updated', ['status' => 'out_for_delivery']);
    
    // Redirect back to deliveries page
    header("Location: ../../public/driver.php?page=deliveries&accepted=1");
    exit;

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
