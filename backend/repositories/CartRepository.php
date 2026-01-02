<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/CartItem.php';

class CartRepository {
    private $conn;
    private $table_carts = "carts";
    private $table_items = "cart_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCartByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_carts . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cart = new Cart($row);
            $cart->items = $this->getCartItems($cart->id);
            return $cart;
        }
        return null;
    }

    public function createCart($userId) {
        $query = "INSERT INTO " . $this->table_carts . " (user_id) VALUES (:user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            $cartId = $this->conn->lastInsertId();
            return $this->getCartById($cartId);
        }
        return false;
    }

    public function getCartById($id) {
        $query = "SELECT * FROM " . $this->table_carts . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return new Cart($row);
        }
        return null;
    }

    public function getCartItems($cartId) {
        $query = "SELECT ci.*, p.name as product_name, p.price, p.image_path 
                  FROM " . $this->table_items . " ci
                  JOIN products p ON ci.product_id = p.product_id
                  WHERE ci.cart_id = :cart_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new CartItem($row);
        }
        return $items;
    }

    public function addItem($cartId, $productId, $quantity) {
        // Check if item exists first
        $query = "SELECT id, quantity FROM " . $this->table_items . " WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Update quantity
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $newQty = $row['quantity'] + $quantity;
            return $this->updateItemQty($cartId, $productId, $newQty);
        } else {
            // Insert new
            $query = "INSERT INTO " . $this->table_items . " (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':quantity', $quantity);
            return $stmt->execute();
        }
    }

    public function updateItemQty($cartId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartId, $productId);
        }

        $query = "UPDATE " . $this->table_items . " SET quantity = :quantity WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        return $stmt->execute();
    }

    public function removeItem($cartId, $productId) {
        $query = "DELETE FROM " . $this->table_items . " WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        return $stmt->execute();
    }
    
    public function clearCart($cartId) {
        $query = "DELETE FROM " . $this->table_items . " WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        return $stmt->execute();
    }
}
