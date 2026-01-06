<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../helpers/SessionManager.php';
require_once __DIR__ . '/../config/Database.php';

$driverId = SessionManager::get('driver_id') ?? 1;

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
    // Verify this driver owns the order and it's out for delivery
    $stmt = $db->prepare("SELECT id FROM orders WHERE id = :id AND assigned_driver_id = :driver_id AND status = 'out_for_delivery'");
    $stmt->execute(['id' => $orderId, 'driver_id' => $driverId]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Active order not found for this driver.");
    }

    // Update status to delivered
    // Also mark as paid if it was COD (optional business logic, but common)
    $stmt = $db->prepare("UPDATE orders SET status = 'delivered', payment_status = 'paid' WHERE id = :id");
    $stmt->execute(['id' => $orderId]);

    // Trigger Real-time update
    require_once __DIR__ . '/../services/RealtimeService.php';
    RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'delivered']);
    
    // Notify the specific order tracking page
    RealtimeService::trigger('order-' . $orderId, 'status-updated', ['status' => 'delivered']);

    // Also notify the customer if we know their ID (fetching it first)
    $stmt = $db->prepare("SELECT user_id FROM orders WHERE id = :id");
    $stmt->execute(['id' => $orderId]);
    $userId = $stmt->fetchColumn();
    if ($userId) {
        RealtimeService::trigger('user-' . $userId, 'order-status-changed', ['orderId' => $orderId, 'status' => 'delivered']);
    }

    // Redirect to dashboard with success message
    header("Location: ../../public/driver.php?page=dashboard&completed=1");
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
