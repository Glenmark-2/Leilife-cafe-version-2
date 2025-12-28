<?php
require_once __DIR__ . '/../backend/config/Database.php';
require_once __DIR__ . '/../backend/repositories/OrderRepository.php';
require_once __DIR__ . '/../backend/repositories/SettingsRepository.php';

$orderId = $_GET['order_id'] ?? null;
$order = null;
$error = null;
$settings = null;

try {
    $database = new Database();
    $db = $database->getConnection();

    $settingsRepo = new SettingsRepository();
    $settings = $settingsRepo->getSettings();

    if ($orderId) {
        $orderRepo = new OrderRepository($db);
        $order = $orderRepo->findById($orderId);

        if (!$order) {
            $error = "Order not found.";
        }
    } else {
        $error = "No order ID specified.";
    }
} catch (Exception $e) {
    $error = "Error loading tracking data.";
}

// Status Mapping
$currentStep = 1;
$statusText = 'Queuing';
$isPickup = ($order && $order->delivery_method === 'pickup');

if ($order) {
    $s = strtolower($order->status);

    if ($s == 'pending') {
        $currentStep = 1;
        $statusText = "Queuing";
    } elseif ($s == 'preparing') {
        $currentStep = 2;
        $statusText = "Preparing";
    } elseif ($s == 'ready_for_pickup' || $s == 'out_for_delivery' || $s == 'on_delivery' || $s == 'ready') {
        $currentStep = 3;
        $statusText = $isPickup ? "Ready for Pickup" : "Out for delivery";
    } elseif ($s == 'delivered' || $s == 'completed' || $s == 'picked_up') {
        $currentStep = 4;
        $statusText = $isPickup ? "Picked Up" : "Delivered";
    } elseif ($s == 'cancelled') {
        $currentStep = 0;
        $statusText = "Cancelled";
    }
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
                    <p><?php echo $isPickup ? 'Ready for Pickup' : 'Out for delivery'; ?></p>
                </div>

                <div class="step">
                    <div class="prog-num <?php echo ($currentStep >= 4) ? 'active' : ''; ?>">4</div>
                    <p><?php echo $isPickup ? 'Picked Up' : 'Delivered'; ?></p>
                </div>
            </div>
        <?php else: ?>
            <!-- Order Cancelled Block Removed as per request -->
        <?php endif; ?>

        <!-- ETA + DELIVERY DETAILS -->
        <div class="row justify-content-center align-items-start">

            <!-- LEFT SIDE -->
            <div class="col-12 col-md-6 text-center mb-4 mb-md-0" style="width: fit-content; padding: 50px;">
                <?php if ($currentStep > 0): ?>
                    <p class="eta-title">
                        <?php
                        if ($currentStep == 4) {
                            echo $isPickup ? 'Pick up successful' : 'Delivery successful';
                        } else {
                            echo $isPickup ? 'Estimated pickup time' : 'Estimated time of delivery';
                        }
                        ?>
                    </p>
                    <p class="eta"><?php echo ($currentStep == 4) ? 'Order Completed' : '30 - 45 mins'; ?></p>

                    <div class="product-img-wrapper">
                        <?php if ($currentStep == 4): ?>
                            <img src="/Leilife_2nd/public/assets/success-order.png" alt="Success" class="product-img">
                        <?php elseif ($isPickup): ?>
                            <img src="/Leilife_2nd/public/assets/walk.png" alt="Pickup" class="product-img">
                        <?php else: ?>
                            <img src="/Leilife_2nd/public/assets/motorbike.png" alt="Motorbike" class="product-img">
                        <?php endif; ?>
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

                <p class="section-title text-center"><?php echo $isPickup ? 'Pickup details' : 'Delivery details'; ?></p>

                <div class="dev-details">
                    <!-- Customer/Shop Name -->
                    <div class="detail-item mb-3">
                        <img src="/Leilife_2nd/public/assets/leilife-logo.png" class="icon" style="width: 20px; height: 20px;">
                        <span class="fw-bold">
                            <?php
                            if ($isPickup) {
                                echo htmlspecialchars($settings['store_name'] ?? 'Leilife Cafe & Resto');
                            } else {
                                echo htmlspecialchars($order->customer_name ?? 'Valued Customer');
                            }
                            ?>
                        </span>
                    </div>

                    <!-- Contact Number -->
                    <div class="detail-item mb-3">
                        <img src="/Leilife_2nd/public/assets/white-call.png" class="icon" style="width: 20px; height: 20px; filter: invert(0.5);">
                        <span>
                            <?php
                            if ($isPickup) {
                                echo htmlspecialchars($settings['contact_phone'] ?? '09123456789');
                            } else {
                                echo htmlspecialchars($order->contact_number ?? 'No contact number');
                            }
                            ?>
                        </span>
                    </div>

                    <!-- Address -->
                    <div class="detail-item mb-3">
                        <img src="/Leilife_2nd/public/assets/pin.png" class="icon" style="width: 20px; height: 20px;">
                        <span>
                            <?php
                            if ($isPickup) {
                                echo htmlspecialchars($settings['physical_address'] ?? '123 Coffee Street, Caloocan City');
                            } else {
                                echo htmlspecialchars($order->delivery_address ?? 'No address provided');
                            }
                            ?>
                        </span>
                    </div>

                    <!-- Payment Method -->
                    <div class="detail-item">
                        <img src="/Leilife_2nd/public/assets/credit-card.png" class="icon" style="width: 20px; height: 20px;">
                        <span>
                            <?php
                            if ($order->payment_method === 'cod') {
                                echo ($order->delivery_method === 'pickup') ? 'Cash' : 'Cash on Delivery';
                            } else {
                                echo 'GCash / Online';
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <p class="section-title mt-4 text-center">Order details</p>

                <div class="ord-dtls">
                    <?php foreach ($order->items as $item): ?>
                        <div class="detail-item mb-3 d-flex align-items-center gap-3">
                            <?php
                            $itemImg = $item->product_image ?: 'not_available.png';
                            if (!str_starts_with($itemImg, 'http') && !str_starts_with($itemImg, '/')) {
                                $itemImg = '/Leilife_2nd/public/assets/products/' . $itemImg;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($itemImg); ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #eee; <?php echo ($item->status == 'cancelled') ? 'filter: grayscale(1); opacity: 0.6;' : ''; ?>">

                            <div class="flex-grow-1">
                                <p class="mb-0 fw-bold" style="<?php echo ($item->status == 'cancelled') ? 'text-decoration: line-through; color: #999;' : ''; ?>">
                                    <?php echo htmlspecialchars($item->product_name); ?>
                                </p>
                                <p class="small text-muted mb-0">₱<?php echo number_format($item->price, 2); ?> × <?php echo $item->quantity; ?></p>
                                <?php if ($item->status == 'cancelled'): ?>
                                    <span class="badge bg-danger-subtle text-danger border-danger-subtle border px-2 py-1" style="font-size: 0.7rem;">Cancelled</span>
                                <?php endif; ?>
                            </div>

                            <p class="fw-bold mb-0" style="<?php echo ($item->status == 'cancelled') ? 'text-decoration: line-through; color: #999;' : ''; ?>">
                                ₱<?php echo number_format($item->subtotal, 2); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>

                    <div class="border-top mt-3 pt-3">
                        <?php if ($order->delivery_fee > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Delivery Fee</span>
                                <span>₱<?php echo number_format($order->delivery_fee, 2); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-primary-custom" style="color: #24353A;">₱<?php echo number_format($order->total_amount, 2); ?></span>
                        </div>
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
                                body: JSON.stringify({
                                    order_id: orderId
                                })
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