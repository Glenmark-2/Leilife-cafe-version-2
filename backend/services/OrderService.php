<?php

require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';

class OrderService
{
    private $orderRepository;
    private $productRepository;
    private $cartRepository;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository, CartRepository $cartRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
    }

    public function placeOrder($userId, $data)
    {


        if (empty($data['payment_method'])) {
            return ['success' => false, 'message' => 'Payment method is required.'];
        }

        $phone = $data['phone'] ?? $data['contact_number'] ?? '';
        if (empty($phone)) {
            return ['success' => false, 'message' => 'Contact number is required.'];
        }

        $numericPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($numericPhone) !== 11 || strpos($numericPhone, '09') !== 0) {
            return ['success' => false, 'message' => 'Invalid contact number format. Please provide an 11-digit number starting with 09.'];
        }

        // 2. Calculate Totals & Prepare Items
        $finalItems = [];
        $totalAmount = 0;

        // Fetch Cart Items from DB to ensure source of truth
        $cart = $this->cartRepository->getCartByUserId($userId);
        $dbItems = $cart ? $this->cartRepository->getCartItems($cart->id) : [];

        if (empty($dbItems)) {
            // Fallback for logic consistency or if we want to allow direct data passing (e.g. 'buy now' without cart)
            // But user requested "database as source of truth".
            // If DB cart is empty, we should probably blocking the order or check if $data['items'] was passed as a "buy now" feature?
            // For this request, we strictly use DB.
            return ['success' => false, 'message' => 'Your cart is empty.'];
        }

        foreach ($dbItems as $item) {
             // CartItem object has product_id, quantity, etc.
             // We need to fetch latest product price/availability to be safe
             $product = $this->productRepository->findById($item->product_id);

             if (!$product) {
                 return ['success' => false, 'message' => 'Product not found: ID ' . $item->product_id];
             }

             if (isset($product['is_available']) && !$product['is_available']) {
                 return ['success' => false, 'message' => 'Product is not available: ' . $product['name']];
             }

             $price = $product['price'];
             $quantity = $item->quantity;
             $subtotal = $price * $quantity;

             $totalAmount += $subtotal;

             $finalItems[] = new OrderItem([
                 'product_id' => $product['product_id'],
                 'product_name' => $product['name'],
                 'price' => $price,
                 'quantity' => $quantity,
                 'subtotal' => $subtotal
             ]);
        }

        $deliveryFee = isset($data['delivery_fee']) ? floatval($data['delivery_fee']) : 0.00;
        // If delivery method is pickup, fee should be 0
        if (isset($data['delivery_method']) && $data['delivery_method'] === 'pickup') {
            $deliveryFee = 0;
        }

        $totalAmount += $deliveryFee;

        // 3. Create Order Object
        $order = new Order();
        $order->user_id = $userId;
        $order->total_amount = $totalAmount;
        $order->delivery_fee = $deliveryFee;

        $order->payment_method = $data['payment_method'];
        $order->delivery_method = $data['delivery_method'] ?? 'pickup';

        // Handle delivery address
        // If it's a JSON string or array, we might want to standardize
        $order->delivery_address = isset($data['delivery_address']) ? (is_string($data['delivery_address']) ? $data['delivery_address'] : json_encode($data['delivery_address'])) : null;
        $order->delivery_notes = $data['delivery_notes'] ?? null;
        $order->contact_number = $data['phone'] ?? $data['contact_number'] ?? null;

        // Set Initial Status
        $order->status = 'pending';
        // For COD, payment is unpaid. For GCash, it starts unpaid until webhook/callback confirms.
        $order->payment_status = 'unpaid';

        // 4. Save to DB
        // Use a transaction if possible, but Repository handles connections. 
        // We'll just do sequential inserts for now.

        $orderId = $this->orderRepository->create($order);

        if ($orderId) {
            foreach ($finalItems as $item) {
                $item->order_id = $orderId;
                $this->orderRepository->addOrderItem($item);
            }

            // Clear user's cart
            $cart = $this->cartRepository->getCartByUserId($userId);
            if ($cart) {
                $this->cartRepository->clearCart($cart->id);
            }

            return ['success' => true, 'message' => 'Order placed successfully.', 'order_id' => $orderId];
        }

        return ['success' => false, 'message' => 'Failed to place order.'];
    }

    public function getUserOrders($userId)
    {
        $orders = $this->orderRepository->findByUserId($userId);
        // Convert objects to arrays for JSON response or keep as objects.
        // Usually safer to return data structures.
        return ['success' => true, 'orders' => $orders];
    }
    public function cancelOrder($userId, $orderId)
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if ($order->user_id != $userId) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        if ($order->status !== 'pending') {
            return ['success' => false, 'message' => 'Order cannot be cancelled. It is already ' . $order->status];
        }

        if ($this->orderRepository->updateStatus($orderId, 'cancelled')) {
            // Synchronize items: Cancel all items for this order
            $this->orderRepository->updateItemsStatusByOrderId($orderId, 'cancelled');
            return ['success' => true, 'message' => 'Order cancelled successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to cancel order.'];
    }
}
