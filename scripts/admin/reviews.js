document.addEventListener('DOMContentLoaded', () => {
    fetchReviews();
});

async function fetchReviews() {
    const container = document.getElementById('reviews-grid-container');
    if (!container) return;

    try {
        const response = await fetch('../backend/api/admin/get_all_feedbacks.php');
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
        const initials = (fb.first_name?.[0] || '') + (fb.last_name?.[0] || '');
        const fullName = `${fb.first_name} ${fb.last_name}`;
        const stars = '★'.repeat(fb.rating) + '☆'.repeat(5 - fb.rating);
        const date = new Date(fb.created_at).toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric'
        });

        // Determine if we show a photo or initials
        let avatarHtml = `<p class="reviewer-initials">${initials.toUpperCase()}</p>`;
        if (fb.profile_photo) {
            const photoPath = fb.profile_photo.startsWith('http') ? fb.profile_photo : `../public/assets/profiles/${fb.profile_photo}`;
            avatarHtml = `<img src="${photoPath}" class="reviewer-photo" alt="${fullName}" onerror="this.outerHTML='${avatarHtml}'">`;
        }

        html += `
            <div class="feedback-card minimalist">
                <div class="review-header">
                    ${avatarHtml}
                    <p class="reviewer-title">${fullName}</p>
                </div>
                <p class="review-body-simple">
                    "${fb.comment || 'No comment provided.'}"
                </p>
                <div class="review-metadata d-flex justify-content-between align-items-center">
                    <span>Order: ${fb.order_number} | <span class="review-stars">${stars}</span></span>
                    <span class="text-muted small">${date}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}
