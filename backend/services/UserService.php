<?php
require_once __DIR__ . '/../models/User.php';

class UserService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function updateProfile($userId, $data)
    {
        // Business Logic / Validation
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['phone_number'])) {
            return ['success' => false, 'message' => 'Names and phone number cannot be empty.'];
        }

        // Mobile Number Validation
        $phone = $data['phone_number'];
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Mobile number must be 11 digits and start with 09.'];
        }

        // Call the static method you added to User.php
        $success = User::updateUserPersonalInfo(
            $this->db,
            $userId,
            $data['first_name'],
            $data['last_name'],
            $data['phone_number']
        );

        if ($success) {
            return ['success' => true, 'message' => 'Profile updated successfully!'];
        }

        return ['success' => false, 'message' => 'Failed to update database.'];
    }

    public function updateAddress($userId, $data)
    {
        // Validation
        if (empty($data['street']) || empty($data['barangay']) || empty($data['latitude']) || empty($data['longitude'])) {
            return ['success' => false, 'message' => 'Street, Barangay, and Location Pin are required.'];
        }

        // Hardcoded constraints check (double check server side)
        $city = "Caloocan City";
        $province = "Metro Manila";
        $region = "NCR"; // Assuming NCR for Metro Manila

        $success = User::updateUserAddress(
            $this->db,
            $userId,
            $data['street'],
            $data['barangay'],
            $city,
            $province,
            $region,
            $data['latitude'],
            $data['longitude']
        );

        if ($success) {
            return ['success' => true, 'message' => 'Address updated successfully!'];
        }
        return ['success' => false, 'message' => 'Failed to update address in database.'];
    }

    public function updatePassword($userId, $data)
    {
        require_once __DIR__ . '/../repositories/UserRepository.php';
        $userRepo = new UserRepository($this->db);

        $user = $userRepo->findById($userId);
        if (!$user) return ['success' => false, 'message' => 'User not found'];

        // If user already has a password, we must verify the current one
        if (!empty($user->password)) {
            if (empty($data['current_password'])) {
                return ['success' => false, 'message' => 'Current password is required'];
            }
            if (!password_verify($data['current_password'], $user->password)) {
                return ['success' => false, 'message' => 'Incorrect current password'];
            }
        }

        // Validate new password
        if (empty($data['new_password'])) {
            return ['success' => false, 'message' => 'New password cannot be empty'];
        }
        if (strlen($data['new_password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        // Hash and update
        $newHash = password_hash($data['new_password'], PASSWORD_DEFAULT);

        if ($userRepo->updatePassword($userId, $newHash)) {
            return ['success' => true, 'message' => 'Password updated successfully'];
        }

        return ['success' => false, 'message' => 'Database error updating password'];
    }
}
