<?php

require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../../vendor/autoload.php'; 
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/UserRegistrationRepository.php';
require_once __DIR__ . '/../repositories/PasswordResetRepository.php';

class AuthService
{
    private $userRepository;
    private $userRegistrationRepository;
    private $passwordResetRepository;

    public function __construct(
        UserRepository $userRepository,
        ?UserRegistrationRepository $userRegistrationRepository = null,
        ?PasswordResetRepository $passwordResetRepository = null
    ) {
        $this->userRepository = $userRepository;
        $this->userRegistrationRepository = $userRegistrationRepository;
        $this->passwordResetRepository = $passwordResetRepository;
    }

    public function requestPasswordReset($email)
    {
        date_default_timezone_set('Asia/Manila');
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            // For security, don't reveal if email exists or not, but the user specifically asked to check.
            // "ask for email first then check if its in the db"
            return ['success' => false, 'message' => 'Email not found in our records.'];
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Clear previous resets for this email and global cleanup of expired ones
        $this->passwordResetRepository->cleanupExpiredResets(date('Y-m-d H:i:s'));
        $this->passwordResetRepository->deleteResetsForEmail($email);

        if ($this->passwordResetRepository->createReset($email, $otp, $expiresAt)) {
            require_once __DIR__ . '/MailService.php';
            $mailService = new MailService();
            if ($mailService->sendPasswordResetOTP($email, $otp)) {
                return ['success' => true, 'message' => 'OTP sent to your email.'];
            }
        }

        return ['success' => false, 'message' => 'Failed to send OTP. Please try again.'];
    }

    public function verifyResetOTP($email, $otp)
    {
        date_default_timezone_set('Asia/Manila');
        $reset = $this->passwordResetRepository->findValidReset($email, $otp);
        if ($reset) {
            return ['success' => true, 'message' => 'OTP verified.'];
        }
        return ['success' => false, 'message' => 'Invalid or expired OTP.'];
    }

    public function resetPassword($email, $otp, $newPassword)
    {
        date_default_timezone_set('Asia/Manila');
        $reset = $this->passwordResetRepository->findValidReset($email, $otp);
        if (!$reset) {
            return ['success' => false, 'message' => 'Verification failed or session expired.'];
        }

        $user = $this->userRepository->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'User not found.'];

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        if ($this->userRepository->updatePassword($user->id, $hashedPassword)) {
            $this->passwordResetRepository->deleteResetsForEmail($email);
            return ['success' => true, 'message' => 'Password reset successfully!'];
        }

        return ['success' => false, 'message' => 'Failed to reset password.'];
    }

    // Implement the register logic
    public function register($data)
    {
        // Validate required fields
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            return ['success' => false, 'message' => 'Please fill all required fields.'];
        }

        // Check if user already exists in main User table
        if ($this->userRepository->findByEmail($data['email']) !== null) {
            return ['success' => false, 'message' => 'Email is already taken by a verified account.'];
        }

        // Check if user already exists in Pending Registrations
        if ($this->userRegistrationRepository && $this->userRegistrationRepository->findByEmail($data['email']) !== null) {
            // Allow resending verification if the user exists but isn't verified yet?
            // For now, let's just stick to the error to avoid complexity, but clarify the message.
            return ['success' => false, 'message' => 'A verification email has already been sent. Please check your inbox.'];
        }

        // Password matching check
        if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Generate Token and OTP
        $token = bin2hex(random_bytes(32)); // 64 chars
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Create UserRegistration object
        $registration = new UserRegistration();
        $registration->first_name = $data['first_name'];
        $registration->last_name = $data['last_name'];
        $registration->email = $data['email'];
        $registration->phone_number = $data['phone_number'] ?? '';
        $registration->password = $hashed_password;
        $registration->verification_token = $token;
        $registration->otp_code = $otpCode;
        $registration->token_expires_at = $expiresAt;

        // Save to UserRegistrations DB
        if ($this->userRegistrationRepository->create($registration)) {
            // Send Verification Email
            require_once __DIR__ . '/MailService.php';
            $mailService = new MailService();
            $source = $data['source'] ?? 'web';
            $emailSent = $mailService->sendVerification($data['email'], $token, $otpCode, $source);

            if (!$emailSent) {
                // If email fails, do we fail registration? 
                // Usually yes, or we tell them "Registration successful but email failed".
                // For now, let's warn but keep data.
                return [
                    'success' => true,
                    'message' => 'Registration successful, but we failed to send the email. Please contact support.',
                    'verification_required' => true
                ];
            }

            return [
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'verification_required' => true
            ];
        }

        return ['success' => false, 'message' => 'Unable to register user.'];
    }

    public function verifyEmail($token)
    {
        $registration = $this->userRegistrationRepository->findByToken($token);

        if (!$registration) {
            return ['success' => false, 'message' => 'Invalid or expired verification token.'];
        }

        return $this->finalizeRegistration($registration);
    }

    public function verifyOTP($email, $otp)
    {
        $registration = $this->userRegistrationRepository->findByOTP($email, $otp);

        if (!$registration) {
            return ['success' => false, 'message' => 'Invalid or expired verification code.'];
        }

        return $this->finalizeRegistration($registration);
    }

    private function finalizeRegistration($registration)
    {
        if (strtotime($registration->token_expires_at) < time()) {
            return ['success' => false, 'message' => 'Verification has expired.'];
        }

        // Move to Users table
        $user = new User();
        $user->first_name = $registration->first_name;
        $user->last_name = $registration->last_name;
        $user->email = $registration->email;
        $user->phone_number = $registration->phone_number;
        $user->password = $registration->password; // Already hashed
        $user->role = 'customer';

        if ($this->userRepository->create($user)) {
            // Delete from temporary table
            $this->userRegistrationRepository->delete($registration->id);
            return ['success' => true, 'message' => 'Account verified successfully. You can now login.'];
        }

        return ['success' => false, 'message' => 'Failed to verify account.'];
    }

    // Implement the login logic
    public function login($identifier, $password)
    {
        // Ensure SessionManager is loaded.
        require_once __DIR__ . '/../helpers/SessionManager.php';

        $user = $this->userRepository->findByEmail($identifier);

        if ($user && password_verify($password, $user->password)) {
            // Remove password from returned object for security
            $user->password = null;

            // Start Session and save User Data
            SessionManager::set('user_id', $user->id);
            SessionManager::set('user_role', $user->role);
            SessionManager::set('user_name', trim($user->first_name . ' ' . ($user->last_name ?? '')));
            SessionManager::set('user_email', $user->email);

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Invalid email/username or password.'];
    }

    public function loginWithGoogle($idToken)
    {
        if (!class_exists('Google_Client')) {
            return ['success' => false, 'message' => 'Google Login library not installed on server.'];
        }

        // Initialize Google Client
        $clientId = getenv('GOOGLE_CLIENT_ID');
        if (!$clientId) {
            return ['success' => false, 'message' => 'GOOGLE_CLIENT_ID not configured on server.'];
        }

        $client = new Google_Client(['client_id' => $clientId]);

        try {
            $payload = $client->verifyIdToken($idToken);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Invalid Google Token: ' . $e->getMessage()];
        }

        if (!$payload) {
            return ['success' => false, 'message' => 'Invalid Google Token.'];
        }

        $email = $payload['email'];
        $fname = $payload['given_name'] ?? 'Google';
        $lname = $payload['family_name'] ?? 'User';

        // Check if user exists
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            // Register new user automatically
            $user = new User();
            $user->first_name = $fname;
            $user->last_name = $lname;
            $user->email = $email;
            $user->phone_number = ''; // Optional
            // No password for Google users
            $user->password = null;
            $user->role = 'customer';

            if (!$this->userRepository->create($user)) {
                return ['success' => false, 'message' => 'Failed to create account with Google.'];
            }
            // Fetch the ID of the newly created user (needs modification in Repository to get last ID or re-fetch)
            $user = $this->userRepository->findByEmail($email);
        }

        // Login (Start Session)
        require_once __DIR__ . '/../helpers/SessionManager.php';
        SessionManager::set('user_id', $user->id);
        SessionManager::set('user_role', $user->role);
        SessionManager::set('user_name', $user->first_name . ' ' . $user->last_name);
        SessionManager::set('user_email', $user->email);

        // Remove password from returned object for security
        $user->password = null;

        return ['success' => true, 'user' => $user];
    }

    public function resendOTP($email)
    {
        $registration = $this->userRegistrationRepository->findByEmail($email);

        if (!$registration) {
            return ['success' => false, 'message' => 'No pending registration found for this email.'];
        }

        // Generate new OTP
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $registration->otp_code = $otpCode;
        $registration->token_expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        if ($this->updateRegistration($registration)) {
            require_once __DIR__ . '/MailService.php';
            $mailService = new MailService();
            $emailSent = $mailService->sendVerification($email, $registration->verification_token, $otpCode);

            if ($emailSent) {
                return ['success' => true, 'message' => 'New verification code sent.'];
            }
        }

        return ['success' => false, 'message' => 'Failed to resend code.'];
    }

    private function updateRegistration($reg)
    {
        $query = "UPDATE user_registrations SET otp_code = :otp, token_expires_at = :expires WHERE id = :id";
        $stmt = $this->userRegistrationRepository->getConnection()->prepare($query);
        $stmt->bindParam(":otp", $reg->otp_code);
        $stmt->bindParam(":expires", $reg->token_expires_at);
        $stmt->bindParam(":id", $reg->id);
        return $stmt->execute();
    }

    public function getUserProfile($userId, $role = null)
    {
        $user = $this->userRepository->findById($userId, $role);
        if ($user) {
            $user->has_password = !empty($user->password);
            $user->password = null; // Don't return the password
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'User not found.'];
    }
}
