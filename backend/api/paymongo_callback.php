<?php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../services/PayMongoService.php';
require_once __DIR__ . '/../helpers/UrlHelper.php';

// Instantiate DB
$database = new Database();
$db = $database->getConnection();

$orderRepo = new OrderRepository($db);
$transactionRepo = new TransactionRepository($db);
$cartRepo = new CartRepository($db);
$payMongo = new PayMongoService();

// Get Parameters
$orderId = $_GET['order_id'] ?? null;
$status = $_GET['status'] ?? null;
$platform = $_GET['platform'] ?? 'web';

if (!$orderId) {
    die("Invalid Callback: Missing Order ID.");
}

// Fetch Order
$order = $orderRepo->findById($orderId);
if (!$order) {
    die("Order not found.");
}

// Ensure we are redirecting to frontend dynamically
$frontendUrl = UrlHelper::getFullUrl("/public/index.php?page=order_tracking&order_id=" . $orderId);
$cartUrl = UrlHelper::getFullUrl("/public/index.php?page=checkout&error=payment_failed");

// Mobile Redirects (Deep Linking)
if ($platform === 'mobile') {
    $frontendUrl = "leilife://orders?status=success&order_id=" . $orderId;
    $cartUrl = "leilife://checkout?status=failed&error=payment_failed";
}

if ($status === 'failed') {
    // Payment failed or cancelled by user
    // Update order status? Or keeping it pending?
    // If user cancelled in GCash, source status is 'cancelled'.
    // We can mark order as cancelled.
    $orderRepo->updateStatus($orderId, 'cancelled');
    // Helper to sync items
    $orderRepo->updateItemsStatusByOrderId($orderId, 'cancelled');
    
    // Log Failure
    $transactionRepo->create([
        'order_id' => $orderId,
        'transaction_type' => 'payment',
        'transaction_reference' => 'CALLBACK_FAILED',
        'amount' => $order->total_amount,
        'status' => 'failed',
        'description' => 'User cancelled or payment failed in PayMongo checkout.',
        'payment_method' => $order->payment_method,
        'raw_response' => ['get_params' => $_GET]
    ]);

    // Redirect to cart/checkout with error
    header("Location: " . $cartUrl);
    exit;
}

if ($status === 'success') {
    // Verify with PayMongo
    $sourceId = $order->paymongo_payment_intent_id;
    
    if (!$sourceId) {
        // Fallback? Maybe it wasn't saved?
        die("System Error: Source ID missing for order.");
    }

    $sourceResult = $payMongo->retrieveSource($sourceId);
    
    if (!$sourceResult['success']) {
         // API Error
         error_log("PayMongo Retrieve Error: " . print_r($sourceResult, true));
         
         $transactionRepo->create([
            'order_id' => $orderId,
            'transaction_type' => 'payment',
            'transaction_reference' => $sourceId,
            'amount' => $order->total_amount,
            'status' => 'failed',
            'description' => 'Failed to retrieve source from PayMongo.',
            'payment_method' => $order->payment_method,
            'raw_response' => $sourceResult
        ]);

         header("Location: " . $cartUrl);
         exit;
    }

    $sourceData = $sourceResult['data'];
    $sourceStatus = $sourceData['attributes']['status']; // chargeable, consumed, cancelled, failed

    if ($sourceStatus === 'chargeable') {
        // Create Payment
        $amount = $sourceData['attributes']['amount']; // In centavos
        $description = "Payment for Order #" . $order->order_number;
        
        $paymentResult = $payMongo->createPayment($sourceId, $amount, 'PHP', $description, ['order_id' => $orderId]);

        if ($paymentResult['success']) {
            // Payment Successful!
            // Update Order
            // We can update payment_status to 'paid'
            // And order status to 'preparing' (since it's paid and confirmed)
            
            // There isn't a separate updatePaymentStatus method exposed in Repository public interface in the snippet I saw,
            // but updateStatus exists.
            
            // Wait, Order schema has 'payment_status'.
            // I should update that too.
            // Since I added updatePayMongoSource, I should have added updatePaymentStatus or similar.
            // But I can use raw query here or add another method. 
            // I'll add a quick raw query execution here for simplicity or assume I can modify Repository again.
            // Modifying Repository is better.
            
            // For now, I'll update 'status' to 'preparing'.
            // And create a new method to update payment_status or just execute SQL here?
            // Executing SQL here using $db is easy since I have $db.
            
            $updateQuery = "UPDATE orders SET payment_status = 'paid', status = 'pending' WHERE id = :id";
            $stmt = $db->prepare($updateQuery);
            $stmt->execute([':id' => $orderId]);

            // Sync items status?
            $orderRepo->updateItemsStatusByOrderId($orderId, 'pending');

            // Log Success Payment
            $transactionRepo->create([
                'order_id' => $orderId,
                'transaction_type' => 'payment',
                'transaction_reference' => $paymentResult['data']['id'] ?? $sourceId,
                'amount' => $order->total_amount,
                'status' => 'success',
                'description' => 'Payment successfully created and charged.',
                'payment_method' => $order->payment_method,
                'raw_response' => $paymentResult
            ]);

            // Clear Cart
            $cart = $cartRepo->getCartByUserId($order->user_id);
            if ($cart) {
                $cartRepo->clearCart($cart->id);
            }

            // Redirect to Success Page
            header("Location: " . $frontendUrl . "&payment=success");
            exit;

        } else {
             // Payment Creation Failed
             error_log("Payment Creation Failed: " . print_r($paymentResult, true));
             
             // Log Failure
             $transactionRepo->create([
                'order_id' => $orderId,
                'transaction_type' => 'payment',
                'transaction_reference' => $sourceId,
                'amount' => $order->total_amount,
                'status' => 'failed',
                'description' => 'Failed to create payment from chargeable source.',
                'payment_method' => $order->payment_method,
                'raw_response' => $paymentResult
            ]);

             // Maybe source was already used?
             if ($paymentResult['error'] === 'Source used') {
                 // Already paid?
                 header("Location: " . $frontendUrl);
                 exit;
             }
             
             header("Location: " . $cartUrl);
             exit;
        }

    } elseif ($sourceStatus === 'consumed' || $sourceStatus === 'paid') {
        // Already paid
        header("Location: " . $frontendUrl);
        exit;
    } else {
        // Startus is cancelled or failed or pending
        // If pending, maybe user closed window before auth?
        // Treat as failed for now.
        $orderRepo->updateStatus($orderId, 'cancelled');
        header("Location: " . $cartUrl);
        exit;
    }
}
