<!-- View Orders Pill Component -->
<div id="view-orders-pill" class="view-orders-pill" style="display: none;">
    <span class="pill-icon"><i class="bi bi-receipt"></i></span>
    <span class="pill-text">View Orders</span>
</div>

<!-- Orders Modal -->
<div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-custom modal-dialog-centered">
    <div class="modal-content modal-content-custom">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="ordersModalLabel">My Orders</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="orders-list" class="list-group list-group-flush">
            <!-- Orders will be injected here -->
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
    .view-orders-pill {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #d0b28c 0%, #b08d55 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        cursor: pointer;
        z-index: 1050;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        animation: subtleBounce 3s infinite;
        font-family: 'Lato', sans-serif;
        font-weight: 600;
        border: 2px solid rgba(255,255,255,0.2);
    }

    .view-orders-pill:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .pill-icon {
        font-size: 1.2rem;
    }

    @keyframes subtleBounce {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
        40% {transform: translateY(-10px);}
        60% {transform: translateY(-5px);}
    }

    /* Cart Open Behavior */
    body.cart-open .view-orders-pill {
        right: auto;
        left: 30px;
    }
    
    @media (min-width: 992px) {
        body.cart-open .view-orders-pill {
            right: auto;
            left: 30px;
        }
    }

    /* Modal Styling */
    .modal-dialog-custom {
        max-width: 500px;
    }
    
    .modal-content-custom {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .orders-list-item {
        cursor: pointer;
        transition: background-color 0.2s;
        border-left: 5px solid transparent;
        padding: 15px 20px;
    }

    .orders-list-item:hover {
        background-color: #f8f9fa;
        border-left-color: #d0b28c;
    }

    .order-id {
        font-weight: 700;
        color: #333;
    }

    .order-date {
        font-size: 0.85rem;
        color: #777;
    }

    .order-status {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
    }
    
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-preparing { background-color: #cfe2ff; color: #084298; }
    .status-ready { background-color: #d1e7dd; color: #0f5132; }
    .status-completed { background-color: #d1e7dd; color: #0f5132; }
    .status-cancelled { background-color: #f8d7da; color: #842029; }
    
    .order-total {
        font-weight: 700;
        color: #d0b28c;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewOrdersPill = document.getElementById('view-orders-pill');
        const ordersModalEl = document.getElementById('ordersModal');
        const ordersModal = new bootstrap.Modal(ordersModalEl);
        const ordersList = document.getElementById('orders-list');
        
        let activeOrders = [];
        let isLoading = false;

        // Check availability (Only on Menu Page)
        const urlParams = new URLSearchParams(window.location.search);
        // Only run logic on menu page
        if (urlParams.get('page') === 'menu') {
            checkOrdersVisibility();
        }

        viewOrdersPill.addEventListener('click', function() {
            if (activeOrders.length === 1) {
                // Redirect immediately
                 window.location.href = 'index.php?page=order_tracking&order_id=' + activeOrders[0].id;
            } else {
                ordersModal.show();
                renderOrders(activeOrders);
                // Optional: Re-fetch purely to update status if needed, 
                // but for UX speed using cached is better. 
                // We can do a silent background refresh if we want.
                fetchOrders(false).then(updated => {
                    if(updated) renderOrders(activeOrders);
                });
            }
        });

        function checkOrdersVisibility() {
            fetchOrders(true).then(() => {
                if (activeOrders.length > 0) {
                    if(viewOrdersPill) {
                        viewOrdersPill.style.display = 'flex';
                    }
                } else {
                    if(viewOrdersPill) viewOrdersPill.style.display = 'none';
                }
            });
        }

        function fetchOrders(isInitialCheck = false) {
             if (!isInitialCheck) {
                 ordersList.innerHTML = `
                    <div class="text-center p-4">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
             }

            // Adjust the path if necessary. Using absolute path from root.
            return fetch('/Leilife_2nd/backend/api/get_my_orders.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Filter out cancelled orders
                        activeOrders = data.orders.filter(o => o.status !== 'cancelled');
                        return true;
                    } else {
                        activeOrders = [];
                        // User might not be logged in or error
                        if (!isInitialCheck) {
                            if (data.message === 'Unauthorized') {
                                 ordersList.innerHTML = `
                                    <div class="text-center p-4">
                                        <p class="mb-3">Please login to view your orders.</p>
                                        <a href="index.php?page=home" class="btn btn-outline-primary btn-sm">Go to Login</a>
                                    </div>
                                `;
                            } else {
                                 ordersList.innerHTML = `<div class="p-4 text-center text-danger">${data.message || 'No orders found.'}</div>`;
                            }
                        }
                        return false;
                    }
                })
                .catch(error => {
                    console.error('Error fetching orders:', error);
                    activeOrders = [];
                    if (!isInitialCheck) {
                        ordersList.innerHTML = `<div class="p-4 text-center text-danger">Failed to load orders. Please try again.</div>`;
                    }
                    return false;
                });
        }

        function renderOrders(orders) {
            if (orders.length === 0) {
                ordersList.innerHTML = `<div class="p-4 text-center text-muted">You have no orders yet.</div>`;
                return;
            }

            let html = '';
            orders.forEach(order => {
                const date = new Date(order.created_at).toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });
                const statusClass = 'status-' + (order.status || 'pending').toLowerCase();
                const displayId = order.order_number ? order.order_number : ('#' + order.id);
                
                // Redirection logic
                const itemHtml = `
                    <div class="orders-list-item d-flex justify-content-between align-items-center border-bottom" onclick="window.location.href='index.php?page=order_tracking&order_id=${order.id}'">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="order-id">${displayId}</span>
                                <span class="order-status ${statusClass}">${order.status}</span>
                            </div>
                            <div class="order-date">${date}</div>
                        </div>
                        <div class="text-end">
                            <div class="order-total">₱${parseFloat(order.total_amount).toFixed(2)}</div>
                        </div>
                    </div>
                `;
                html += itemHtml;
            });
            ordersList.innerHTML = html;
        }
    });
</script>
