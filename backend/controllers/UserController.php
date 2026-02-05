<?php
require_once __DIR__ . '/../services/UserService.php';

class UserController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function update()
    {
        // Start session to get the logged-in user's ID
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Handle both JSON and Form-Data (to match your AuthController style)
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        $result = $this->userService->updateProfile($userId, $data);
        echo json_encode($result);
    }

    public function updateAddress()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $result = $this->userService->updateAddress($userId, $data);
        echo json_encode($result);
    }

    public function updatePassword()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        // Mobile fallback: get userId from data if session is empty
        $userId = $_SESSION['user_id'] ?? ($data['user_id'] ?? null);

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or User ID missing']);
            return;
        }

        $result = $this->userService->updatePassword($userId, $data);
        echo json_encode($result);
    }
}
