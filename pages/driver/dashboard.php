<?php
// pages/driver/dashboard.php

// Mock Data
$driverName = "Jericho"; 
$todayDate = date("l, d M");
$stats = [
    'deliveries_today' => 12,
    'earnings_today' => 850.50,
    'rating' => 4.9,
    'time_online' => '4h 20m'
];

$recentActivities = [
    ['id' => 'Ord-9921', 'type' => 'delivery', 'status' => 'completed', 'time' => '10:30 AM', 'amount' => 120.00, 'location' => 'Bonifacio High St.'],
    ['id' => 'Ord-9920', 'type' => 'delivery', 'status' => 'completed', 'time' => '09:45 AM', 'amount' => 85.00, 'location' => 'Uptown Mall'],
    ['id' => 'Tip-002', 'type' => 'tip', 'status' => 'completed', 'time' => '09:45 AM', 'amount' => 50.00, 'location' => 'Customer Tip'],
];
?>

<div class="container-fluid pb-5">
    <!-- Welcome Section -->
    <div class="row mb-4 pt-2 welcome-sectionnn">
        <div class="col-12">
            <p class="text-muted mb-1 fw-medium"><?php echo $todayDate; ?></p>
            <h1 class="fw-bold text-dark" style="font-size: 1.75rem;">Good Morning, <?php echo htmlspecialchars($driverName); ?>! 👋</h1>
        </div>
    </div>

    <!-- Performance / Incentive Card -->
    <div class="card border-0 text-white mb-4 performance-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold">Weekly Goal</span>
                <span class="fw-bold">12/20</span>
            </div>
            <div class="progress mb-3" style="height: 10px; background-color: rgba(255,255,255,0.2);">
                <div class="progress-bar bg-success" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <p class="small mb-0 opacity-75">Complete 8 more deliveries to earn a ₱500 bonus!</p>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="row g-3 mb-4">
        <!-- Earnings (Highlight) -->
        <div class="col-6">
            <div class="card border-0 h-100 stat-card highlight">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="fs-4 mb-2"><i class="ph-fill ph-wallet"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1 mb-1">₱<?php echo number_format($stats['earnings_today'], 2); ?></div>
                        <div class="small fw-semibold opacity-75">Earnings</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliveries -->
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="text-primary fs-4 mb-2"><i class="ph-fill ph-package"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1 mb-1 text-dark"><?php echo $stats['deliveries_today']; ?></div>
                        <div class="small text-muted fw-semibold">Delivered</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating -->
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="text-warning fs-4 mb-2"><i class="ph-fill ph-star"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1 mb-1 text-dark"><?php echo $stats['rating']; ?></div>
                        <div class="small text-muted fw-semibold">Rating</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Online -->
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="text-info fs-4 mb-2"><i class="ph-fill ph-clock"></i></div>
                    <div>
                        <div class="fw-bold fs-4 lh-1 mb-1 text-dark"><?php echo $stats['time_online']; ?></div>
                        <div class="small text-muted fw-semibold">Hours Online</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <a href="?page=available" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2 rounded-3 shadow-sm">
                <i class="ph-bold ph-magnifying-glass"></i> Find Orders
            </a>
        </div>
        <div class="col-6">
            <button class="btn btn-light w-100 py-3 d-flex align-items-center justify-content-center gap-2 rounded-3 border">
                <i class="ph-bold ph-chat-circle-text"></i> Support
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
        <h3 class="h5 fw-bold mb-0">Recent Activity</h3>
        <a href="?page=deliveries" class="text-decoration-none fw-semibold">View All</a>
    </div>
    
    <div class="card border-0 shadow-sm overflow-hidden rounded-4">
        <div class="list-group list-group-flush">
            <?php foreach ($recentActivities as $activity): ?>
                <div class="list-group-item p-3 border-bottom-0 border-top d-flex align-items-center">
                    <div class="rounded-3 d-flex align-items-center justify-content-center me-3" 
                         style="width: 44px; height: 44px; background-color: <?php echo $activity['status'] === 'completed' ? '#ecfdf5' : '#f3f4f6'; ?>; color: <?php echo $activity['status'] === 'completed' ? '#059669' : '#6b7280'; ?>;">
                        <?php if ($activity['type'] === 'tip'): ?>
                            <i class="ph-fill ph-coins fs-5"></i>
                        <?php else: ?>
                            <i class="ph-fill ph-check-circle fs-5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark mb-0"><?php echo htmlspecialchars($activity['location']); ?></div>
                        <div class="small text-muted"><?php echo htmlspecialchars($activity['time']); ?> • <?php echo htmlspecialchars($activity['id']); ?></div>
                    </div>
                    <div class="fw-bold text-dark">+₱<?php echo number_format($activity['amount'], 0); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
