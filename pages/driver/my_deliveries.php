<?php
// pages/driver/my_deliveries.php
require_once __DIR__ . '/../../backend/helpers/SessionManager.php';
require_once __DIR__ . '/../../backend/config/Database.php';

$driverId = SessionManager::get('driver_id') ?? 1;
$db = (new Database())->getConnection();

// Fetch one active delivery for this driver + customer coordinates
$query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_full_name, u.phone_number,
                 ua.latitude as customer_lat, ua.longitude as customer_lng, ua.street, ua.barangay, ua.city
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN user_addresses ua ON u.id = ua.user_id
          WHERE o.assigned_driver_id = :driver_id 
          AND o.status = 'out_for_delivery' 
          LIMIT 1";

$stmt = $db->prepare($query);
$stmt->execute(['driver_id' => $driverId]);
$currentDelivery = $stmt->fetch(PDO::FETCH_ASSOC);

// Store coordinates for JS
$customerCoords = $currentDelivery ? [ 'lat' => (float)$currentDelivery['customer_lat'], 'lng' => (float)$currentDelivery['customer_lng'] ] : null;
?>
<div class="container-fluid pb-5 h-100 d-flex flex-column" id="deliveryModuleRoot">
    <div class="d-flex justify-content-between align-items-center mb-2 pt-2">
        <h2 class="h4 fw-bold mb-0">Current Delivery</h2>
        <?php if ($currentDelivery): ?>
            <span class="badge bg-success bg-opacity-10 text-success fw-bold border border-success px-3 py-1">In Progress</span>
        <?php else: ?>
            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold border border-secondary px-3 py-1">Idle</span>
        <?php endif; ?>
    </div>

    <!-- Active Delivery Card -->
    <?php if ($currentDelivery): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3 position-relative">
            <!-- Map Container -->
            <div id="deliveryMap" class="position-relative bg-light" style="height: 45vh; width: 100%;">
                
                <!-- Floating Nav Controls -->
                <div class="map-controls-overlay">
                    <button id="recenterBtn" class="btn btn-white shadow-sm rounded-circle mb-2" title="Recenter">
                        <i class="ph-bold ph-crosshair"></i>
                    </button>
                    <button id="toggleFollowBtn" class="btn btn-primary shadow-sm rounded-circle active" title="Follow Mode">
                        <i class="ph-bold ph-navigation-arrow"></i>
                    </button>
                </div>
            </div>

            <!-- Directions Strip (Populated by JS) -->
            <div id="directionsPanel" class="bg-primary text-white p-2 d-none">
                <div class="d-flex align-items-center gap-3">
                    <div id="stepIcon" class="fs-4"><i class="ph-bold ph-arrow-u-up-right"></i></div>
                    <div class="flex-grow-1">
                        <div id="stepInstruction" class="fw-bold small lh-sm">Calculating route...</div>
                        <div id="stepDistance" class="small opacity-75" style="font-size: 0.7rem;">0 meters</div>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Details Panel -->
            <div class="card-body p-3 d-flex flex-column bg-white shadow-lg" style="position: relative; z-index: 500; margin-top: -15px; border-radius: 20px 20px 0 0;">
                <div class="align-self-center bg-secondary rounded-pill mb-3" style="width: 40px; height: 4px; opacity: 0.2;"></div>
                
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h3 class="h5 fw-bold text-dark mb-0"><?php echo htmlspecialchars($currentDelivery['order_number'] ?? '#'.$currentDelivery['id']); ?></h3>
                        <p class="text-muted small mb-0">Customer: <?php echo htmlspecialchars($currentDelivery['customer_full_name'] ?? 'Guest'); ?></p>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary">₱<?php echo number_format($currentDelivery['total_amount'], 2); ?></div>
                        <div class="badge bg-warning text-dark" style="font-size: 0.65rem;"><?php echo strtoupper($currentDelivery['payment_method']); ?></div>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3 pt-2 border-top">
                    <div class="flex-grow-1">
                         <div class="fw-bold text-dark lh-sm small"><i class="ph-fill ph-map-pin text-danger me-1"></i><?php echo htmlspecialchars($currentDelivery['delivery_address']); ?></div>
                         <small class="text-muted d-block mt-1">Contact: <?php echo htmlspecialchars($currentDelivery['phone_number'] ?? 'N/A'); ?></small>
                    </div>
                    <a href="tel:<?php echo htmlspecialchars($currentDelivery['phone_number'] ?? ''); ?>" class="btn btn-success bg-opacity-10 text-success border-0 rounded-pill px-3 py-2 d-flex align-items-center gap-2">
                        <i class="ph-fill ph-phone fs-5"></i>
                        <span class="fw-bold small">Call</span>
                    </a>
                </div>
                
                <div class="mt-auto pt-2">
                     <form action="../backend/api/driver_complete_order.php" method="POST" id="completeOrderForm">
                         <input type="hidden" name="order_id" value="<?php echo $currentDelivery['id']; ?>">
                         <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm py-3 rounded-3">
                             <i class="ph-bold ph-check-circle me-2"></i> Confirm Delivery
                         </button>
                     </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-5">
            <div class="bg-light rounded-circle p-4 mb-3">
                <i class="ph ph-truck fs-1 text-muted"></i>
            </div>
            <h3 class="h5 fw-bold">No assigned deliveries</h3>
            <p class="text-muted mb-4 small">Pickup an order from the dispatch board to start your trip.</p>
            <a href="?page=available" class="btn btn-primary px-5 py-3 rounded-pill shadow">Dispatch Board</a>
        </div>
    <?php endif; ?>
</div>

<style>
.map-controls-overlay {
    position: absolute;
    bottom: 30px;
    right: 15px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
}
.map-controls-overlay .btn {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    font-size: 1.5rem;
    border-radius: 15px;
}
.btn-white {
    background: white;
    color: #333;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
#directionsPanel {
    background: rgba(0, 122, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 0 0 20px 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.driver-marker {
    width: 55px;
    height: 55px;
    background-image: url('../public/assets/rider.png');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    cursor: pointer;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
    pointer-events: none;
}
.customer-marker {
    font-size: 3rem;
    color: #ff3b30;
    filter: drop-shadow(0 0 10px rgba(255,59,48,0.5));
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
}
</style>

<script>
// Expose destination for the mapping system
window.deliveryData = {
    customer: <?php echo json_encode($customerCoords); ?>,
    orderNumber: "<?php echo $currentDelivery['order_number'] ?? ''; ?>"
};
</script>

<!-- Load the custom MapLibre driver module -->
<script src="../scripts/driver/driver.js"></script>

