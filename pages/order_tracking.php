<?php
require_once __DIR__ . '/../backend/config/Database.php';
require_once __DIR__ . '/../backend/repositories/OrderRepository.php';

$orderId = $_GET['order_id'] ?? null;
$order = null;
$error = null;

if ($orderId) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $orderRepo = new OrderRepository($db);
        $order = $orderRepo->findById($orderId);
        
        if (!$order) {
            $error = "Order not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading order.";
    }
} else {
    $error = "No order ID specified.";
}

// Status Mapping
$statusSteps = [
    'pending' => 1,
    'preparing' => 2,
    'ready' => 3, // Assuming 'ready' implies ready for pickup/delivery usually
    'out_for_delivery' => 3, 
    'delivered' => 4,
    'completed' => 4
];

$currentStep = 1;
$statusText = 'Queuing'; // Default
if ($order) {
    // Normalize status
    $s = strtolower($order->status);
    // Rough mapping
    if ($s == 'pending') { $currentStep = 1; $statusText = "Queuing"; }
    elseif ($s == 'preparing') { $currentStep = 2; $statusText = "Preparing"; }
    elseif ($s == 'ready' || $s == 'out_for_delivery' || $s == 'on_delivery') { $currentStep = 3; $statusText = "Out for delivery"; }
    elseif ($s == 'delivered' || $s == 'completed') { $currentStep = 4; $statusText = "Delivered"; }
    elseif ($s == 'cancelled') { $currentStep = 0; $statusText = "Cancelled"; }
}
?>

<div class="container text-center mt-3">

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <a href="index.php?page=home" class="btn btn-primary-custom">Back to Home</a>
    <?php elseif ($order): ?>

    <p class="order-number">Your Order #<?php echo htmlspecialchars($order->order_number ?? $order->id); ?></p>

    <!-- PROGRESS STEPS -->
    <?php if ($currentStep > 0): ?>
    <div class="progress-steps d-flex justify-content-between mt-4">
        <div class="step">
            <div class="prog-num <?php echo ($currentStep >= 1) ? 'active' : ''; ?>">1</div>
            <p>Queuing</p>
        </div>

        <div class="step">
            <div class="prog-num <?php echo ($currentStep >= 2) ? 'active' : ''; ?>">2</div>
            <p>Preparing</p>
        </div>

        <div class="step">
            <div class="prog-num <?php echo ($currentStep >= 3) ? 'active' : ''; ?>">3</div>
            <p>Out for delivery</p>
        </div>

        <div class="step">
            <div class="prog-num <?php echo ($currentStep >= 4) ? 'active' : ''; ?>">4</div>
            <p>Delivered</p>
        </div>
    </div>
    <?php else: ?>
        <!-- Order Cancelled Block Removed as per request -->
    <?php endif; ?>

    <!-- ETA + DELIVERY DETAILS -->
    <div class="row justify-content-center align-items-start mt-5 g-4">

        <!-- LEFT SIDE -->
        <div class="col-12 col-md-6 text-center mb-4 mb-md-0">
            <?php if ($currentStep > 0): ?>
                <p class="eta-title">Estimated time of delivery</p>
                <p class="eta">30 - 45 mins</p>

                <div class="product-img-wrapper">
                    <img src="/Leilife_2nd/public/assets/motorbike.png" alt="Motorbike" class="product-img">
                </div>
            <?php else: ?>
                <p class="eta-title text-danger">Your order has been cancelled</p>
                
                <div class="product-img-wrapper">
                    <img src="/Leilife_2nd/public/assets/cancel-order.png" alt="Cancelled" class="product-img">
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-12 col-md-6 delivery-col">

            <p class="section-title text-center">Delivery details</p>

            <div class="dev-details">
                <div class="detail-item">
                    <img src="/Leilife_2nd/public/assets/pin.png" class="icon">
                    <span><?php echo htmlspecialchars($order->delivery_address ?? 'No address provided'); ?></span>
                </div>

                <div class="detail-item mt-3">
                    <img src="/Leilife_2nd/public/assets/credit-card.png" class="icon">
                    <span><?php echo htmlspecialchars($order->payment_method); ?></span>
                </div>
            </div>

            <p class="section-title mt-4 text-center">Order details</p>

            <div class="ord-dtls">
                <?php foreach ($order->items as $item): ?>
                <div class="detail-item mb-2">
                    <p style="<?php echo ($item->status == 'cancelled') ? 'text-decoration: line-through; color: #999;' : ''; ?>">
                        <?php echo $item->quantity; ?> × <?php echo htmlspecialchars($item->product_name); ?> — ₱<?php echo number_format($item->subtotal, 2); ?>
                    </p>
                    <?php if ($item->status == 'cancelled'): ?>
                        <p class="text-danger small ms-3 fst-italic" style="margin-top: -5px; text-decoration: line-through;">Cancelled Item</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if ($order->delivery_fee > 0): ?>
                <div class="detail-item mt-2 border-top pt-2">
                    <p>Delivery Fee: ₱<?php echo number_format($order->delivery_fee, 2); ?></p>
                </div>
                <?php endif; ?>

                <div class="detail-item mt-3 border-top pt-2">
                    <p class="fw-bold">Total: ₱<?php echo number_format($order->total_amount, 2); ?></p>
                </div>
            </div>

            <div class="text-center mt-4">
                <?php if ($order->status == 'pending'): ?>
                    <button class="btn btn-outline-danger" id="cancelOrderBtn" onclick="cancelOrder(<?php echo $order->id; ?>)">Cancel Order</button>
                <?php elseif ($order->status == 'cancelled'): ?>
                    <a href="index.php?page=menu" class="btn btn-primary-custom">Go to Menu</a>
                <?php else: ?>
                    <button class="btn btn-primary-custom" disabled>Cannot Cancel</button>
                <?php endif; ?>
            </div>
            
            <script>
            function cancelOrder(orderId) {
                if (!confirm("Are you sure you want to cancel this order?")) {
                    return;
                }
                
                const btn = document.getElementById('cancelOrderBtn');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = "Cancelling...";
                
                fetch('/Leilife_2nd/backend/api/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Order cancelled successfully.");
                        window.location.reload();
                    } else {
                        alert(data.message || "Failed to cancel order.");
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred. Please try again.");
                    btn.disabled = false;
                    btn.innerText = originalText;
                });
            }
            </script>
            </div>

        </div>

    </div>

    <?php endif; ?>
</div>
