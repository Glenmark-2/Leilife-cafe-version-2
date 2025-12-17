<?php
require_once __DIR__ . '/../models/User.php';

class UserService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function updateProfile($userId, $data) {
        // Business Logic / Validation
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['phone_number'])) {
            return ['success' => false, 'message' => 'Names and phone number cannot be empty.'];
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

    public function updateAddress($userId, $data) {
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
}