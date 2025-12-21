<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../backend/services/ProductService.php';
$productService = new ProductService();
$categories = $productService->getAllCategories();
try {
    $products = $productService->getAllProductsAdmin(['is_archived' => 'all']);
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!-- DEBUG: Found <?php echo count($products); ?> products -->

<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Products</p>
    </div>
    <div>
        <button type="button" class="btn-primary-custom btns" id="toggle-archive">View Archive</button>
    </div>
</div>

<!-- category buttons -->
<div class="category-filters-container mb-3">
    <div class="main-categories mb-2">
        <span class="filter-label">Main:</span>
        <button type="button" class="btn btn-outline-primary active category-btn" data-category-id="all">All</button>
        <?php foreach ($categories as $cat): ?>
            <?php if ($cat['parent_id'] === null): ?>
                <button type="button" class="btn btn-outline-primary category-btn" data-category-id="<?= $cat['category_id'] ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="sub-categories">
        <span class="filter-label">Sub:</span>
        <?php foreach ($categories as $cat): ?>
            <?php if ($cat['parent_id'] !== null): ?>
                <button type="button" class="btn btn-outline-secondary category-btn sub-cat-btn" data-category-id="<?= $cat['category_id'] ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<hr>

<div id="search_add">
    <div class="search-container">
        <i class="bi bi-search search-icon"></i>
        <input type="search" id="product-search" placeholder="Search product name or description..." aria-label="Search products">
    </div>
    <div class="add-container">
        <button type="button" class="btn-primary-custom btns">Add new product</button>
    </div>
</div>

<div class="table-container">
    <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
        <table id="products-table">
            <thead style="position: sticky; top: 0; z-index: 1; background-color: #e1d1bbff;">
                <tr>
                    <th class="nameCol">Name</th>
                    <th class="priceCol">Price</th>
                    <th class="catCol">Category</th>
                    <th class="statusCol">Status</th>
                    <th class="actionsCol">Actions</th>
                </tr>
            </thead>
            <tbody id="products-body">
                <!-- Data will be populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="pagination-container mt-3 d-flex justify-content-between align-items-center">
    <div id="pagination-info">Showing 0 to 0 of 0 entries</div>
    <nav aria-label="Product pagination">
        <ul class="pagination mb-0" id="pagination-controls">
            <!-- Pagination items will be populated by JS -->
        </ul>
    </nav>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f8f1e7;">
                <h5 class="modal-title fw-bold" id="editProductModalLabel">Edit Product Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" class="needs-validation" novalidate>
                    <input type="hidden" id="edit-product-id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit-name" class="form-label fw-semibold">Product Name</label>
                                <input type="text" class="form-control" id="edit-name" required minlength="3">
                                <div class="invalid-feedback">Please enter a valid product name (min 3 characters).</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit-description" class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" id="edit-description" rows="3" required></textarea>
                                <div class="invalid-feedback">Please provide a product description.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit-price" class="form-label fw-semibold">Price (₱)</label>
                                <input type="number" class="form-control" id="edit-price" step="0.01" min="0" required>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit-category" class="form-label fw-semibold">Category</label>
                                <select class="form-select" id="edit-category" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a category.</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit-status" class="form-label fw-semibold">Availability</label>
                                <select class="form-select" id="edit-status">
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editProductForm" class="btn-primary-custom" style="padding: 7px 25px;">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Pass PHP data to JS
    const allProducts = <?php echo json_encode($products); ?>;
</script>