<?php

class PayMongoService
{
    private $secretKey;
    private $baseUrl = 'https://api.paymongo.com/v1';

    public function __construct()
    {
        // Load keys from environment variables or config
        // Assuming they are available via getenv or $_ENV
        $this->secretKey = $_ENV['PAYMONGO_SECRET_KEY'] ?? getenv('PAYMONGO_SECRET_KEY');

        if (!$this->secretKey) {
            // Fallback for dev/testing if not in env, remove in production
            // Ideally, throw error or log warning
            error_log("PayMongo Secret Key is missing!");
        }
    }

    private function request($method, $endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $decoded['data'] ?? $decoded];
        } else {
            return [
                'success' => false,
                'error' => $decoded['errors'][0]['detail'] ?? 'Unknown error from PayMongo',
                'http_code' => $httpCode,
                'response' => $decoded
            ];
        }
    }

    public function createSource($amount, $redirectSuccess, $redirectFailed, $currency = 'PHP', $type = 'gcash', $metadata = [])
    {
        // Amount inside PayMongo is in centavos
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int) $amount, // Ensure integer
                    'currency' => $currency,
                    'type' => $type,
                    'redirect' => [
                        'success' => $redirectSuccess,
                        'failed' => $redirectFailed
                    ],
                    'metadata' => $metadata
                ]
            ]
        ];

        return $this->request('POST', '/sources', $payload);
    }

    public function createPayment($sourceId, $amount, $currency = 'PHP', $description = 'Order Payment', $metadata = [])
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int) $amount,
                    'currency' => $currency,
                    'description' => $description,
                    'source' => [
                        'id' => $sourceId,
                        'type' => 'source'
                    ],
                    'metadata' => $metadata
                ]
            ]
        ];

        return $this->request('POST', '/payments', $payload);
    }

    public function retrieveSource($id)
    {
        return $this->request('GET', '/sources/' . $id);
    }

    public function createRefund($paymentId, $amount, $reason = 'requested_by_customer', $notes = 'Refund')
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int) $amount,
                    'payment_id' => $paymentId,
                    'reason' => $reason,
                    'notes' => $notes
                ]
            ]
        ];
        return $this->request('POST', '/refunds', $payload);
    }

    public function retrievePayment($id)
    {
        return $this->request('GET', '/payments/' . $id);
    }

    // Simple logger
    public function log($db, $orderId, $action, $payload, $response)
    {
        try {
            $stmt = $db->prepare("INSERT INTO paymongo_logs (order_id, action, payload, response) VALUES (:order_id, :action, :payload, :response)");
            $stmt->execute([
                ':order_id' => $orderId,
                ':action' => $action,
                ':payload' => is_string($payload) ? $payload : json_encode($payload),
                ':response' => is_string($response) ? $response : json_encode($response)
            ]);
        } catch (Exception $e) {
            error_log("Failed to log PayMongo action: " . $e->getMessage());
        }
    }

    public function verifyWebhookSignature($payload, $signatureHeader, $webhookSecret)
    {
        if (empty($signatureHeader) || empty($webhookSecret)) {
            return false;
        }

        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $signature = null;

        foreach ($parts as $part) {
            if (strpos($part, 't=') === 0) {
                $timestamp = substr($part, 2);
            } elseif (strpos($part, 'v1=') === 0) {
                $signature = substr($part, 3);
            }
        }

        if (!$timestamp || !$signature) {
            return false;
        }

        $signedPayload = $timestamp . "." . $payload;
        $computedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

        return hash_equals($signature, $computedSignature);
    }
}
