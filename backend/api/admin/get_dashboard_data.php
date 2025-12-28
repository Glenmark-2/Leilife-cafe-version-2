<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/DashboardService.php';

$database = new Database();
$db = $database->getConnection();

$dashboardService = new DashboardService($db);
$filter = $_GET['filter'] ?? 'date_desc';

try {
    $data = $dashboardService->getDashboardData($filter);
    error_log("Dashboard Data: " . json_encode($data));
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
