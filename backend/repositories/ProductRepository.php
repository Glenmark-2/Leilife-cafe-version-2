<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Product.php';

class ProductRepository
{
    private $conn;
    private $table_products = "products";
    private $table_categories = "categories";

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getFullMenuData()
    {
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
                p.image_path,
                p.is_available
            FROM " . $this->table_products . " p
            JOIN " . $this->table_categories . " c ON p.category_id = c.category_id
            JOIN " . $this->table_categories . " parent ON c.parent_id = parent.category_id
            WHERE p.is_archived = 0
            ORDER BY parent.category_id, c.category_id, p.name ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $query = "SELECT * FROM " . $this->table_products . " WHERE product_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addFavorite($user_id, $product_id)
    {
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

    public function removeFavorite($user_id, $product_id)
    {
        $query = "DELETE FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    public function isFavorite($user_id, $product_id)
    {
        $query = "SELECT favorite_id FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getFavoritesByUserId($user_id)
    {
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

    public function getAllProductsAdmin($filter = [])
    {
        $query = "
            SELECT 
                p.*, 
                c.name as category_name,
                c.parent_id as parent_category_id,
                parent.name as parent_category_name
            FROM " . $this->table_products . " p
            LEFT JOIN " . $this->table_categories . " c ON p.category_id = c.category_id
            LEFT JOIN " . $this->table_categories . " parent ON c.parent_id = parent.category_id
            WHERE 1=1
        ";

        $params = [];

        if (isset($filter['is_archived']) && $filter['is_archived'] !== 'all') {
            $query .= " AND p.is_archived = :is_archived";
            $params[':is_archived'] = $filter['is_archived'];
        }

        if (!empty($filter['category_id'])) {
            $query .= " AND (p.category_id = :category_id OR c.parent_id = :category_id)";
            $params[':category_id'] = $filter['category_id'];
        }

        if (!empty($filter['search'])) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filter['search'] . '%';
        }

        $query .= " ORDER BY p.product_id DESC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories()
    {
        $query = "SELECT * FROM " . $this->table_categories . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateArchivedStatus($productId, $isArchived)
    {
        $query = "UPDATE " . $this->table_products . " SET is_archived = :is_archived WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':is_archived', $isArchived, PDO::PARAM_INT);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateProduct($id, $data)
    {
        $query = "UPDATE " . $this->table_products . " 
                  SET name = :name, 
                      description = :description, 
                      price = :price, 
                      category_id = :category_id, 
                      is_available = :is_available";

        if (isset($data['image_path'])) {
            $query .= ", image_path = :image_path";
        }

        $query .= " WHERE product_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':is_available', $data['is_available']);
        $stmt->bindParam(':id', $id);

        if (isset($data['image_path'])) {
            $stmt->bindParam(':image_path', $data['image_path']);
        }

        return $stmt->execute();
    }

    public function createProduct($data)
    {
        $query = "INSERT INTO " . $this->table_products . " 
                  (name, description, price, category_id, image_path, is_available) 
                  VALUES (:name, :description, :price, :category_id, :image_path, :is_available)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':image_path', $data['image_path']);
        $stmt->bindParam(':is_available', $data['is_available']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
}
