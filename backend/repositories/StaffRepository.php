<?php
require_once __DIR__ . '/../config/Database.php';

class StaffRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllStaffs()
    {
        $query = "SELECT s.*, a.username as admin_user, a.email as admin_mail, d.username as driver_user, d.email as driver_mail 
                  FROM staffs s 
                  LEFT JOIN admins a ON s.staff_id = a.staff_id 
                  LEFT JOIN drivers d ON s.staff_id = d.staff_id 
                  WHERE s.is_archived = 0 
                  ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArchivedStaffs()
    {
        $query = "SELECT s.*, a.username as admin_user, a.email as admin_mail, d.username as driver_user, d.email as driver_mail 
                  FROM staffs s 
                  LEFT JOIN admins a ON s.staff_id = a.staff_id 
                  LEFT JOIN drivers d ON s.staff_id = d.staff_id 
                  WHERE s.is_archived = 1 
                  ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateArchivedStatus($id, $isArchived)
    {
        $query = "UPDATE staffs SET is_archived = :is_archived WHERE staff_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':is_archived', $isArchived);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function createStaff($data)
    {
        $query = "INSERT INTO staffs (full_name, role, position, shift, status, photo_path) 
                  VALUES (:full_name, :role, :position, :shift, :status, :photo_path)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':shift', $data['shift']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':photo_path', $data['photo_path']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function createAdmin($data)
    {
        $query = "INSERT INTO admins (staff_id, username, email, password) VALUES (:staff_id, :username, :email, :password)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        return $stmt->execute();
    }

    public function getStaffById($id)
    {
        $query = "SELECT s.*, a.username as admin_user, a.email as admin_mail, d.username as driver_user, d.email as driver_mail 
                  FROM staffs s 
                  LEFT JOIN admins a ON s.staff_id = a.staff_id 
                  LEFT JOIN drivers d ON s.staff_id = d.staff_id 
                  WHERE s.staff_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStaff($data)
    {
        $query = "UPDATE staffs SET full_name = :full_name, role = :role, position = :position, 
                  shift = :shift, status = :status";
        if (isset($data['photo_path'])) {
            $query .= ", photo_path = :photo_path";
        }
        $query .= " WHERE staff_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':shift', $data['shift']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $data['staff_id']);
        if (isset($data['photo_path'])) {
            $stmt->bindParam(':photo_path', $data['photo_path']);
        }
        return $stmt->execute();
    }

    public function updateAdmin($data)
    {
        $query = "UPDATE admins SET username = :username, email = :email";
        if (isset($data['password'])) {
            $query .= ", password = :password";
        }
        $query .= " WHERE staff_id = :staff_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        if (isset($data['password'])) {
            $stmt->bindParam(':password', $data['password']);
        }
        return $stmt->execute();
    }

    public function updateDriver($data)
    {
        $query = "UPDATE drivers SET username = :username, email = :email";
        if (isset($data['password'])) {
            $query .= ", password = :password";
        }
        $query .= " WHERE staff_id = :staff_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        if (isset($data['password'])) {
            $stmt->bindParam(':password', $data['password']);
        }
        return $stmt->execute();
    }
}
