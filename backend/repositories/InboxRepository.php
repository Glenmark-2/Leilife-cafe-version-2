<?php
require_once __DIR__ . '/../config/Database.php';

class InboxRepository {
    private $conn;
    private $table_name = "inbox";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, email, subject, message) 
                  VALUES (:name, :email, :subject, :message)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':message', $data['message']);
        
        return $stmt->execute();
    }

    public function getAllMessages() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE inbox_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateArchivedStatus($id, $isArchived) {
        $query = "UPDATE " . $this->table_name . " SET is_archived = :is_archived WHERE inbox_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':is_archived', $isArchived);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
