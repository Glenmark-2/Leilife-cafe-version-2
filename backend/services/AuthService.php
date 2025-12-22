<?php

require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../../vendor/autoload.php'; 
require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService {
    private $userRepository;
    private $userRegistrationRepository;

    public function __construct(UserRepository $userRepository, UserRegistrationRepository $userRegistrationRepository = null) {
        $this->userRepository = $userRepository;
        // Optional for now until fully wired, but essential for the new flow
        $this->userRegistrationRepository = $userRegistrationRepository;
    }

    // Implement the register logic
    public function register($data) {
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

        // Generate Token
        $token = bin2hex(random_bytes(32)); // 64 chars
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Create UserRegistration object
        $registration = new UserRegistration();
        $registration->first_name = $data['first_name'];
        $registration->last_name = $data['last_name'];
        $registration->email = $data['email'];
        $registration->phone_number = $data['phone_number'] ?? '';
        $registration->password = $hashed_password;
        $registration->verification_token = $token;
        $registration->token_expires_at = $expiresAt;

        // Save to UserRegistrations DB
        if ($this->userRegistrationRepository->create($registration)) {
            // Send Verification Email
            require_once __DIR__ . '/MailService.php';
            $mailService = new MailService();
            $emailSent = $mailService->sendVerification($data['email'], $token);
            
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

    public function verifyEmail($token) {
        $registration = $this->userRegistrationRepository->findByToken($token);

        if (!$registration) {
            return ['success' => false, 'message' => 'Invalid or expired verification token.'];
        }

        if (strtotime($registration->token_expires_at) < time()) {
             return ['success' => false, 'message' => 'Verification token has expired.'];
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
            return ['success' => true, 'message' => 'Email verified successfully. You can now login.'];
        }

        return ['success' => false, 'message' => 'Failed to verify account.'];
    }

    // Implement the login logic
    public function login($email, $password) {
        // Ensure SessionManager is loaded. It's better to require it at top of file, 
        // but for now we will assume it is available or we add require_once here if needed.
        require_once __DIR__ . '/../helpers/SessionManager.php';

        $user = $this->userRepository->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            // Remove password from returned object for security
            $user->password = null;

            // Start Session and save User Data
            SessionManager::set('user_id', $user->id);
            SessionManager::set('user_role', $user->role);
            SessionManager::set('user_name', $user->first_name . ' ' . $user->last_name);
            SessionManager::set('user_email', $user->email);

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    public function loginWithGoogle($idToken) {
        // Initialize Google Client
        $client = new Google_Client(['client_id' => getenv('GOOGLE_CLIENT_ID')]);

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
}
