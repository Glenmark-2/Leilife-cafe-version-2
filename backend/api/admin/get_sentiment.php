<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/FeedbackRepository.php';
require_once __DIR__ . '/../../services/SentimentService.php';

$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT sentiment, COUNT(*) as count FROM order_feedbacks WHERE sentiment IS NOT NULL";
    if ($fromDate && $toDate) {
        $query .= " AND DATE(created_at) BETWEEN :fromDate AND :toDate";
    }
    $query .= " GROUP BY sentiment";

    $stmt = $db->prepare($query);
    if ($fromDate && $toDate) {
        $stmt->bindParam(':fromDate', $fromDate);
        $stmt->bindParam(':toDate', $toDate);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $counts = [
        'Positive' => 0,
        'Neutral' => 0,
        'Negative' => 0
    ];

    foreach ($results as $row) {
        if (isset($counts[$row['sentiment']])) {
            $counts[$row['sentiment']] = (int)$row['count'];
        }
    }

    echo json_encode(["status" => "success", "data" => $counts]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
