<?php

require_once __DIR__ . '/../config/Database.php';

class TransactionRepository
{
    private $conn;
    private $table = "transactions";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  (order_id, transaction_type, transaction_reference, amount, status, description, payment_method, raw_response) 
                  VALUES (:order_id, :transaction_type, :transaction_reference, :amount, :status, :description, :payment_method, :raw_response)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':order_id', $data['order_id']);
        $stmt->bindParam(':transaction_type', $data['transaction_type']);
        $stmt->bindParam(':transaction_reference', $data['transaction_reference']);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        
        $rawResponse = isset($data['raw_response']) ? (is_string($data['raw_response']) ? $data['raw_response'] : json_encode($data['raw_response'])) : null;
        $stmt->bindParam(':raw_response', $rawResponse);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function findByOrderId($orderId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE order_id = :order_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
