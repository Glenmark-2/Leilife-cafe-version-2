<?php

class OrderItem {
    public $id;
    public $order_id;
    public $product_id;
    public $product_name;
    public $price;
    public $quantity;
    public $subtotal;
    public $status;

    public function __construct($data = null) {
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->order_id = $data['order_id'] ?? null;
            $this->product_id = $data['product_id'] ?? null;
            $this->product_name = $data['product_name'] ?? null;
            $this->price = $data['price'] ?? 0.00;
            $this->quantity = $data['quantity'] ?? 0;
            $this->subtotal = $data['subtotal'] ?? 0.00;
            $this->status = $data['status'] ?? 'pending';
        }
    }
}
