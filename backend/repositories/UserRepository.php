<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Implement this method to find a user by ID
    public function findById($id) {
        $query = "SELECT u.*, 
                         ua.street, ua.barangay, ua.city, ua.province, ua.region, ua.latitude, ua.longitude 
                  FROM users u
                  LEFT JOIN user_addresses ua ON u.id = ua.user_id
                  WHERE u.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new User($row);
        }
        return null;
    }

    // TODO: Implement this method to insert a new user into the database
    // Implement this method to insert a new user into the database
    public function create(User $user) {
        $query = "INSERT INTO users (first_name, last_name, email, phone_number, password, role)
                  VALUES (:first_name, :last_name, :email, :phone_number, :password, :role)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":first_name", $user->first_name);
        $stmt->bindParam(":last_name", $user->last_name);
        $stmt->bindParam(":email", $user->email);
        $stmt->bindParam(":phone_number", $user->phone_number);
        $stmt->bindParam(":password", $user->password);
        $stmt->bindParam(":role", $user->role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Find a user by email or username across multiple tables (users, admins, drivers)
     */
    public function findByEmail($identifier) {
        // We use a UNION to search across all potential user tables.
        // For admins/drivers, we join with staffs to get names.
        $query = "
            SELECT id, email, password, role, first_name, last_name FROM (
                SELECT id, 
                       email COLLATE utf8mb4_unicode_ci as email, 
                       CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci as username, 
                       password COLLATE utf8mb4_unicode_ci as password, 
                       role COLLATE utf8mb4_unicode_ci as role, 
                       first_name COLLATE utf8mb4_unicode_ci as first_name, 
                       last_name COLLATE utf8mb4_unicode_ci as last_name 
                FROM users
                UNION ALL
                SELECT a.staff_id as id, 
                       a.email COLLATE utf8mb4_unicode_ci, 
                       a.username COLLATE utf8mb4_unicode_ci, 
                       a.password COLLATE utf8mb4_unicode_ci, 
                       'admin' COLLATE utf8mb4_unicode_ci as role, 
                       s.full_name COLLATE utf8mb4_unicode_ci as first_name, 
                       '' COLLATE utf8mb4_unicode_ci as last_name 
                FROM admins a JOIN staffs s ON a.staff_id = s.staff_id
                UNION ALL
                SELECT d.staff_id as id, 
                       d.email COLLATE utf8mb4_unicode_ci, 
                       d.username COLLATE utf8mb4_unicode_ci, 
                       d.password COLLATE utf8mb4_unicode_ci, 
                       'driver' COLLATE utf8mb4_unicode_ci as role, 
                       s.full_name COLLATE utf8mb4_unicode_ci as first_name, 
                       '' COLLATE utf8mb4_unicode_ci as last_name 
                FROM drivers d JOIN staffs s ON d.staff_id = s.staff_id
            ) AS all_users 
            WHERE email = :identifier OR username = :identifier 
            LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":identifier", $identifier);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new User($row);
        }
        return null;
    }
    public function updatePassword($userId, $newHash) {
        $query = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $newHash);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
}
