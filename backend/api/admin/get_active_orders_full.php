<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';

$database = new Database();
$db = $database->getConnection();
$orderRepo = new OrderRepository($db);

try {
    // Fetch all orders that are not "finished" in the sense of being ancient history, 
    // but For a real dashboard, we'll fetch the last 50 orders total to allow filtering by any status.
    
    $query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name 
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              ORDER BY o.created_at DESC
              LIMIT 50";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($orders as &$order) {
        // Fetch items for each order using existing OrderRepository method
        $items = $orderRepo->getOrderItems($order['id']);
        
        // Convert item objects to arrays for JSON response if they are objects
        $order['items'] = array_map(function($item) {
            return (array)$item;
        }, $items);
    }
    
    echo json_encode([
        "status" => "success", 
        "data" => $orders
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
