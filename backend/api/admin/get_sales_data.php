<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';

$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);

// Get filters from GET request
$filters = [
    'status' => $_GET['status'] ?? 'All',
    'payment' => $_GET['payment'] ?? 'All',
    'fromDate' => $_GET['fromDate'] ?? null,
    'toDate' => $_GET['toDate'] ?? null
];

// Debug logging
error_log("Sales Data Request Filters: " . print_r($filters, true));

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    $orders = $orderRepo->getSalesOrders($filters, $limit, $offset);
    $totalOrders = $orderRepo->countSalesOrders($filters);
    $totalPages = ceil($totalOrders / $limit);

    echo json_encode([
        "status" => "success",
        "data" => $orders,
        "pagination" => [
            "current_page" => $page,
            "total_pages" => $totalPages,
            "total_records" => $totalOrders,
            "limit" => $limit
        ]
    ]);
} catch (Throwable $e) {
    error_log("Sales Data Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
