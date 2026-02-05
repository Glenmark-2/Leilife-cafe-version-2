<?php

class PasswordResetRepository
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createReset($email, $otp, $expiresAt)
    {
        $query = "INSERT INTO password_resets (email, otp_code, expires_at) VALUES (:email, :otp, :expires_at)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":otp", $otp);
        $stmt->bindParam(":expires_at", $expiresAt);
        return $stmt->execute();
    }

    public function findValidReset($email, $otp)
    {
        $query = "SELECT * FROM password_resets WHERE email = :email AND otp_code = :otp AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":otp", $otp);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function deleteResetsForEmail($email)
    {
        $query = "DELETE FROM password_resets WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    public function cleanupExpiredResets($now)
    {
        $query = "DELETE FROM password_resets WHERE expires_at < :now";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":now", $now);
        return $stmt->execute();
    }
}
