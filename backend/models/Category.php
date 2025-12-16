<?php

class Category {
    public $category_id;
    public $name;
    public $parent_id;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        $this->category_id = $data['category_id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->parent_id = $data['parent_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
