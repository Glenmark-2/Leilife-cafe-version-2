<?php

require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../helpers/SessionManager.php';

class OrderController {
    private $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function placeOrder() {
        // Enforce Authentication
        SessionManager::startSession();
        if (!SessionManager::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $userId = SessionManager::get('user_id');
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        $result = $this->orderService->placeOrder($userId, $data);
        
        if ($result['success']) {
            http_response_code(201); // Created
        } else {
            http_response_code(400); // Bad Request
        }
        
        echo json_encode($result);
    }

    public function getUserOrders() {
        SessionManager::startSession();
        
        $userId = null;
        if (SessionManager::isLoggedIn()) {
             $userId = SessionManager::get('user_id');
        } elseif (isset($_GET['user_id'])) {
             $userId = $_GET['user_id'];
        }

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $result = $this->orderService->getUserOrders($userId);
        
        echo json_encode($result);
    }
    public function cancelOrder() {
        SessionManager::startSession();
        if (!SessionManager::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $userId = SessionManager::get('user_id');
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
            return;
        }

        $result = $this->orderService->cancelOrder($userId, $orderId);
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }
}
