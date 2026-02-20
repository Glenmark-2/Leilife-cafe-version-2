<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/FeedbackRepository.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../helpers/SessionManager.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$userId = SessionManager::get('user_id');
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->order_id) && !empty($data->rating)) {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $orderRepo = new OrderRepository($db);
        $order = $orderRepo->findById($data->order_id);

        if (!$order || $order->user_id != $userId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Forbidden"]);
            exit();
        }

        $sentiment = null;
        if (!empty($data->comment)) {
            require_once __DIR__ . '/../services/SentimentService.php';
            $sentimentService = new SentimentService();
            $sentiment = $sentimentService->analyze($data->comment);
        }

        $feedbackRepo = new FeedbackRepository($db);
        $result = $feedbackRepo->create(
            $data->order_id,
            $userId,
            $data->rating,
            $data->comment ?? null,
            $sentiment
        );

        if ($result) {
            // REAL-TIME: Notify Admin Analytics
            require_once __DIR__ . '/../services/RealtimeService.php';
            require_once __DIR__ . '/../helpers/NotificationHelper.php';
            
            RealtimeService::trigger('admin-analytics', 'feedback-submitted', [
                'order_id' => $data->order_id,
                'rating' => $data->rating,
                'sentiment' => $sentiment
            ]);

            // PUSH NOTIFICATION: Notify Admins
            try {
                $adminTokens = NotificationHelper::getTokensByRole('admin', $db);
                if (!empty($adminTokens)) {
                    NotificationHelper::sendPushToMany(
                        $adminTokens,
                        "New Customer Review ⭐",
                        "A new {$data->rating}-star review has been submitted for Order #{$order->order_number}.",
                        ["orderId" => $data->order_id, "type" => "feedback"]
                    );
                }
            } catch (Exception $e) {
                error_log("Feedback Admin Notif Error: " . $e->getMessage());
            }

            echo json_encode(["success" => true, "message" => "Feedback submitted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to submit feedback"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete data"]);
}
