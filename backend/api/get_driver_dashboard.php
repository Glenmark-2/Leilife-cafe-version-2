<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/SessionManager.php';

$driverId = $_GET['driver_id'] ?? null;

if (!$driverId) {
    echo json_encode(['success' => false, 'message' => 'Driver ID is required']);
    exit;
}

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

    // Current Active Delivery (if any)
    $stmt = $db->prepare("SELECT o.*, u.first_name, u.last_name, u.phone_number as customer_phone,
                                 ua.latitude as customer_lat, ua.longitude as customer_lng
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          LEFT JOIN user_addresses ua ON o.user_id = ua.user_id
                          WHERE o.assigned_driver_id = :driver_id AND o.status = 'out_for_delivery'
                          LIMIT 1");
    $stmt->execute(['driver_id' => $driverId]);
    $activeDelivery = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'delivered_today' => $deliveredToday,
            'status' => 'Active'
        ],
        'recent_activities' => $recentActivities,
        'active_delivery' => $activeDelivery
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
