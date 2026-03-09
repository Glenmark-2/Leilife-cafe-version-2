let statusModal;
let orderDetailsModal;
let confirmationModal;
let pendingStatusUpdate = null; // Store (status, orderId) to execute after confirmation
let currentPage = 1;
const currentLimit = 10;

// Debounce function to limit how often a function is called
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// Debounced version of fetchDashboardData
const debouncedFetchDashboardData = debounce(fetchDashboardData, 500); // 500ms delay

function initRealtime() {
    if (!window.pusherConfig || !window.pusherConfig.key) {
        console.warn('Pusher configuration missing. Real-time updates disabled.');
        return;
    }

    const pusher = new Pusher(window.pusherConfig.key, {
        cluster: window.pusherConfig.cluster
    });

    const channel = pusher.subscribe('admin-orders');

    channel.bind('new-order', function (data) {
        debouncedFetchDashboardData();
    });

    channel.bind('status-updated', function (data) {
        debouncedFetchDashboardData();
    });

    channel.bind('item-updated', function (data) {
        debouncedFetchDashboardData();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initRealtime();
    statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    fetchDashboardData(); // Initial fetch

    // Add filter listener
    document.getElementById('sortOrders').addEventListener('change', function () {
        currentPage = 1;
        fetchDashboardData();
    });

    // Confirmation Modal Confirm Button
    document.getElementById('confirmStatusBtn').addEventListener('click', async function () {
        if (pendingStatusUpdate) {
            await executeStatusUpdate(pendingStatusUpdate);
            confirmationModal.hide();
            pendingStatusUpdate = null;
        }
    });

    // Auto refresh every 30 seconds
    setInterval(fetchDashboardData, 30000);
});

// ... (keep existing functions until updateStatus)

async function updateStatus(newStatus) {
    const orderId = document.getElementById('modalOrderId').value;
    const orderNumber = document.getElementById('displayOrderId').innerText;
    const triggerConfirm = ['picked_up', 'delivered', 'cancelled'].includes(newStatus);

    if (triggerConfirm) {
        // Show confirmation modal
        pendingStatusUpdate = newStatus;
        let action = '';
        switch (newStatus) {
            case 'picked_up': action = 'Picked Up'; break;
            case 'delivered': action = 'Delivered'; break;
            case 'cancelled': action = 'Cancelled'; break;
        }

        let warningText = `Are you sure you want to mark Order #${orderNumber} as ${action}?`;
        if (newStatus === 'cancelled') {
            warningText += " This will trigger a FULL refund if the order was paid via GCash/PayMongo.";
        } else {
            warningText += " It will be removed from the active dashboard.";
        }

        document.getElementById('confirmationMessage').innerText = warningText;
        statusModal.hide(); // Hide the first modal
        confirmationModal.show();
    } else {
        // Proceed directly
        await executeStatusUpdate(newStatus);
    }
}

async function executeStatusUpdate(newStatus) {
    const orderId = document.getElementById('modalOrderId').value;

    try {
        const response = await fetch(`${window.BASE_URL}/backend/api/admin/update_order_status.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                orderId: orderId,
                status: newStatus
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            statusModal.hide();
            fetchDashboardData(); // Refresh data
        } else {
            alert('Failed to update status: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('An error occurred while updating status.');
    }
}

async function fetchDashboardData() {
    try {
        const filter = document.getElementById('sortOrders').value;
        const response = await fetch(`${window.BASE_URL}/backend/api/admin/get_dashboard_data.php?filter=${filter}&page=${currentPage}&limit=${currentLimit}`);
        const result = await response.json();

        if (result.status === 'success') {
            updateDashboardUI(result.data);
            renderPagination(result.data.totalRecentOrders);
        } else {
            console.error('Failed to fetch dashboard data:', result.message);
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    }
}

function updateDashboardUI(data) {
    // Update Stat Cards
    const stats = data.orderStats;
    const staff = data.staffStats;

    document.querySelector('.text-pending').innerText = stats.pending || 0;
    document.querySelector('.text-preparing').innerText = stats.preparing || 0;
    document.querySelector('.text-ready').innerText = (stats.ready_for_pickup || 0) + (stats.out_for_delivery || 0);
    document.querySelector('.text-delivered').innerText = stats.delivered_today || 0;
    document.querySelector('.text-picked-up').innerText = stats.picked_up_today || 0;
    document.querySelector('.text-cancelled').innerText = stats.cancelled || 0;

    // Active Admins and Drivers
    document.querySelector('.text-admins').innerText = staff.admin || 0;
    document.querySelector('.text-drivers').innerText = staff.driver || 0;

    // Update Recent Orders Table
    const tableBody = document.querySelector('.dashboard-table tbody');
    tableBody.innerHTML = '';

    data.recentOrders.forEach(order => {
        const row = document.createElement('tr');
        row.className = 'clickable-row';
        row.onclick = (e) => {
            // Only trigger if we didn't click the status button explicitly
            if (!e.target.classList.contains('status-badge')) {
                openOrderDetails(order.id);
            }
        };

        const customerName = `${capitalizeFirstLetter(order.first_name || '')} ${capitalizeFirstLetter(order.last_name || '')}`.trim() || 'Guest';
        const isCancelled = order.status === 'cancelled';
        const typeBadge = order.delivery_method === 'delivery' ? 'bg-secondary' : 'bg-info text-dark';
        const statusClass = `status-${order.status.toLowerCase()}`;

        const createdDate = new Date(order.created_at);
        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const mon = monthNames[createdDate.getMonth()];
        const day = createdDate.getDate();
        let hours = createdDate.getHours();
        const mins = String(createdDate.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const dateTime = `${mon} ${day}, ${hours}:${mins}${ampm}`;

        row.innerHTML = `
            <td>${order.order_number}</td>
            <td>${customerName}</td>
            <td><span class="badge ${typeBadge}">${capitalizeFirstLetter(order.delivery_method)}</span></td>
            <td>
                <button class="status-badge ${statusClass} btn btn-sm" 
                        ${isCancelled ? 'disabled style="opacity: 0.8; cursor: not-allowed;"' : ''} 
                        onclick="event.stopPropagation(); openStatusModal('${order.id}', '${order.order_number}', '${order.status}', '${order.delivery_method}', '${order.payment_status}')">
                    ${capitalizeFirstLetter(order.status.replace(/_/g, ' '))}
                </button>
            </td>
            <td>₱${parseFloat(order.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td>${dateTime}</td>
            <td class="text-center">
                <button class="dlBtn" onclick="event.stopPropagation(); downloadReceipt(${order.id})">
                    <img src="${window.BASE_URL}/public/assets/downloads.png" alt="Download" class="dlButton" style="width: 20px; height: 20px;">
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function downloadReceipt(orderId) {
    window.open(`${window.BASE_URL}/pages/admin/pos_receipt.php?order_id=${orderId}`, '_blank');
}
function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

function openStatusModal(orderId, orderNumber, currentStatus, deliveryMethod, paymentStatus) {
    document.getElementById('modalOrderId').value = orderId;
    document.getElementById('displayOrderId').innerText = orderNumber;

    // Update payment status badge
    const psBadge = document.getElementById('displayPaymentStatus');
    psBadge.innerText = (paymentStatus || 'unpaid').toUpperCase();
    psBadge.className = `badge rounded-pill ${paymentStatus === 'paid' ? 'bg-success' : 'bg-danger'}`;

    // Show/Hide "Mark as Paid" button
    // Only show if it's a Pickup order AND it's currently Unpaid
    const paymentAction = document.getElementById('paymentActionSection');
    if (deliveryMethod === 'pickup' && paymentStatus !== 'paid') {
        paymentAction.classList.remove('d-none');
    } else {
        paymentAction.classList.add('d-none');
    }

    const btnReady = document.getElementById('btnReadyForPickup');
    const btnDelivery = document.getElementById('btnOutForDelivery');
    const btnPickedUp = document.getElementById('btnPickedUp');
    const btnDelivered = document.getElementById('btnDelivered');

    if (deliveryMethod === 'pickup') {
        btnReady.style.display = 'block';
        btnDelivery.style.display = 'none';
        btnPickedUp.style.display = 'block';
        btnDelivered.style.display = 'none';
    } else {
        btnReady.style.display = 'none';
        btnDelivery.style.display = 'block';
        btnPickedUp.style.display = 'none';
        btnDelivered.style.display = 'block';
    }

    statusModal.show();
}



async function openOrderDetails(orderId) {
    try {
        const response = await fetch(`${window.BASE_URL}/backend/api/admin/get_order_details.php?orderId=${orderId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const data = result.data;
            document.getElementById('detailOrderNumber').innerText = data.order_number;
            document.getElementById('detailDeliveryFee').innerText = `₱${parseFloat(data.delivery_fee).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
            document.getElementById('detailTotalAmount').innerText = `₱${parseFloat(data.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;

            const tableBody = document.getElementById('orderDetailsBody');
            tableBody.innerHTML = '';

            const statuses = ['pending', 'preparing', 'finished', 'cancelled'];

            data.items.forEach(item => {
                const tr = document.createElement('tr');
                const isCancelled = item.status === 'cancelled';

                let statusOptions = '';
                statuses.forEach(status => {
                    const selected = item.status === status ? 'selected' : '';
                    const label = capitalizeFirstLetter(status.replace(/_/g, ' '));
                    statusOptions += `<option value="${status}" ${selected}>${label}</option>`;
                });

                tr.innerHTML = `
                    <td>${capitalizeFirstLetter(item.product_name)}</td>
                    <td>₱${parseFloat(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td>${item.quantity}</td>
                    <td>₱${parseFloat(item.subtotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td>
                        <select class="form-select form-select-sm status-select" 
                                ${isCancelled ? 'disabled style="background-color: #f8f9fa; cursor: not-allowed;"' : ''} 
                                onchange="updateOrderItemStatus('${item.id}', this.value, this)">
                            ${statusOptions}
                        </select>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            orderDetailsModal.show();
        } else {
            alert('Failed to load order details: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        alert('An error occurred while loading order details.');
    }
}

async function updateOrderItemStatus(itemId, newStatus, element) {
    try {
        const response = await fetch(`${window.BASE_URL}/backend/api/admin/update_order_item_status.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                itemId: itemId,
                status: newStatus
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            if (newStatus === 'cancelled') {
                element.disabled = true;
                element.style.backgroundColor = '#f8f9fa';
                element.style.cursor = 'not-allowed';
            }
        } else {
            alert('Failed to update item status: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating item status:', error);
        alert('An error occurred while updating item status.');
    }
}

async function markAsPaid() {
    const orderId = document.getElementById('modalOrderId').value;
    if (!confirm("Confirm payment received? This will stop the customer's timer.")) return;

    try {
        const response = await fetch(`${window.BASE_URL}/backend/api/admin/mark_order_paid.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderId: orderId })
        });

        const result = await response.json();
        if (result.status === 'success') {
            statusModal.hide();
            fetchDashboardData();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error marking as paid:', error);
        alert('An error occurred.');
    }
}

function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / currentLimit);
    const controls = document.getElementById('pagination-controls');
    const info = document.getElementById('pagination-info');
    
    // Update Info
    const start = totalItems === 0 ? 0 : (currentPage - 1) * currentLimit + 1;
    const end = Math.min(currentPage * currentLimit, totalItems);
    info.innerText = `Showing ${start} to ${end} of ${totalItems} entries`;

    if (totalPages <= 1) {
        controls.innerHTML = '';
        return;
    }

    let html = `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${currentPage - 1})">&laquo;</a>
        </li>`;

    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${i})">${i}</a>
            </li>`;
    }

    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${currentPage + 1})">&raquo;</a>
        </li>`;

    controls.innerHTML = html;
}

function changePage(page) {
    const totalItems = parseInt(document.getElementById('pagination-info').innerText.split('of ')[1]) || 0;
    const totalPages = Math.ceil(totalItems / currentLimit);

    if (page < 1 || (totalPages > 0 && page > totalPages)) return;
    
    currentPage = page;
    fetchDashboardData();
}
