<style>
    /* --- Base Minimalist Style (Copied from previous response) --- */

.feedback-card.minimalist {
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); /* Lighter shadow for clean look */
    height: 100%; /* Important for grid items */
}

.minimalist .review-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.minimalist .reviewer-initials {
    background-color: #343a40;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    margin-right: 10px;
    flex-shrink: 0;
}

.minimalist .reviewer-title {
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.minimalist .review-body-simple {
    font-size: 0.95em; /* Slightly smaller for density */
    color: #555;
    margin-bottom: 15px;
    line-height: 1.4;
    /* Limit height for compactness */
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.minimalist .review-metadata {
    font-size: 0.8em; /* Smaller metadata */
    color: #999;
}

.minimalist .review-stars {
    color: #007bff;
    letter-spacing: 1px;
}

/* --- Responsive Grid Styling --- */

.reviews-minimalist-grid {
    display: grid;
    /* Sets the default grid for small screens (single column) */
    grid-template-columns: 1fr;
    gap: 15px; /* Smaller gap for compactness */
    padding: 15px;
}

/* Medium screens (Tablets/Small Desktops) */
@media (min-width: 600px) {
    .reviews-minimalist-grid {
        /* Two columns of equal width */
        grid-template-columns: 1fr 1fr;
    }
}

/* Large screens (Desktops) */
@media (min-width: 1024px) {
    .reviews-minimalist-grid {
        /* Three columns of equal width, meeting the "not too big" requirement */
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }
}
</style>
<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Reviews</p>
    </div>
    </div>

<div class="reviews-container reviews-minimalist-grid">

    <div class="feedback-card minimalist">
        <div class="review-header">
            <p class="reviewer-initials">JS</p>
            <p class="reviewer-title">J. Sterling</p>
        </div>
        <p class="review-body-simple">
            "Simple interface, extremely fast loading times. Exactly what we needed. Five stars for reliability."
        </p>
        <p class="review-metadata">
            Product: Service Pro 3.0 | <span class="review-stars">★★★★★</span>
        </p>
    </div>

    <div class="feedback-card minimalist">
        <div class="review-header">
            <p class="reviewer-initials">AK</p>
            <p class="reviewer-title">Anna Kowalski</p>
        </div>
        <p class="review-body-simple">
            "Clean design, easy to navigate, and the customer support was prompt and helpful. Great product!"
        </p>
        <p class="review-metadata">
            Product: DataFlow 1.5 | <span class="review-stars">★★★★★</span>
        </p>
    </div>

    <div class="feedback-card minimalist">
        <div class="review-header">
            <p class="reviewer-initials">TM</p>
            <p class="reviewer-title">Tom Murphy</p>
        </div>
        <p class="review-body-simple">
            "We switched from a competitor, and this is far superior. Intuitive setup and amazing reliability. Worth it for the time saving."
        </p>
        <p class="review-metadata">
            Product: Service Pro 3.0 | <span class="review-stars">★★★★★</span>
        </p>
    </div>

    <div class="feedback-card minimalist">
        <div class="review-header">
            <p class="reviewer-initials">SJ</p>
            <p class="reviewer-title">Sarah Jenkins</p>
        </div>
        <p class="review-body-simple">
            "Intuitive and user-friendly. My team adapted to it with minimal training, but they love how efficient it is."
        </p>
        <p class="review-metadata">
            Product: TaskFlow Pro | <span class="review-stars">★★★★★</span>
        </p>
    </div>

    <div class="feedback-card minimalist">
        <div class="review-header">
            <p class="reviewer-initials">CG</p>
            <p class="reviewer-title">Chen Guo</p>
        </div>
        <p class="review-body-simple">
            "Good value for money. The reporting features are powerful; I wish I'd switched sooner. Highly recommended for small teams."
        </p>
        <p class="review-metadata">
            Product: TaskFlow Pro | <span class="review-stars">★★★★☆</span>
        </p>
    </div>

    <div class="feedback-card minimalist">
        <div class="review-header">
            <p class="reviewer-initials">LT</p>
            <p class="reviewer-title">Liam Thomson</p>
        </div>
        <p class="review-body-simple">
            "The initial data migration was seamless, and the performance has been stable since launch. Excellent customer support response time."
        </p>
        <p class="review-metadata">
            Product: Service Pro 3.0 | <span class="review-stars">★★★★★</span>
        </p>
    </div>

</div>