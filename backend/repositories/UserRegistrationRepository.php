<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/UserRegistration.php';

class UserRegistrationRepository {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function create(UserRegistration $reg) {
        $query = "INSERT INTO user_registrations 
                    (first_name, last_name, email, phone_number, password, verification_token, otp_code, token_expires_at)
                  VALUES 
                    (:first_name, :last_name, :email, :phone_number, :password, :token, :otp_code, :expires_at)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":first_name", $reg->first_name);
        $stmt->bindParam(":last_name", $reg->last_name);
        $stmt->bindParam(":email", $reg->email);
        $stmt->bindParam(":phone_number", $reg->phone_number);
        $stmt->bindParam(":password", $reg->password);
        $stmt->bindParam(":token", $reg->verification_token);
        $stmt->bindParam(":otp_code", $reg->otp_code);
        $stmt->bindParam(":expires_at", $reg->token_expires_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM user_registrations WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new UserRegistration($row);
        }
        return null;
    }
    
    // For future use
    public function findByToken($token) {
        $query = "SELECT * FROM user_registrations WHERE verification_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new UserRegistration($row);
        }
        return null;
    }

    public function findByOTP($email, $otp) {
        $query = "SELECT * FROM user_registrations WHERE email = :email AND otp_code = :otp LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":otp", $otp);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new UserRegistration($row);
        }
        return null;
    }

    public function delete($id) {
        $query = "DELETE FROM user_registrations WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
