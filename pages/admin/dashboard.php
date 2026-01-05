<?php
require_once __DIR__ . '/../../backend/helpers/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../../.env');
$pusherKey = getenv('PUSHER_KEY');
$pusherCluster = getenv('PUSHER_CLUSTER') ?: 'ap1';
?>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    window.pusherConfig = {
        key: '<?php echo $pusherKey; ?>',
        cluster: '<?php echo $pusherCluster; ?>'
    };
</script>
<link rel="stylesheet" href="/Leilife_2nd/css/admin/dashboard.css">
<div class="dashboard-wrapper">
    <div class="title-content">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Dashboard</p>
    </div>

    <!-- Dashboard Stats Grid -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Pending Orders</h3>
                <p class="stat-value text-pending">0</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Preparing</h3>
                <p class="stat-value text-preparing">0</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Ready for Delivery</h3>
                <p class="stat-value text-ready">0</p>
            </div>
        </div>


        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Delivered Today</h3>
                <p class="stat-value text-delivered">0</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Picked up Today</h3>
                <p class="stat-value text-picked-up">0</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Cancelled</h3>
                <p class="stat-value text-cancelled">0</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Active Admins</h3>
                <p class="stat-value text-admins">0</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info center-text">
                <h3>Active Drivers</h3>
                <p class="stat-value text-drivers">0</p>
            </div>
        </div>
    </div>

    <div class="dashboard-row">
        <!-- Recent Orders Section -->
        <div class="dashboard-section recent-orders w-100 h-100 d-flex flex-column">
            <div class="section-header">
                <h2>Recent Orders</h2>

                <div class="sort-controls">
                    <select id="sortOrders" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                        <option value="date_desc">Newest First</option>
                        <option value="date_asc">Oldest First</option>
                        <option value="status">Status</option>
                        <option value="total_desc">Highest Total</option>
                        <option value="total_asc">Lowest Total</option>
                        <option value="type_pickup">Pickup Only</option>
                        <option value="type_delivery">Delivery Only</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="dashboard-table table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Time</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="6" class="text-center">Loading orders...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalOrderId">
                <p>Updating status for Order #<span id="displayOrderId" class="fw-bold"></span></p>

                <div class="status-btn-grid">
                    <button class="btn btn-outline-primary" onclick="updateStatus('pending')">Set Pending</button>
                    <button class="btn btn-outline-warning" onclick="updateStatus('preparing')">Set Preparing</button>
                    <button id="btnReadyForPickup" class="btn btn-outline-info" onclick="updateStatus('ready_for_pickup')">Set Ready</button>
                    <button id="btnOutForDelivery" class="btn btn-outline-secondary" onclick="updateStatus('out_for_delivery')">Set Out</button>
                    <button id="btnPickedUp" class="btn btn-outline-success" onclick="updateStatus('picked_up')">Set Picked Up</button>
                    <button id="btnDelivered" class="btn btn-outline-success" onclick="updateStatus('delivered')">Set Delivered</button>
                    <button class="btn btn-outline-danger w-100" style="grid-column: span 2;" onclick="updateStatus('cancelled')">Set Cancelled</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="orderDetailsModalLabel">Order Details - <span id="detailOrderNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="orderDetailsBody">
                            <!-- Items will be loaded here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Delivery Fee:</th>
                                <td id="detailDeliveryFee">₱0.00</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total Amount:</th>
                                <th id="detailTotalAmount">₱0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">Are you sure you want to change the status?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="/Leilife_2nd/scripts/admin/dashboard.js"></script>