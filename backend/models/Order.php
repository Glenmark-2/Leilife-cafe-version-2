<?php

class Order
{
    public $id;
    public $order_number;
    public $user_id;
    public $total_amount;
    public $delivery_fee;
    public $status;
    public $payment_status;
    public $payment_method;
    public $delivery_method;
    public $delivery_address;
    public $delivery_notes;
    public $paymongo_checkout_session_id;
    public $paymongo_payment_intent_id;
    public $contact_number;
    public $customer_name;
    public $feedback;
    public $created_at;
    public $updated_at;

    // Optional: list of items
    public $items = [];

    public function __construct($data = null)
    {
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->order_number = $data['order_number'] ?? null;
            $this->user_id = $data['user_id'] ?? null;
            $this->customer_name = $data['customer_name'] ?? null;
            $this->contact_number = $data['contact_number'] ?? null;
            $this->total_amount = $data['total_amount'] ?? 0.00;
            $this->delivery_fee = $data['delivery_fee'] ?? 0.00;
            $this->status = $data['status'] ?? 'pending';
            $this->payment_status = $data['payment_status'] ?? 'unpaid';
            $this->payment_method = $data['payment_method'] ?? null;
            $this->delivery_method = $data['delivery_method'] ?? null;
            $this->delivery_address = $data['delivery_address'] ?? null;
            $this->delivery_notes = $data['delivery_notes'] ?? null;
            $this->paymongo_checkout_session_id = $data['paymongo_checkout_session_id'] ?? null;
            $this->paymongo_payment_intent_id = $data['paymongo_payment_intent_id'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
}
