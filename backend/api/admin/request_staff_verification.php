<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/PasswordResetRepository.php';
require_once __DIR__ . '/../../services/MailService.php';

try {
    date_default_timezone_set('Asia/Manila');

    $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;

    if (empty($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    $email = $data['email'];

    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Store in password_resets table (reusing for staff verification)
    $database = new Database();
    $db = $database->getConnection();
    $resetRepo = new PasswordResetRepository($db);

    // Cleanup expired and delete any existing for this email
    $resetRepo->cleanupExpiredResets(date('Y-m-d H:i:s'));
    $resetRepo->deleteResetsForEmail($email);

    if ($resetRepo->createReset($email, $otp, $expiresAt)) {
        // Send email
        $mailService = new MailService();
        if ($mailService->sendStaffVerificationOTP($email, $otp)) {
            echo json_encode(['success' => true, 'message' => 'Verification code sent to email.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create verification request.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
