<?php
// pages/driver/available_orders.php
require_once __DIR__ . '/../../backend/config/Database.php';

$db = (new Database())->getConnection();

// Fetch orders that are 'out_for_delivery' (dispatched from kitchen) and NO assigned_driver_id
$query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.delivery_method = 'delivery' 
          AND o.status = 'out_for_delivery' 
          AND o.assigned_driver_id IS NULL 
          ORDER BY o.created_at ASC";

$stmt = $db->prepare($query);
$stmt->execute();
$availableOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid pb-5">
    <div class="mb-4 pt-2">
        <h2 class="h4 fw-bold mb-1">Dispatch Board</h2>
        <p class="text-muted small">Accept pending orders for delivery.</p>
    </div>

    <!-- List -->
    <div class="d-flex flex-column gap-3">
        <?php if (empty($availableOrders)): ?>
            <div class="text-center py-5 text-muted">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i class="ph ph-check-circle fs-2"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">All cleared!</h3>
                <p class="small">No pending orders to deliver right now.</p>
            </div>
        <?php else: ?>
            <?php foreach ($availableOrders as $order): ?>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                        <span class="badge bg-light text-dark border fw-medium"><?php echo htmlspecialchars($order['order_number'] ?? '#'.$order['id']); ?></span>
                        <span class="fw-bold text-primary">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="card-body px-3 pb-3 pt-1">
                        <div class="mb-3">
                            <h3 class="h6 fw-bold mb-0 text-dark"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></h3>
                            <div class="d-flex align-items-start gap-2 mt-1">
                                <i class="ph-fill ph-map-pin text-danger mt-1"></i>
                                <p class="small text-muted mb-0 lh-sm"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3 bg-light rounded-3 p-2 mx-0">
                            <div class="col-6 text-center border-end">
                                <div class="small text-muted" style="font-size: 0.7rem;">PAYMENT</div>
                                <div class="fw-bold fs-6 text-dark"><?php echo strtoupper($order['payment_method']); ?></div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="small text-muted" style="font-size: 0.7rem;">ITEMS</div>
                                <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($order['status']); ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <form action="../backend/api/driver_accept_order.php" method="POST" class="w-100">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold text-white shadow-sm shimmer-effect">
                                    Accept Delivery
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* Subtle shimmer animation for the primary action to encourage accepting */
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.shimmer-effect {
    background: linear-gradient(105deg, var(--primary-color) 20%, #bcaea0 50%, var(--primary-color) 80%);
    background-size: 200% 100%;
    animation: shimmer 3s infinite;
}
</style>

