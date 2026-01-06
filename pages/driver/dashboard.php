<?php
// pages/driver/dashboard.php
require_once __DIR__ . '/../../backend/helpers/SessionManager.php';
require_once __DIR__ . '/../../backend/config/Database.php';

$driverId = SessionManager::get('driver_id') ?? 1; // Fallback to 1 for testing if not logged in
$driverName = SessionManager::get('user_name') ?? "Jericho"; 

$db = (new Database())->getConnection();

// Fetch Real Stats
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE assigned_driver_id = :driver_id AND DATE(created_at) = :today AND status = 'delivered'");
$stmt->execute(['driver_id' => $driverId, 'today' => $today]);
$deliveriesToday = $stmt->fetchColumn();

// Fetch Recent Activity (last 5 orders assigned to this driver)
$stmt = $db->prepare("SELECT o.*, u.first_name, u.last_name 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.assigned_driver_id = :driver_id 
                      ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute(['driver_id' => $driverId]);
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$todayDate = date("l, d M");
?>

<div class="container-fluid pb-5">
    <!-- Welcome Section -->
    <div class="row mb-4 pt-2 welcome-sectionnn">
        <div class="col-12">
            <p class="text-muted mb-1 fw-medium"><?php echo $todayDate; ?></p>
            <h1 class="fw-bold text-dark" style="font-size: 1.75rem;">Good Morning, <?php echo htmlspecialchars($driverName); ?>! 👋</h1>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="row g-3 mb-4">
        <!-- Deliveries -->
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="text-primary fs-4 mb-2"><i class="ph-fill ph-package"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1 mb-1 text-dark"><?php echo $deliveriesToday; ?></div>
                        <div class="small text-muted fw-semibold">Delivered Today</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Online (Placeholder or actual if tracked) -->
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="text-info fs-4 mb-2"><i class="ph-fill ph-clock"></i></div>
                    <div>
                        <div class="fw-bold fs-4 lh-1 mb-1 text-dark">Active</div>
                        <div class="small text-muted fw-semibold">Status</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <a href="?page=available" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2 rounded-3 shadow-sm">
                <i class="ph-bold ph-magnifying-glass"></i> Find New Delivery Orders
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
        <h3 class="h5 fw-bold mb-0">Your Recent Deliveries</h3>
        <a href="?page=deliveries" class="text-decoration-none fw-semibold">View All</a>
    </div>
    
    <div class="card border-0 shadow-sm overflow-hidden rounded-4">
        <div class="list-group list-group-flush">
            <?php if (empty($recentActivities)): ?>
                <div class="list-group-item p-4 text-center text-muted">
                    No recent delivery activity found.
                </div>
            <?php else: ?>
                <?php foreach ($recentActivities as $activity): ?>
                    <div class="list-group-item p-3 border-bottom-0 border-top d-flex align-items-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center me-3" 
                             style="width: 44px; height: 44px; background-color: <?php echo $activity['status'] === 'delivered' ? '#ecfdf5' : '#fef3c7'; ?>; color: <?php echo $activity['status'] === 'delivered' ? '#059669' : '#d97706'; ?>;">
                             <i class="ph-fill <?php echo $activity['status'] === 'delivered' ? 'ph-check-circle' : 'ph-truck'; ?> fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark mb-0"><?php echo htmlspecialchars($activity['order_number']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($activity['status']); ?> • <?php echo date('h:i A', strtotime($activity['created_at'])); ?></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-dark">₱<?php echo number_format($activity['total_amount'], 2); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($activity['first_name']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

