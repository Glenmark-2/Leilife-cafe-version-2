<?php
// pages/driver/available_orders.php

// Mock Data for a single store dispatch system
$availableOrders = [
    [
        'order_no' => '#ORD-001',
        'customer_name' => 'Maria Santos',
        'customer_address' => 'Unit 402, High Street South, BGC',
        'distance' => '2.5 km',
        'est_time' => '15 mins',
        'payment_method' => 'COD',
        'total_amount' => '450.00',
        'items_count' => 2
    ],
    [
        'order_no' => '#ORD-005',
        'customer_name' => 'John Doe',
        'customer_address' => '123 Mahogany Place, Taguig',
        'distance' => '5.2 km',
        'est_time' => '30 mins',
        'payment_method' => 'Paid (Gcash)',
        'total_amount' => '1,200.00',
        'items_count' => 8
    ]
];
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
                        <span class="badge bg-light text-dark border fw-medium"><?php echo htmlspecialchars($order['order_no']); ?></span>
                        <span class="fw-bold text-primary">₱<?php echo htmlspecialchars($order['total_amount']); ?></span>
                    </div>
                    <div class="card-body px-3 pb-3 pt-1">
                        <div class="mb-3">
                            <h3 class="h6 fw-bold mb-0 text-dark"><?php echo htmlspecialchars($order['customer_name']); ?></h3>
                            <div class="d-flex align-items-start gap-2 mt-1">
                                <i class="ph-fill ph-map-pin text-danger mt-1"></i>
                                <p class="small text-muted mb-0 lh-sm"><?php echo htmlspecialchars($order['customer_address']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3 bg-light rounded-3 p-2 mx-0">
                            <div class="col-4 text-center border-end">
                                <div class="small text-muted" style="font-size: 0.7rem;">DISTANCE</div>
                                <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($order['distance']); ?></div>
                            </div>
                            <div class="col-4 text-center border-end">
                                <div class="small text-muted" style="font-size: 0.7rem;">EST. TIME</div>
                                <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($order['est_time']); ?></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted" style="font-size: 0.7rem;">PAYMENT</div>
                                <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary w-100 py-2 fw-bold text-white shadow-sm shimmer-effect">
                                Accept Delivery
                            </button>
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
