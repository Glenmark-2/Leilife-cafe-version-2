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

<div class="reviews-container reviews-minimalist-grid" id="reviews-grid-container">
    <!-- Feedback cards will be loaded here via JS -->
    <div class="text-center p-5 w-100">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Fetching reviews...</p>
    </div>
</div>