<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/FeedbackRepository.php';

$database = new Database();
$db = $database->getConnection();

$feedbackRepo = new FeedbackRepository($db);

try {
    $feedbacks = $feedbackRepo->getAllFeedbacks();
    echo json_encode([
        "success" => true,
        "feedbacks" => $feedbacks
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
