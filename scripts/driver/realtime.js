/**
 * Real-time updates for Driver Module using Pusher
 */

function initDriverRealtime() {
    if (!window.pusherConfig || !window.pusherConfig.key) {
        console.warn('Pusher configuration missing. Real-time updates disabled.');
        return;
    }

    const pusher = new Pusher(window.pusherConfig.key, {
        cluster: window.pusherConfig.cluster
    });

    // Subscribing to driver-specific channel or general order channel
    // Based on admin/dashboard.js, it uses 'admin-orders'
    const channel = pusher.subscribe('admin-orders');

    channel.bind('status-updated', function (data) {
        console.log('Real-time update received:', data);

        // Handle Dispatch Board Updates
        if (window.currentPage === 'available' || window.currentPage === 'dashboard') {
            if (data.status === 'out_for_delivery' || data.status === 'delivered' || data.status === 'cancelled') {
                // Important for dispatch board to show fresh data
                location.reload();
            }
        }

        // Handle Active Delivery Updates
        else if (window.currentPage === 'deliveries') {
            if (data.status === 'cancelled') {
                alert('Your active order has been cancelled by the store.');
                location.reload();
            }
        }
    });

    channel.bind('new-order', function (data) {
        // New orders usually go to 'preparing' first, 
        // but if they jump to 'out_for_delivery' directly for some reason...
        if (window.currentPage === 'available' || window.currentPage === 'dashboard') {
            location.reload();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDriverRealtime();
});
