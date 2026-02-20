<?php

class User
{
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $password;
    public $role;
    public $profile_photo;
    public $push_token;
    public $created_at;
    public $updated_at;
    public $has_password; // Added for UI logic (e.g. Google users)

    // Address fields
    public $street;
    public $barangay;
    public $city;
    public $province;
    public $region;
    public $latitude;
    public $longitude;

    public function __construct($data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->first_name = $data['first_name'] ?? null;
        $this->last_name = $data['last_name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->phone_number = $data['phone_number'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? 'customer'; // Default role
        $this->profile_photo = $data['profile_photo'] ?? null;
        $this->push_token = $data['push_token'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;

        // Address initialization
        $this->street = $data['street'] ?? null;
        $this->barangay = $data['barangay'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->province = $data['province'] ?? null;
        $this->region = $data['region'] ?? null;
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
    }

    public static function updateUserPersonalInfo($db, $id, $firstName, $lastName, $phone)
    {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ? WHERE id = ?");
        return $stmt->execute([$firstName, $lastName, $phone, $id]);
    }

    public static function updateUserAddress($db, $user_id, $street, $barangay, $city, $province, $region, $lat, $lng)
    {
        $stmt = $db->prepare("SELECT user_id FROM user_addresses WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $sql = "UPDATE user_addresses SET street = ?, barangay = ?, city = ?, province = ?, region = ?, latitude = ?, longitude = ? WHERE user_id = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$street, $barangay, $city, $province, $region, $lat, $lng, $user_id]);
        } else {
            $sql = "INSERT INTO user_addresses (user_id, street, barangay, city, province, region, latitude, longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                street = VALUES(street),
                barangay = VALUES(barangay),
                city = VALUES(city),
                province = VALUES(province),
                region = VALUES(region),        
                latitude = VALUES(latitude),
                longitude = VALUES(longitude)";

            $stmt = $db->prepare($sql);
            return $stmt->execute([$user_id, $street, $barangay, $city, $province, $region, $lat, $lng]);
        }
    }

    public static function updateUserPassword($db, $user_id, $password) {}
}
