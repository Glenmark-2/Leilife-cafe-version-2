<?php

class User {
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $password;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->first_name = $data['first_name'] ?? null;
        $this->last_name = $data['last_name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->phone_number = $data['phone_number'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? 'customer'; // Default role
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function updateUserPersonalInfo($db, $id, $firstName, $lastName, $phone) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ? WHERE id = ?");
        return $stmt->execute([$firstName, $lastName, $phone, $id]);
    }
}
