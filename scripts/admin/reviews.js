let orderDetailsModal;

document.addEventListener('DOMContentLoaded', () => {
    orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    fetchReviews();
});

async function fetchReviews() {
    const container = document.getElementById('reviews-grid-container');
    if (!container) return;

    try {
        const response = await fetch(`${window.BASE_URL}/backend/api/admin/get_all_feedbacks.php`);
        const data = await response.json();

        if (data.success) {
            renderReviews(data.feedbacks);
        } else {
            container.innerHTML = `<div class="text-center p-5 w-100"><p class="text-muted">${data.message || 'No reviews found.'}</p></div>`;
        }
    } catch (error) {
        console.error('Error fetching reviews:', error);
        container.innerHTML = `<div class="text-center p-5 w-100"><p class="text-danger">Failed to load reviews. Please try again later.</p></div>`;
    }
}

function renderReviews(feedbacks) {
    const container = document.getElementById('reviews-grid-container');
    if (!feedbacks || feedbacks.length === 0) {
        container.innerHTML = `<div class="text-center p-5 w-100"><p class="text-muted">No reviews have been submitted yet.</p></div>`;
        return;
    }

    let html = '';
    feedbacks.forEach(fb => {
        const firstName = capitalizeFirstLetter(fb.first_name || '');
        const lastName = capitalizeFirstLetter(fb.last_name || '');
        const initials = (firstName?.[0] || '') + (lastName?.[0] || '');
        const fullName = `${firstName} ${lastName}`.trim() || 'Guest User';
        const stars = '★'.repeat(fb.rating) + '☆'.repeat(5 - fb.rating);
        const date = new Date(fb.created_at).toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric'
        });

        // Determine if we show a photo or initials
        let avatarHtml = `<p class="reviewer-initials">${initials.toUpperCase()}</p>`;
        if (fb.profile_photo) {
            const photoPath = fb.profile_photo.startsWith('http') ? fb.profile_photo : `${window.BASE_URL}/public/assets/profiles/${fb.profile_photo}`;
            avatarHtml = `<img src="${photoPath}" class="reviewer-photo" alt="${fullName}" onerror="this.outerHTML='${avatarHtml}'">`;
        }

        html += `
            <div class="feedback-card minimalist">
                <div class="review-header d-flex justify-content-between align-items-start">
                    <div class="reviewer-info d-flex align-items-center">
                        ${avatarHtml}
                        <p class="reviewer-title mb-0 ms-2">${fullName}</p>
                    </div>
                    <button class="btn btn-sm btn-outline-primary view-order-btn" onclick="openOrderDetails('${fb.order_id}')">
                        View Order
                    </button>
                </div>
                <p class="review-body-simple mt-3 mb-3">
                    "${fb.comment || 'No comment provided.'}"
                </p>
                <div class="review-metadata d-flex justify-content-between align-items-center">
                    <span>Order: <span class="fw-bold">#${fb.order_number}</span> | <span class="review-stars">${stars}</span></span>
                    <span class="text-muted small">${date}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
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

            data.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${capitalizeFirstLetter(item.product_name)}</td>
                    <td>₱${parseFloat(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td>${item.quantity}</td>
                    <td>₱${parseFloat(item.subtotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td>
                        <span class="badge bg-secondary">${capitalizeFirstLetter(item.status.replace(/_/g, ' '))}</span>
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

function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}
