<?php

class UserRegistration {
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $password;
    public $verification_token;
    public $token_expires_at;
    public $created_at;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->first_name = $data['first_name'] ?? null;
        $this->last_name = $data['last_name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->phone_number = $data['phone_number'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->verification_token = $data['verification_token'] ?? null;
        $this->token_expires_at = $data['token_expires_at'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }
}
