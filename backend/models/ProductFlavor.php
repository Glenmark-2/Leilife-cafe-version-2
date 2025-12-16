<?php

class ProductFlavor {
    public $flavor_id;
    public $product_id;
    public $flavor_name;
    public $is_available;

    public function __construct($data = []) {
        $this->flavor_id = $data['flavor_id'] ?? null;
        $this->product_id = $data['product_id'] ?? null;
        $this->flavor_name = $data['flavor_name'] ?? null;
        $this->is_available = $data['is_available'] ?? true;
    }
}
