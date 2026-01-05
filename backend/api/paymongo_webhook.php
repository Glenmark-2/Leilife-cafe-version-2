<?php
// Headers
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../services/PayMongoService.php';

// Instantiate dependencies
$database = new Database();
$db = $database->getConnection();
$orderRepo = new OrderRepository($db);
$transactionRepo = new TransactionRepository($db);
$cartRepo = new CartRepository($db);
$payMongo = new PayMongoService();

// Retrieve Webhook Secret from ENV
$webhookSecret = $_ENV['PAYMONGO_WEBHOOK_SECRET'] ?? getenv('PAYMONGO_WEBHOOK_SECRET');

if (!$webhookSecret) {
    // If not set, we can't verify. For safety in production, we should abort.
    // However, for initial setup, we might log it.
    error_log("PayMongo Webhook Secret is missing!");
}

// Get the payload and signature header
$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

// Verify Signature (Recommended for Production)
if ($webhookSecret && !$payMongo->verifyWebhookSignature($payload, $signatureHeader, $webhookSecret)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$data = json_decode($payload, true);
$event = $data['data']['attributes']['type'] ?? '';
$resourceData = $data['data']['attributes']['data'] ?? [];

error_log("PayMongo Webhook Received: " . $event);

switch ($event) {
    case 'source.chargeable':
        $sourceId = $resourceData['id'];
        $amount = $resourceData['attributes']['amount'];
        $metadata = $resourceData['attributes']['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if ($orderId) {
            $order = $orderRepo->findById($orderId);
            if ($order && $order->payment_status === 'unpaid') {
                // Charge the source
                $description = "Payment for Order #" . $order->order_number . " (Webhook)";
                $paymentResult = $payMongo->createPayment($sourceId, $amount, 'PHP', $description, ['order_id' => $orderId]);

                if ($paymentResult['success']) {
                    // Update Order
                    $orderRepo->updatePaymentStatus($orderId, 'paid');
                    $orderRepo->updateStatus($orderId, 'pending'); // Or 'preparing'
                    $orderRepo->updateItemsStatusByOrderId($orderId, 'pending');

                    // Log Success
                    $transactionRepo->create([
                        'order_id' => $orderId,
                        'transaction_type' => 'payment',
                        'transaction_reference' => $paymentResult['data']['id'],
                        'amount' => $amount / 100,
                        'status' => 'success',
                        'description' => 'Payment successfully charged via Webhook.',
                        'payment_method' => $order->payment_method,
                        'raw_response' => $paymentResult
                    ]);

                    // Clear Cart
                    $cart = $cartRepo->getCartByUserId($order->user_id);
                    if ($cart) {
                        $cartRepo->clearCart($cart->id);
                    }
                } else {
                    // Log Failure
                    $transactionRepo->create([
                        'order_id' => $orderId,
                        'transaction_type' => 'payment',
                        'transaction_reference' => $sourceId,
                        'amount' => $amount / 100,
                        'status' => 'failed',
                        'description' => 'Failed to charge source via Webhook: ' . ($paymentResult['error'] ?? ''),
                        'payment_method' => $order->payment_method,
                        'raw_response' => $paymentResult
                    ]);
                }
            }
        }
        break;

    case 'payment.paid':
        // Optional: Update status if not already updated
        $sourceData = $resourceData['attributes']['source'] ?? [];
        $metadata = $resourceData['attributes']['metadata'] ?? []; // Payments also have metadata if we pass it
        // Note: When creating payment from source, PayMongo doesn't automatically copy source metadata to payment metadata
        // unless we specify it in createPayment.
        break;
}

// Always return 200 to PayMongo
http_response_code(200);
echo json_encode(['status' => 'ok']);
