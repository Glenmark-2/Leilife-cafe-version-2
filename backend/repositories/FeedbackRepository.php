<?php

class FeedbackRepository
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($orderId, $userId, $rating, $comment, $sentiment = null)
    {
        $query = "INSERT INTO order_feedbacks (order_id, user_id, rating, comment, sentiment) 
                  VALUES (:order_id, :user_id, :rating, :comment, :sentiment)
                  ON DUPLICATE KEY UPDATE rating = :rating2, comment = :comment2, sentiment = :sentiment2";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':sentiment', $sentiment);
        $stmt->bindParam(':rating2', $rating);
        $stmt->bindParam(':comment2', $comment);
        $stmt->bindParam(':sentiment2', $sentiment);

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

    public function getAllFeedbacks()
    {
        $query = "SELECT f.*, u.first_name, u.last_name, u.profile_photo, o.order_number 
                  FROM order_feedbacks f
                  JOIN users u ON f.user_id = u.id
                  JOIN orders o ON f.order_id = o.id
                  ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
