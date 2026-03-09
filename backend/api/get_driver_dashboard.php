<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
ini_set('display_errors', '0');
require_once __DIR__ . '/../helpers/SessionManager.php';

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/SessionManager.php';

SessionManager::startSession();
$sessionUserId = SessionManager::get('user_id');
$sessionRole = strtolower((string)(SessionManager::get('user_role') ?? ''));
$requestedDriverId = $_GET['driver_id'] ?? null;

if (!$sessionUserId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!in_array($sessionRole, ['driver', 'rider'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: driver access required']);
    exit;
}

if ($requestedDriverId !== null && strval($requestedDriverId) !== strval($sessionUserId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: driver mismatch']);
    exit;
}
$driverId = $sessionUserId;

$db = (new Database())->getConnection();

try {
    // Recent Activity (Last 24 Hours - Completed Only)
    $stmt = $db->prepare("SELECT o.*, u.first_name, u.last_name 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.assigned_driver_id = :driver_id 
                          AND o.status = 'delivered'
                          AND o.updated_at >= NOW() - INTERVAL 1 DAY
                          ORDER BY o.updated_at DESC LIMIT 5");
    $stmt->execute(['driver_id' => $driverId]);
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter Stats for Today
    $stmt = $db->prepare("SELECT COUNT(*) FROM orders 
                          WHERE assigned_driver_id = :driver_id 
                          AND status = 'delivered' 
                          AND DATE(updated_at) = CURDATE()");
    $stmt->execute(['driver_id' => $driverId]);
    $deliveredToday = (int)$stmt->fetchColumn();

    // Current Active Deliveries (All orders out for delivery assigned to this driver)
    $stmt = $db->prepare("SELECT o.*, u.first_name, u.last_name, u.phone_number as customer_phone,
                                 ua.latitude as customer_lat, ua.longitude as customer_lng
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          LEFT JOIN user_addresses ua ON o.user_id = ua.user_id
                          WHERE o.assigned_driver_id = :driver_id AND o.status = 'out_for_delivery'
                          ORDER BY o.updated_at ASC");
    $stmt->execute(['driver_id' => $driverId]);
    $activeDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'delivered_today' => $deliveredToday,
            'status' => 'Active'
        ],
        'recent_activities' => $recentActivities,
        'active_deliveries' => $activeDeliveries,
        'active_delivery' => !empty($activeDeliveries) ? $activeDeliveries[0] : null
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
