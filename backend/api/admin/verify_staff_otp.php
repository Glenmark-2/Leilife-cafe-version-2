<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/PasswordResetRepository.php';

try {
    date_default_timezone_set('Asia/Manila');

    $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;

    if (empty($data['email']) || empty($data['otp'])) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $resetRepo = new PasswordResetRepository($db);

    $reset = $resetRepo->findValidReset($data['email'], $data['otp']);

    if ($reset) {
        // OTP is valid - delete it so it can't be reused
        $resetRepo->deleteResetsForEmail($data['email']);

        // Mark this email as verified
        $insertQuery = "INSERT INTO verified_staff_emails (email) VALUES (:email) 
                        ON DUPLICATE KEY UPDATE verified_at = CURRENT_TIMESTAMP";
        $stmt = $db->prepare($insertQuery);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Email verified successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
