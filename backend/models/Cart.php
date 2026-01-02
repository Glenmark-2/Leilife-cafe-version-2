<?php

class Cart {
    public $id;
    public $user_id;
    public $created_at;
    public $updated_at;
    public $items = [];

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
