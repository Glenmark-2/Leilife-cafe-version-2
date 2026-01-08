<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../services/AnalyticsService.php';

$database = new Database();
$db = $database->getConnection();

$analyticsService = new AnalyticsService($db);

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate = $_GET['toDate'] ?? date('Y-m-d');

try {
    $data = $analyticsService->getAnalyticsData($fromDate, $toDate);
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
