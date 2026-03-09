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
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        z-index: 1050;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        animation: subtleBounce 3s infinite;
        font-family: 'Lato', sans-serif;
        font-weight: 600;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .view-orders-pill:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .pill-icon {
        font-size: 1.2rem;
    }

    @keyframes subtleBounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-10px);
        }

        60% {
            transform: translateY(-5px);
        }
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
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
        display: inline-block;
        white-space: nowrap;
        width: fit-content;
        margin-right: 10px;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-preparing {
        background-color: #cfe2ff;
        color: #084298;
    }

    .status-ready_for_pickup {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-out_for_delivery {
        background-color: #e2e3e5;
        color: #41464b;
    }

    .status-ready {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-delivered {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-completed {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-picked_up {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #842029;
    }

    .status-payment_failed {
        background-color: #f8d7da;
        color: #842029;
    }

    .order-total {
        font-weight: 700;
        color: #d0b28c;
    }

    #orders-list {
        max-height: 60vh;
        overflow-y: auto;
    }

    /* Custom Scrollbar for Orders List */
    #orders-list::-webkit-scrollbar {
        width: 6px;
    }

    #orders-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #orders-list::-webkit-scrollbar-thumb {
        background: #d0b28c;
        border-radius: 10px;
    }

    #orders-list::-webkit-scrollbar-thumb:hover {
        background: #b08d55;
    }
</style>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewOrdersPill = document.getElementById('view-orders-pill');
        const ordersModalEl = document.getElementById('ordersModal');
        const ordersModal = new bootstrap.Modal(ordersModalEl);
        const ordersList = document.getElementById('orders-list');

        let activeOrders = [];
        let isLoading = false;

        // Pusher Initialization
        initPusher();

        function initPusher() {
            if (typeof Pusher === 'undefined') return;

            // For the pill, we can't reliably assume pusherConfig is in window 
            // if this component is loaded independently. 
            // So we inject it here from PHP.
            <?php
            require_once __DIR__ . '/../backend/helpers/EnvLoader.php';
            EnvLoader::load(__DIR__ . '/../.env');
            $pk = getenv('PUSHER_KEY');
            $pc = getenv('PUSHER_CLUSTER') ?: 'ap1';
            ?>

            const pKey = '<?php echo $pk; ?>';
            const pCluster = '<?php echo $pc; ?>';
            const uId = window.userId || null;

            if (!pKey || !uId) return;

            const pusher = new Pusher(pKey, {
                cluster: pCluster
            });

            const channel = pusher.subscribe('user-' + uId);
            channel.bind('order-status-changed', function(data) {
                checkOrdersVisibility();
            });
        }

        // Check availability on all pages
        checkOrdersVisibility();

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
                    if (updated) renderOrders(activeOrders);
                });
            }
        });

        function checkOrdersVisibility() {
            if (!window.isLoggedIn) return; // Don't fetch if not logged in

            fetchOrders(true).then(() => {
                if (activeOrders.length > 0) {
                    if (viewOrdersPill) {
                        viewOrdersPill.style.display = 'flex';
                    }
                } else {
                    if (viewOrdersPill) viewOrdersPill.style.display = 'none';
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
                        // Show only active orders: exclude completed, delivered, picked_up, cancelled, and failed
                        const inactiveStatuses = ['delivered', 'picked_up', 'cancelled', 'payment_failed', 'completed'];
                        activeOrders = data.orders.filter(o => !inactiveStatuses.includes(o.status.toLowerCase()));
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
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const statusLabel = order.status.replace(/_/g, ' ').toUpperCase();
                const statusClass = 'status-' + (order.status || 'pending').toLowerCase();
                const displayId = order.order_number ? order.order_number : ('#' + order.id);

                // Redirection logic
                const itemHtml = `
                    <div class="orders-list-item d-flex justify-content-between align-items-center border-bottom" onclick="window.location.href='index.php?page=order_tracking&order_id=${order.id}'">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="order-id">${displayId}</span>
                                <span class="order-status ${statusClass}">${statusLabel}</span>
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