<?php

class Product {
    public $product_id;
    public $category_id;
    public $name;
    public $description;
    public $price;
    public $image_path;
    public $is_available;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        $this->product_id = $data['product_id'] ?? null;
        $this->category_id = $data['category_id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->image_path = $data['image_path'] ?? null;
        $this->is_available = $data['is_available'] ?? true;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
