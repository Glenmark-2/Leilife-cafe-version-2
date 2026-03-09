<?php
// PayMongo Webhook Handler
// This receives webhook events from PayMongo (source.chargeable, payment.paid, etc.)

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../services/PayMongoService.php';
require_once __DIR__ . '/../helpers/EnvLoader.php';

// Load environment
EnvLoader::load(__DIR__ . '/../../.env');

// Get webhook secret
$webhookSecret = getenv('PAYMONGO_WEBHOOK_SECRET');

// Get raw POST body
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Verify webhook signature
$signature = $headers['Paymongo-Signature'] ?? '';

if (!$signature || !$webhookSecret) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify signature (PayMongo uses timestamp + payload)
// Format: t=timestamp,s1=signature
$signatureParts = [];
foreach (explode(',', $signature) as $part) {
    list($key, $value) = explode('=', $part, 2);
    $signatureParts[$key] = $value;
}

$timestamp = $signatureParts['t'] ?? '';
$providedSignature = $signatureParts['s1'] ?? '';

// Create expected signature
$signedPayload = $timestamp . '.' . $payload;
$expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

// Compare signatures
if (!hash_equals($expectedSignature, $providedSignature)) {
    http_response_code(401);
    error_log("PayMongo Webhook: Invalid signature");
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Parse webhook data
$event = json_decode($payload, true);

if (!$event) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Log webhook event
error_log("PayMongo Webhook Event: " . ($event['data']['attributes']['type'] ?? 'unknown'));

// Initialize services
$database = new Database();
$db = $database->getConnection();
$orderRepo = new OrderRepository($db);
$transactionRepo = new TransactionRepository($db);
$cartRepo = new CartRepository($db);
$payMongo = new PayMongoService();

// Get event type
$eventType = $event['data']['attributes']['type'] ?? '';
$eventData = $event['data']['attributes']['data'] ?? [];

// Handle different event types
switch ($eventType) {
    case 'source.chargeable':
        handleSourceChargeable($eventData, $orderRepo, $transactionRepo, $payMongo, $db, $cartRepo);
        break;
    
    case 'payment.paid':
        handlePaymentPaid($eventData, $orderRepo, $transactionRepo, $cartRepo);
        break;
    
    case 'payment.failed':
        handlePaymentFailed($eventData, $orderRepo, $transactionRepo);
        break;
    
    case 'source.cancelled':
        handleSourceCancelled($eventData, $orderRepo, $transactionRepo);
        break;
    
    default:
        error_log("PayMongo Webhook: Unhandled event type: " . $eventType);
}

// Return success response
http_response_code(200);
echo json_encode(['success' => true]);
exit;

// ============================================================================
// Event Handlers
// ============================================================================

function handleSourceChargeable($data, $orderRepo, $transactionRepo, $payMongo, $db, $cartRepo) {
    $sourceId = $data['id'] ?? null;
    $amount = $data['attributes']['amount'] ?? 0;
    
    if (!$sourceId) {
        error_log("PayMongo Webhook: Missing source ID");
        return;
    }
    
    // Find order by source ID
    $query = "SELECT * FROM orders WHERE paymongo_payment_intent_id = :source_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':source_id' => $sourceId]);
    $order = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$order) {
        error_log("PayMongo Webhook: Order not found for source: " . $sourceId);
        return;
    }
    
    // Check if already paid
    if ($order->payment_status === 'paid') {
        error_log("PayMongo Webhook: Order already paid: " . $order->id);
        return;
    }
    
    // Create payment
    $description = "Payment for Order #" . $order->order_number;
    $paymentResult = $payMongo->createPayment($sourceId, $amount, 'PHP', $description, ['order_id' => $order->id]);
    
    if ($paymentResult['success']) {
        // Update order status
        $updateQuery = "UPDATE orders SET payment_status = 'paid', status = 'pending' WHERE id = :id";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute([':id' => $order->id]);
        
        // Update order items
        $orderRepo->updateItemsStatusByOrderId($order->id, 'pending');
        
        // Log transaction
        $transactionRepo->create([
            'order_id' => $order->id,
            'transaction_type' => 'payment',
            'transaction_reference' => $paymentResult['data']['id'] ?? $sourceId,
            'amount' => $order->total_amount,
            'status' => 'success',
            'description' => 'Payment created via webhook (source.chargeable)',
            'payment_method' => $order->payment_method,
            'raw_response' => $paymentResult
        ]);

        // Clear cart after confirmed payment.
        $cart = $cartRepo->getCartByUserId($order->user_id);
        if ($cart) {
            $cartRepo->clearCart($cart->id);
        }
        
        error_log("PayMongo Webhook: Payment successful for order: " . $order->id);
    } else {
        error_log("PayMongo Webhook: Payment creation failed: " . print_r($paymentResult, true));
        
        // Log failed transaction
        $transactionRepo->create([
            'order_id' => $order->id,
            'transaction_type' => 'payment',
            'transaction_reference' => $sourceId,
            'amount' => $order->total_amount,
            'status' => 'failed',
            'description' => 'Payment creation failed via webhook',
            'payment_method' => $order->payment_method,
            'raw_response' => $paymentResult
        ]);
    }
}

function handlePaymentPaid($data, $orderRepo, $transactionRepo, $cartRepo) {
    $paymentId = $data['id'] ?? null;
    $metadata = $data['attributes']['metadata'] ?? [];
    $orderId = $metadata['order_id'] ?? null;
    
    if (!$orderId) {
        error_log("PayMongo Webhook: Missing order_id in payment.paid metadata");
        return;
    }
    
    $order = $orderRepo->findById($orderId);
    if (!$order) {
        error_log("PayMongo Webhook: Order not found: " . $orderId);
        return;
    }
    
    // Ensure payment status is updated
    if ($order->payment_status !== 'paid') {
        $orderRepo->updatePaymentStatus($orderId, 'paid');
        error_log("PayMongo Webhook: Payment confirmed for order: " . $orderId);
    }

    // Ensure cart is cleared for paid orders.
    $cart = $cartRepo->getCartByUserId($order->user_id);
    if ($cart) {
        $cartRepo->clearCart($cart->id);
    }
}

function handlePaymentFailed($data, $orderRepo, $transactionRepo) {
    $paymentId = $data['id'] ?? null;
    $metadata = $data['attributes']['metadata'] ?? [];
    $orderId = $metadata['order_id'] ?? null;
    
    if (!$orderId) {
        error_log("PayMongo Webhook: Missing order_id in payment.failed metadata");
        return;
    }
    
    $order = $orderRepo->findById($orderId);
    if (!$order) {
        error_log("PayMongo Webhook: Order not found: " . $orderId);
        return;
    }
    
    // Update order status to cancelled
    $orderRepo->updateStatus($orderId, 'cancelled');
    $orderRepo->updateItemsStatusByOrderId($orderId, 'cancelled');
    
    // Log failed transaction
    $transactionRepo->create([
        'order_id' => $orderId,
        'transaction_type' => 'payment',
        'transaction_reference' => $paymentId,
        'amount' => $order->total_amount,
        'status' => 'failed',
        'description' => 'Payment failed via webhook',
        'payment_method' => $order->payment_method,
        'raw_response' => $data
    ]);
    
    error_log("PayMongo Webhook: Payment failed for order: " . $orderId);
}

function handleSourceCancelled($data, $orderRepo, $transactionRepo) {
    $sourceId = $data['id'] ?? null;
    
    if (!$sourceId) {
        error_log("PayMongo Webhook: Missing source ID in source.cancelled");
        return;
    }
    
    // Find order by source ID
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT * FROM orders WHERE paymongo_payment_intent_id = :source_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':source_id' => $sourceId]);
    $order = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$order) {
        error_log("PayMongo Webhook: Order not found for cancelled source: " . $sourceId);
        return;
    }
    
    // Update order status
    $orderRepo->updateStatus($order->id, 'cancelled');
    $orderRepo->updateItemsStatusByOrderId($order->id, 'cancelled');
    
    error_log("PayMongo Webhook: Source cancelled for order: " . $order->id);
}
