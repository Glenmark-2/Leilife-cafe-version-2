<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Product.php';

class ProductRepository {
    private $conn;
    private $table_products = "products";
    private $table_categories = "categories";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getFullMenuData() {
        // Query to get plain list of all items with their hierarchy
        // Only fetching available items for now
        $query = "
            SELECT 
                parent.name AS main_category,
                c.name AS sub_category,
                p.product_id,
                p.name AS product_name,
                p.description,
                p.price,
                p.image_path
            FROM " . $this->table_products . " p
            JOIN " . $this->table_categories . " c ON p.category_id = c.category_id
            JOIN " . $this->table_categories . " parent ON c.parent_id = parent.category_id
            WHERE p.is_available = 1
            ORDER BY parent.category_id, c.category_id, p.name ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->table_products . " WHERE product_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addFavorite($user_id, $product_id) {
        $query = "INSERT INTO favorites (user_id, product_id) VALUES (:user_id, :product_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Handle duplicate entry gracefully if needed, or let controller handle it
            return false; 
        }
    }

    public function removeFavorite($user_id, $product_id) {
        $query = "DELETE FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    public function isFavorite($user_id, $product_id) {
        $query = "SELECT favorite_id FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getFavoritesByUserId($user_id) {
        $query = "
            SELECT 
                p.product_id,
                p.name AS product_name,
                p.price,
                p.image_path
            FROM favorites f
            JOIN products p ON f.product_id = p.product_id
            WHERE f.user_id = :user_id
            ORDER BY f.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
