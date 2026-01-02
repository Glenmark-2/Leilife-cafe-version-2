<?php

require_once __DIR__ . '/../repositories/CartRepository.php';

class CartService {
    private $cartRepo;

    public function __construct(CartRepository $cartRepo) {
        $this->cartRepo = $cartRepo;
    }

    public function getCart($userId) {
        $cart = $this->cartRepo->getCartByUserId($userId);
        if (!$cart) {
            // Create new cart if none exists
            $cart = $this->cartRepo->createCart($userId);
            if ($cart) {
                $cart->items = [];
            }
        }
        return $cart;
    }

    public function addItem($userId, $productId, $quantity) {
        $cart = $this->getCart($userId);
        if ($cart) {
            return $this->cartRepo->addItem($cart->id, $productId, $quantity);
        }
        return false;
    }

    public function updateItemQty($userId, $productId, $quantity) {
        $cart = $this->getCart($userId);
        if ($cart) {
            return $this->cartRepo->updateItemQty($cart->id, $productId, $quantity);
        }
        return false;
    }

    public function removeItem($userId, $productId) {
        $cart = $this->getCart($userId);
        if ($cart) {
            return $this->cartRepo->removeItem($cart->id, $productId);
        }
        return false;
    }

    public function clearCart($userId) {
        $cart = $this->getCart($userId);
        if ($cart) {
            return $this->cartRepo->clearCart($cart->id);
        }
        return false;
    }
    
    public function mergeCart($userId, $localItems) {
        $cart = $this->getCart($userId);
        if (!$cart) return false;

        $results = true;
        foreach ($localItems as $item) {
             // item structure from local storage: { id, qty, ... }
             $pid = $item['id'];
             $qty = $item['qty'];
             $results = $results && $this->cartRepo->addItem($cart->id, $pid, $qty);
        }
        return $results;
    }
}
