<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->getConnection();

try {
    $query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                     (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.delivery_method = 'delivery' 
              AND o.status = 'out_for_delivery' 
              AND o.assigned_driver_id IS NULL 
              ORDER BY o.created_at ASC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $availableOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'orders' => $availableOrders
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
