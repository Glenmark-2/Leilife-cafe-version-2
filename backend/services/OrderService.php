<?php

require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/PayMongoService.php';
require_once __DIR__ . '/RealtimeService.php';
require_once __DIR__ . '/../helpers/UrlHelper.php';

class OrderService
{
    private $orderRepository;
    private $productRepository;
    private $cartRepository;
    private $transactionRepository;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository, CartRepository $cartRepository, TransactionRepository $transactionRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->transactionRepository = $transactionRepository;
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

        $order->delivery_address = isset($data['delivery_address']) ? (is_string($data['delivery_address']) ? $data['delivery_address'] : json_encode($data['delivery_address'])) : null;
        $order->delivery_notes = $data['delivery_notes'] ?? null;
        $order->contact_number = $data['phone'] ?? $data['contact_number'] ?? null;

        $order->status = 'pending';
        // For GCash, start as unpaid.
        $order->payment_status = 'unpaid';

        // 4. Save to DB (Initial Save to get Order ID)
        $orderId = $this->orderRepository->create($order);

        if ($orderId) {
            foreach ($finalItems as $item) {
                $item->order_id = $orderId;
                $this->orderRepository->addOrderItem($item);
            }

            // 5. Log initial transaction
            $this->transactionRepository->create([
                'order_id' => $orderId,
                'transaction_type' => 'payment',
                'transaction_reference' => $order->payment_method === 'cod' ? 'COD-' . $orderId : 'TBD',
                'amount' => $totalAmount,
                'status' => 'pending',
                'description' => 'Initial order record',
                'payment_method' => $order->payment_method,
                'raw_response' => null
            ]);

            // PayMongo Integration for GCash
            if ($order->payment_method === 'gcash' || $order->payment_method === 'grab_pay') {
                $payMongo = new PayMongoService();
                $amountInCentavos = (int) ($totalAmount * 100);

                // Construct Redirect URLs
                // Assuming running on localhost/Leilife_2nd
                // Construct Redirect URLs dynamically
            $platform = $data['platform'] ?? 'web';
            $appRedirectReturn = $data['return_url'] ?? '';
            $appRedirectCancel = $data['cancel_url'] ?? '';

            $successUrl = UrlHelper::getFullUrl("/backend/api/paymongo_callback.php?status=success&order_id=" . $orderId . "&platform=" . $platform . (!empty($appRedirectReturn) ? "&app_redirect=" . urlencode($appRedirectReturn) : ""));
            $failedUrl = UrlHelper::getFullUrl("/backend/api/paymongo_callback.php?status=failed&order_id=" . $orderId . "&platform=" . $platform . (!empty($appRedirectCancel) ? "&app_redirect=" . urlencode($appRedirectCancel) : ""));

            $sourceResult = $payMongo->createSource($amountInCentavos, $successUrl, $failedUrl, 'PHP', $order->payment_method, [
                    'order_id' => $orderId
                ]);

                if ($sourceResult['success']) {
                    $sourceData = $sourceResult['data'];
                    $sourceId = $sourceData['id'];
                    $checkoutUrl = $sourceData['attributes']['redirect']['checkout_url'];

                    // Update Order with Source ID
                    // We need a method in Repository to update this field specifically or just update the order object and save?
                    // Repository has no explicit update method for these fields, so we might need to add one or use raw query.
                    // Or let's just assume we can update it. 
                    // Wait, I should add updatePayMongoSourceId to Repository. 
                    // For now, I'll execute a direct update via Repository logic if I can, OR add the method to Repository.
                    // Since I cannot edit Repository in the same tool call easily without context, I will Assume I can add it or doing it here.
                    // Actually, I can't access $this->orderRepository->conn directly if it's private.
                    // I will add a text task to update OrderRepository.

                    // Ideally: $this->orderRepository->updatePayMongoId($orderId, $sourceId);
                    // But I haven't written that yet. 
                    // I will define it as a requirement.
                    // Workaround: I'll use the existing generic 'updateStatus' if I could, but I can't.

                    // I will ADD the method to OrderRepository next.
                    // So I'll call it here assuming it exists.
                    $this->orderRepository->updatePayMongoSource($orderId, $sourceId);

                    // Log Source Creation
                    $this->transactionRepository->create([
                        'order_id' => $orderId,
                        'transaction_type' => 'payment',
                        'transaction_reference' => $sourceId,
                        'amount' => $totalAmount,
                        'status' => 'pending',
                        'description' => 'PayMongo Source Created',
                        'payment_method' => $order->payment_method,
                        'raw_response' => $sourceResult
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Redirecting to payment...',
                        'order_id' => $orderId,
                        'checkout_url' => $checkoutUrl,
                        'payment_method' => $order->payment_method
                    ];
                } else {
                    // PayMongo Creation Failed
                    // Cancel the order?
                    $this->orderRepository->updateStatus($orderId, 'cancelled');

                    // Log Failure
                    $this->transactionRepository->create([
                        'order_id' => $orderId,
                        'transaction_type' => 'payment',
                        'transaction_reference' => 'SOURCE_CREATION_FAILED',
                        'amount' => $totalAmount,
                        'status' => 'failed',
                        'description' => 'Failed to initialize PayMongo source: ' . ($sourceResult['error'] ?? 'Unknown Error'),
                        'payment_method' => $order->payment_method,
                        'raw_response' => $sourceResult
                    ]);

                    error_log("PayMongo Error: " . print_r($sourceResult, true));
                    return ['success' => false, 'message' => 'Failed to initialize payment gateway. ' . ($sourceResult['error'] ?? '')];
                }
            }

            // Clear user's cart
            // FOR PAYMONGO: We clear only AFTER payment is confirmed in callback/webhook
            // FOR COD: Clear immediately
            if ($order->payment_method === 'cod') {
                $cart = $this->cartRepository->getCartByUserId($userId);
                if ($cart) {
                    $this->cartRepository->clearCart($cart->id);
                }
            }

            // Trigger Real-time event for Admin
            RealtimeService::trigger('admin-orders', 'new-order', ['order_id' => $orderId]);

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

        // Scenario: Order is already paid. We should trigger a refund.
        if ($order->payment_status === 'paid' || $order->payment_status === 'partially_refunded') {
            return $this->refundOrder($userId, $orderId, 'requested_by_customer');
        }

        // Standard Cancellation for unpaid/pending orders
        if ($order->status !== 'pending') {
            return ['success' => false, 'message' => 'Order cannot be cancelled. It is already ' . $order->status];
        }

        if ($this->orderRepository->updateStatus($orderId, 'cancelled')) {
            // Synchronize items: Cancel all items for this order
            $this->orderRepository->updateItemsStatusByOrderId($orderId, 'cancelled');

            // Trigger Real-time events
            RealtimeService::trigger('order-' . $orderId, 'status-updated', ['status' => 'cancelled']);
            RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'cancelled']);
            RealtimeService::trigger('user-' . $userId, 'order-status-changed', ['orderId' => $orderId, 'status' => 'cancelled']);

            // Log the cancellation
            $this->transactionRepository->create([
                'order_id' => $orderId,
                'transaction_type' => 'payment',
                'transaction_reference' => 'CANCEL-' . $orderId,
                'amount' => $order->total_amount,
                'status' => 'failed',
                'description' => 'Order was manually cancelled by the user.',
                'payment_method' => $order->payment_method,
                'raw_response' => null
            ]);

            return ['success' => true, 'message' => 'Order cancelled successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to cancel order.'];
    }

    public function refundOrder($userId, $orderId, $reason = 'requested_by_customer')
    {
        // 1. Check if user is admin (Actually, this service might be called by an admin controller)
        // For now, let's assume the controller handles auth.

        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if ($order->payment_status !== 'paid' && $order->payment_status !== 'partially_refunded') {
            return ['success' => false, 'message' => 'Order is already fully refunded or unpaid.'];
        }

        // 2. Find the successful payment transaction ID
        $transactions = $this->transactionRepository->findByOrderId($orderId);
        $paymentId = null;
        foreach ($transactions as $tx) {
            if ($tx['status'] === 'success' && $tx['transaction_type'] === 'payment') {
                $paymentId = $tx['transaction_reference'];
                break;
            }
        }

        if (!$paymentId && ($order->payment_method === 'gcash' || $order->payment_method === 'grab_pay')) {
            return ['success' => false, 'message' => 'Payment record not found for refund.'];
        }

        if ($order->payment_method === 'gcash' || $order->payment_method === 'grab_pay') {
            $payMongo = new PayMongoService();

            // Calculate refund amount: 
            // If the order was partially refunded, we need to know how much is LEFT to refund.
            // For a 'full' refund request, we want to refund the entire REMAINING balance.
            $transactions = $this->transactionRepository->findByOrderId($orderId);
            $totalPaid = 0;
            $totalRefunded = 0;
            foreach ($transactions as $tx) {
                if ($tx['status'] === 'success') {
                    if ($tx['transaction_type'] === 'payment') $totalPaid += (float) $tx['amount'];
                    if ($tx['transaction_type'] === 'refund') $totalRefunded += (float) $tx['amount'];
                }
            }

            // Round to 2 decimal places to avoid float precision issues
            $totalPaid = round($totalPaid, 2);
            $totalRefunded = round($totalRefunded, 2);
            $remainingBalance = round($totalPaid - $totalRefunded, 2);

            error_log("Refund Calc for Order #$orderId: Paid=$totalPaid, Refunded=$totalRefunded, Remaining=$remainingBalance");

            if ($remainingBalance <= 0) {
                // If already fully refunded, just ensure the statuses are correct
                $this->orderRepository->updateStatus($orderId, 'cancelled');
                $this->orderRepository->updatePaymentStatus($orderId, 'refunded');
                return ['success' => true, 'message' => 'Order was already fully refunded. Status updated.'];
            }

            // --- FIX FOR REFUND MATCHING ISSUE ---
            // PayMongo sometimes has slight centavo differences due to rounding, or 
            // there might be a mismatch in transaction records.
            // We verify the actual payment amount from PayMongo to be safe.
            $paymentData = $payMongo->retrievePayment($paymentId);
            if ($paymentData['success']) {
                $paymongoAmount = (int) $paymentData['data']['attributes']['amount']; // In centavos
                $paymongoRefunded = (int) ($paymentData['data']['attributes']['refund_details']['total_refunded'] ?? 0);
                $paymongoRemaining = $paymongoAmount - $paymongoRefunded;

                $internalAmountInCentavos = (int) round($remainingBalance * 100);

                // Cap the refund amount to PayMongo's remaining balance
                $amountInCentavos = min($internalAmountInCentavos, $paymongoRemaining);
                
                if ($amountInCentavos <= 0) {
                     return ['success' => false, 'message' => 'No refundable balance remaining on PayMongo for payment ID: ' . $paymentId];
                }
            } else {
                // Fallback to internal math if API check fails
                $amountInCentavos = (int) round($remainingBalance * 100);
            }

            $refundResult = $payMongo->createRefund($paymentId, $amountInCentavos, $reason);

            if ($refundResult['success']) {
                $this->orderRepository->updateStatus($orderId, 'cancelled');
                $this->orderRepository->updatePaymentStatus($orderId, 'refunded');
                $this->orderRepository->updateItemsStatusByOrderId($orderId, 'cancelled');

                // Update Log
            $actualRefundAmount = $amountInCentavos / 100;
            $this->transactionRepository->create([
                'order_id' => $orderId,
                'transaction_type' => 'refund',
                'transaction_reference' => $refundResult['data']['id'],
                'amount' => $actualRefundAmount,
                'status' => 'success',
                'description' => 'Remaining balance fully refunded via PayMongo. Reason: ' . $reason,
                'payment_method' => $order->payment_method,
                'raw_response' => $refundResult
            ]);

                return ['success' => true, 'message' => 'Refund successful.'];
            } else {
                return ['success' => false, 'message' => 'Refund failed: ' . ($refundResult['error'] ?? 'Unknown error')];
            }
        } else {
            // COD Refund (Manual/Cash)
            $this->orderRepository->updateStatus($orderId, 'cancelled');
            $this->orderRepository->updatePaymentStatus($orderId, 'refunded');
            $this->orderRepository->updateItemsStatusByOrderId($orderId, 'cancelled');

            $this->transactionRepository->create([
                'order_id' => $orderId,
                'transaction_type' => 'refund',
                'transaction_reference' => 'MANUAL-' . $orderId,
                'amount' => $order->total_amount,
                'status' => 'success',
                'description' => 'Manual refund processed for COD. Reason: ' . $reason,
                'payment_method' => $order->payment_method,
                'raw_response' => null
            ]);
            RealtimeService::trigger('order-' . $orderId, 'status-updated', ['status' => 'cancelled']);
            RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'cancelled']);
            RealtimeService::trigger('user-' . $userId, 'order-status-changed', ['orderId' => $orderId, 'status' => 'cancelled']);

            return ['success' => true, 'message' => 'Manual refund logged.'];
        }
    }

    public function cancelOrderItem($userId, $itemId, $reason = 'requested_by_customer')
    {
        $item = $this->orderRepository->getOrderItemById($itemId);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found.'];
        }

        $orderId = $item['order_id'];
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        // Logic check: only pending or preparing orders can have items cancelled?
        // Or any order that hasn't been delivered? Let's stick to status logic.
        if (in_array($order->status, ['delivered', 'picked_up', 'cancelled'])) {
            return ['success' => false, 'message' => 'Order item cannot be cancelled because the order is ' . $order->status];
        }

        if ($item['status'] === 'cancelled') {
            return ['success' => false, 'message' => 'Item is already cancelled.'];
        }

        // 1. Mark Item as Cancelled
        if ($this->orderRepository->updateOrderItemStatus($itemId, 'cancelled')) {

            // 2. If Order was PAID, handle partial refund
            if ($order->payment_status === 'paid' || $order->payment_status === 'partially_refunded') {

                // Find original payment ID
                $transactions = $this->transactionRepository->findByOrderId($orderId);
                $paymentId = null;
                foreach ($transactions as $tx) {
                    if ($tx['status'] === 'success' && $tx['transaction_type'] === 'payment') {
                        $paymentId = $tx['transaction_reference'];
                        break;
                    }
                }

                if ($order->payment_method === 'gcash' || $order->payment_method === 'grab_pay') {
                    if (!$paymentId) {
                        return ['success' => false, 'message' => 'Payment record not found for partial refund.'];
                    }

                    $payMongo = new PayMongoService();
                    $amountInCentavos = (int) ($item['subtotal'] * 100);

                    $refundResult = $payMongo->createRefund($paymentId, $amountInCentavos, $reason, "Partial refund for item: " . $item['product_name']);

                    if ($refundResult['success']) {
                        $this->orderRepository->updatePaymentStatus($orderId, 'partially_refunded');

                        $this->transactionRepository->create([
                            'order_id' => $orderId,
                            'transaction_type' => 'refund',
                            'transaction_reference' => $refundResult['data']['id'],
                            'amount' => $item['subtotal'],
                            'status' => 'success',
                            'description' => 'Partial refund processed for item: ' . $item['product_name'],
                            'payment_method' => $order->payment_method,
                            'raw_response' => $refundResult
                        ]);
                    } else {
                        // Rollback or notify?
                        // For now, return error but item status is already updated in DB.
                        return ['success' => false, 'message' => 'Item cancelled in DB, but PayMongo refund failed: ' . ($refundResult['error'] ?? 'Unknown Error')];
                    }
                } else {
                    // COD Partial Refund
                    $this->orderRepository->updatePaymentStatus($orderId, 'partially_refunded');
                    $this->transactionRepository->create([
                        'order_id' => $orderId,
                        'transaction_type' => 'refund',
                        'transaction_reference' => 'MANUAL-PARTIAL-' . $itemId,
                        'amount' => $item['subtotal'],
                        'status' => 'success',
                        'description' => 'Manual partial refund logged for item: ' . $item['product_name'],
                        'payment_method' => $order->payment_method,
                        'raw_response' => null
                    ]);
                }
            }

            // Trigger Real-time Events
            RealtimeService::trigger('order-' . $orderId, 'item-updated', ['itemId' => $itemId, 'status' => 'cancelled']);
            RealtimeService::trigger('admin-orders', 'item-updated', ['orderId' => $orderId]);

            // 3. Check if all items are now cancelled. If so, cancel the whole order.
            $allItems = $this->orderRepository->getOrderItems($orderId);
            $allCancelled = true;
            foreach ($allItems as $oi) {
                if ($oi->status !== 'cancelled') {
                    $allCancelled = false;
                    break;
                }
            }

            if ($allCancelled) {
                $this->orderRepository->updateStatus($orderId, 'cancelled');
                // Trigger full order cancellation events
                RealtimeService::trigger('order-' . $orderId, 'status-updated', ['status' => 'cancelled']);
                RealtimeService::trigger('admin-orders', 'status-updated', ['orderId' => $orderId, 'status' => 'cancelled']);
                RealtimeService::trigger('user-' . $order->user_id, 'order-status-changed', ['orderId' => $orderId, 'status' => 'cancelled']);

                // If the order was paid or already partially refunded, it's now fully refunded
                if ($order->payment_status === 'paid' || $order->payment_status === 'partially_refunded') {
                    $this->orderRepository->updatePaymentStatus($orderId, 'refunded');
                }
            }

            return ['success' => true, 'message' => 'Item cancelled and refund processed successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to cancel item.'];
    }
}
