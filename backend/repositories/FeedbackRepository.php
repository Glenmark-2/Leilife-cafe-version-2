<?php

class FeedbackRepository
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($orderId, $userId, $rating, $comment)
    {
        $query = "INSERT INTO order_feedbacks (order_id, user_id, rating, comment) 
                  VALUES (:order_id, :user_id, :rating, :comment)
                  ON DUPLICATE KEY UPDATE rating = :rating2, comment = :comment2";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':rating2', $rating);
        $stmt->bindParam(':comment2', $comment);

        return $stmt->execute();
    }

    public function getByOrderId($orderId)
    {
        $query = "SELECT * FROM order_feedbacks WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
