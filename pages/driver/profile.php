<?php
// pages/driver/profile.php
$driverName = "Jericho";
$driverEmail = "driver@leilife.com";
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
                <span class="d-block fw-bold fs-5 text-dark">4.9</span>
                <span class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Rating</span>
            </div>
            <div class="text-center">
                <span class="d-block fw-bold fs-5 text-dark">1.2k</span>
                <span class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Trips</span>
            </div>
            <div class="text-center">
                <span class="d-block fw-bold fs-5 text-dark">2</span>
                <span class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Years</span>
            </div>
        </div>
    </div>

    <!-- Settings Groups -->
    <div class="mb-4">
        <h6 class="text-muted fw-bold small ms-2 mb-2 text-uppercase">Account</h6>
        <div class="list-group rounded-4 shadow-sm border-0 overflow-hidden">
            <a href="#" class="list-group-item list-group-item-action p-3 border-0 border-bottom d-flex align-items-center">
                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                    <i class="ph-fill ph-user-gear text-secondary fs-5"></i>
                </div>
                <span class="fw-medium text-dark flex-grow-1">Account Information</span>
                <i class="ph ph-caret-right text-muted"></i>
            </a>
            <a href="#" class="list-group-item list-group-item-action p-3 border-0 border-bottom d-flex align-items-center">
                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                    <i class="ph-fill ph-bank text-secondary fs-5"></i>
                </div>
                <span class="fw-medium text-dark flex-grow-1">Payment Details</span>
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

    <div class="mb-4">
        <h6 class="text-muted fw-bold small ms-2 mb-2 text-uppercase">Support</h6>
        <div class="list-group rounded-4 shadow-sm border-0 overflow-hidden">
             <a href="#" class="list-group-item list-group-item-action p-3 border-0 border-bottom d-flex align-items-center">
                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                    <i class="ph-fill ph-question text-secondary fs-5"></i>
                </div>
                <span class="fw-medium text-dark flex-grow-1">Help Center</span>
                <i class="ph ph-caret-right text-muted"></i>
            </a>
             <a href="#" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center">
                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                    <i class="ph-fill ph-shield-check text-secondary fs-5"></i>
                </div>
                <span class="fw-medium text-dark flex-grow-1">Terms & Privacy</span>
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
