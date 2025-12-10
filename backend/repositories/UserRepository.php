<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
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

    // Implement this method to find a user by email
    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new User($row);
        }
        return null; // Return null if user not found
    }
}
