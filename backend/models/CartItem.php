<?php

class CartItem {
    public $id;
    public $cart_id;
    public $product_id;
    public $quantity;
    public $created_at;
    
    // Product details for display
    public $product_name;
    public $product_price;
    public $product_image;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->cart_id = $data['cart_id'] ?? null;
        $this->product_id = $data['product_id'] ?? null;
        $this->quantity = $data['quantity'] ?? 1;
        $this->created_at = $data['created_at'] ?? null;
        
        $this->product_name = $data['product_name'] ?? null;
        $this->product_price = $data['price'] ?? null; // Mapped from 'price' alias in query
        $this->product_image = $data['image_path'] ?? null; // Mapped from 'image_path' alias
    }
}
