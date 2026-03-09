<?php require_once __DIR__ . '/../../backend/helpers/UrlHelper.php'; ?>
<script>
    window.BASE_URL = "<?php echo UrlHelper::getBaseUrl(); ?>";
</script>

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

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="orderDetailsModalLabel">Order Details - <span id="detailOrderNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="orderDetailsBody">
                            <!-- Items will be loaded here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Delivery Fee:</th>
                                <td id="detailDeliveryFee">₱0.00</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total Amount:</th>
                                <th id="detailTotalAmount">₱0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>