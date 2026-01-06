<?php
// pages/driver/profile.php
require_once __DIR__ . '/../../backend/helpers/SessionManager.php';
require_once __DIR__ . '/../../backend/config/Database.php';

$driverId = SessionManager::get('driver_id') ?? 1;
$driverName = SessionManager::get('user_name') ?? "Jericho Driver";
$driverEmail = SessionManager::get('user_email') ?? "driver@leilife.com";

$db = (new Database())->getConnection();

// Fetch Trips Count (Total Delivered)
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE assigned_driver_id = :driver_id AND status = 'delivered'");
$stmt->execute(['driver_id' => $driverId]);
$totalTrips = $stmt->fetchColumn();

// Fetch Start Year (from staffs table)
$stmt = $db->prepare("SELECT created_at FROM staffs s JOIN drivers d ON s.staff_id = d.staff_id WHERE d.driver_id = :driver_id");
$stmt->execute(['driver_id' => $driverId]);
$createdAt = $stmt->fetchColumn();
$yearsActive = floor((time() - strtotime($createdAt)) / (365 * 24 * 60 * 60));
if ($yearsActive < 1) $yearsActive = "< 1";
?>
<div class="container-fluid pb-5">
    
    <!-- Profile Header Card -->
    <div class="card border-0 shadow-sm rounded-4 text-center p-4 mb-4 mt-2">
        <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center text-secondary border border-3 border-white shadow-sm" 
             style="width: 80px; height: 80px; background-color: #f3f4f6; position:relative; box-shadow: 0 0 0 2px var(--primary-color);">
             <i class="ph-fill ph-user fs-1"></i>
        </div>
        <h2 class="h5 fw-bold mb-1 text-dark"><?php echo htmlspecialchars($driverName); ?></h2>
        <p class="small text-muted mb-4"><?php echo htmlspecialchars($driverEmail); ?></p>
        
        <div class="d-flex justify-content-center gap-5 border-top pt-3">
            <div class="text-center">
                <span class="d-block fw-bold fs-5 text-dark"><?php echo $totalTrips; ?></span>
                <span class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Trips</span>
            </div>
            <div class="text-center">
                <span class="d-block fw-bold fs-5 text-dark"><?php echo $yearsActive; ?></span>
                <span class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Years Active</span>
            </div>
        </div>
    </div>

    <!-- Settings Groups -->
    <div class="mb-4">
        <h6 class="text-muted fw-bold small ms-2 mb-2 text-uppercase">Account Settings</h6>
        <div class="list-group rounded-4 shadow-sm border-0 overflow-hidden">
            <a href="#" class="list-group-item list-group-item-action p-3 border-0 border-bottom d-flex align-items-center">
                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                    <i class="ph-fill ph-user-gear text-secondary fs-5"></i>
                </div>
                <span class="fw-medium text-dark flex-grow-1">Account Information</span>
                <i class="ph ph-caret-right text-muted"></i>
            </a>
            <a href="#" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center">
                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                    <i class="ph-fill ph-bell text-secondary fs-5"></i>
                </div>
                <span class="fw-medium text-dark flex-grow-1">Notifications</span>
                <i class="ph ph-caret-right text-muted"></i>
            </a>
        </div>
    </div>

    <form method="POST" action="../backend/auth/driver_logout.php">
        <button type="submit" class="btn btn-danger bg-opacity-10 text-danger border-0 w-100 py-3 rounded-4 fw-bold d-flex align-items-center justify-content-center gap-2">
            <i class="ph-bold ph-sign-out"></i> Log Out
        </button>
    </form>
</div>

