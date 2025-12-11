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
            <p class="stat-value text-pending">8</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
             <h3>Preparing</h3>
             <p class="stat-value text-preparing">3</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Ready for Delivery</h3>
            <p class="stat-value text-ready">5</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Delivered Today</h3>
            <p class="stat-value text-delivered">120</p>
        </div>
    </div>
    
     <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Cancelled</h3>
            <p class="stat-value text-cancelled">2</p>
        </div>
    </div>
    
     <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Active Admins</h3>
            <p class="stat-value text-primary">3</p>
        </div>
    </div>
    
     <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Active Drivers</h3>
            <p class="stat-value text-primary">5</p>
        </div>
    </div>
</div>

<div class="dashboard-row">
    <!-- Recent Orders Section -->
    <div class="dashboard-section recent-orders w-100">
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
        
        <div class="table-responsive">
            <table class="dashboard-table table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#1024</td>
                        <td>John Doe</td>
                        <td><span class="badge bg-secondary">Delivery</span></td>
                        <td>
                            <button class="status-badge status-preparing btn btn-sm " onclick="openStatusModal('1024', 'preparing')">
                                Preparing
                            </button>
                        </td>
                        <td>₱450.00</td>
                        <td>10:05 AM</td>
                    </tr>
                    <tr>
                        <td>#1023</td>
                        <td>Jane Smith</td>
                         <td><span class="badge bg-info text-dark">Pickup</span></td>
                        <td>
                            <button class="status-badge status-delivered btn btn-sm" onclick="openStatusModal('1023', 'delivered')">
                                Delivered
                            </button>
                        </td>
                        <td>₱1,200.00</td>
                        <td>09:45 AM</td>
                    </tr>
                    <tr>
                        <td>#1022</td>
                        <td>Mike Ross</td>
                        <td><span class="badge bg-secondary">Delivery</span></td>
                        <td>
                             <button class="status-badge status-pending btn btn-sm" onclick="openStatusModal('1022', 'pending')">
                                Pending
                            </button>
                        </td>
                        <td>₱850.00</td>
                        <td>09:30 AM</td>
                    </tr>
                </tbody>
            </table>
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
        
        <div class="d-grid gap-2">
            <button class="btn btn-outline-primary" onclick="updateStatus('pending')">Set to Pending</button>
            <button class="btn btn-outline-warning" onclick="updateStatus('preparing')">Set to Preparing</button>
            <button class="btn btn-outline-info" onclick="updateStatus('ready')">Set to Ready for Delivery</button>
            <button class="btn btn-outline-success" onclick="updateStatus('delivered')">Set to Delivered</button>
            <button class="btn btn-outline-danger" onclick="updateStatus('cancelled')">Set to Cancelled</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    // Simple JS to handle modal (Pseudo-code for now, normally would be in separate JS file)
    let statusModal;
    
    document.addEventListener('DOMContentLoaded', function() {
        statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    });

    function openStatusModal(orderId, currentStatus) {
        document.getElementById('modalOrderId').value = orderId;
        document.getElementById('displayOrderId').innerText = orderId;
        statusModal.show();
    }

    function updateStatus(newStatus) {
        const orderId = document.getElementById('modalOrderId').value;
        // In a real app, you would make an AJAX call here
        alert(`Order #${orderId} status updated to: ${newStatus}`);
        statusModal.hide();
        // optionally reload page or update UI row
    }
</script>