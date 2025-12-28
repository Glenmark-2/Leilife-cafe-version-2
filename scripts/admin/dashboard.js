let statusModal;
let orderDetailsModal;
let confirmationModal;
let pendingStatusUpdate = null; // Store (status, orderId) to execute after confirmation

document.addEventListener('DOMContentLoaded', function () {
    statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    fetchDashboardData();

    // Add filter listener
    document.getElementById('sortOrders').addEventListener('change', function () {
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

    if (newStatus === 'picked_up' || newStatus === 'delivered') {
        // Show confirmation modal
        pendingStatusUpdate = newStatus;
        const action = newStatus === 'picked_up' ? 'Pick Up' : 'Delivery';
        document.getElementById('confirmationMessage').innerText = `Are you sure you want to mark Order #${orderId} as ${action}? It will be removed from the active dashboard.`;
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
        const response = await fetch('../backend/api/admin/update_order_status.php', {
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
        const response = await fetch(`../backend/api/admin/get_dashboard_data.php?filter=${filter}`);
        const result = await response.json();

        if (result.status === 'success') {
            updateDashboardUI(result.data);
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

        const customerName = `${order.first_name || ''} ${order.last_name || ''}`.trim() || 'Guest';
        const typeBadge = order.delivery_method === 'delivery' ? 'bg-secondary' : 'bg-info text-dark';
        const statusClass = `status-${order.status.toLowerCase()}`;

        const createdDate = new Date(order.created_at);
        const dateTime = createdDate.toLocaleDateString() + ' ' + createdDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        row.innerHTML = `
            <td>${order.order_number}</td>
            <td>${customerName}</td>
            <td><span class="badge ${typeBadge}">${order.delivery_method}</span></td>
            <td>
                <button class="status-badge ${statusClass} btn btn-sm" onclick="event.stopPropagation(); openStatusModal('${order.id}', '${order.status}', '${order.delivery_method}')">
                    ${capitalizeFirstLetter(order.status.replace(/_/g, ' '))}
                </button>
            </td>
            <td>₱${parseFloat(order.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td>${dateTime}</td>
            <td class="text-center">
                <button class="dlBtn" onclick="event.stopPropagation(); downloadReceipt(${order.id})">
                    <img src="__DIR__./../../public/assets/downloads.png" alt="Download" class="dlButton" style="width: 20px; height: 20px;">
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function openStatusModal(orderId, currentStatus, deliveryMethod) {
    document.getElementById('modalOrderId').value = orderId;
    document.getElementById('displayOrderId').innerText = orderId;

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
        const response = await fetch(`../backend/api/admin/get_order_details.php?orderId=${orderId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const data = result.data;
            document.getElementById('detailOrderNumber').innerText = data.order_number;
            document.getElementById('detailDeliveryFee').innerText = `₱${parseFloat(data.delivery_fee).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
            document.getElementById('detailTotalAmount').innerText = `₱${parseFloat(data.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;

            const tableBody = document.getElementById('orderDetailsBody');
            tableBody.innerHTML = '';

            const statuses = ['pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered', 'cancelled', 'payment_failed'];

            data.items.forEach(item => {
                const tr = document.createElement('tr');

                let statusOptions = '';
                statuses.forEach(status => {
                    const selected = item.status === status ? 'selected' : '';
                    const label = capitalizeFirstLetter(status.replace(/_/g, ' '));
                    statusOptions += `<option value="${status}" ${selected}>${label}</option>`;
                });

                tr.innerHTML = `
                    <td>${item.product_name}</td>
                    <td>₱${parseFloat(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td>${item.quantity}</td>
                    <td>₱${parseFloat(item.subtotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td>
                        <select class="form-select form-select-sm status-select" onchange="updateOrderItemStatus('${item.id}', this.value)">
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

async function updateOrderItemStatus(itemId, newStatus) {
    try {
        const response = await fetch('../backend/api/admin/update_order_item_status.php', {
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
            // Optional: Show a toast or small notification
        } else {
            alert('Failed to update item status: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating item status:', error);
        alert('An error occurred while updating item status.');
    }
}

function downloadReceipt(orderId) {
    // Placeholder for receipt download implementation
    console.log('Downloading receipt for order:', orderId);
    // You can implement the actual download logic here, possibly opening a new window:
    // window.open(`../backend/api/admin/download_receipt.php?orderId=${orderId}`, '_blank');
}
