<?php

class ProductSize {
    public $size_id;
    public $product_id;
    public $size_label;
    public $price;
    public $is_available;

    public function __construct($data = []) {
        $this->size_id = $data['size_id'] ?? null;
        $this->product_id = $data['product_id'] ?? null;
        $this->size_label = $data['size_label'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->is_available = $data['is_available'] ?? true;
    }
}
