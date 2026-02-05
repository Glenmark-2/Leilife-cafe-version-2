<?php

require_once __DIR__ . '/../services/AuthService.php';

class AuthController
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Handle the register request
    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            // Fallback for form-data if JSON fails or if it's sent differently
            $data = $_POST;
        }

        $result = $this->authService->register($data);
        echo json_encode($result);
    }

    // Handle the login request
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            $data = $_POST;
        }

        if (isset($data['email']) && isset($data['password'])) {
            $result = $this->authService->login($data['email'], $data['password']);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        }
    }

    // Handle profile request
    public function profile($userId)
    {
        $result = $this->authService->getUserProfile($userId);
        echo json_encode($result);
    }
}
