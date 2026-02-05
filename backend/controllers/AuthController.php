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

    public function forgotPasswordRequest()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        if (empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email is required.']);
            return;
        }
        $result = $this->authService->requestPasswordReset($data['email']);
        echo json_encode($result);
    }

    public function forgotPasswordVerify()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        if (empty($data['email']) || empty($data['otp'])) {
            echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
            return;
        }
        $result = $this->authService->verifyResetOTP($data['email'], $data['otp']);
        echo json_encode($result);
    }

    public function forgotPasswordReset()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        if (empty($data['email']) || empty($data['otp']) || empty($data['new_password'])) {
            echo json_encode(['success' => false, 'message' => 'Email, OTP, and new password are required.']);
            return;
        }
        $result = $this->authService->resetPassword($data['email'], $data['otp'], $data['new_password']);
        echo json_encode($result);
    }
}
