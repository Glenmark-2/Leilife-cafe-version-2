<?php
require_once __DIR__ . '/../backend/services/ProductService.php';
require_once __DIR__ . '/../backend/helpers/UrlHelper.php';

$productService = new ProductService();
$product = null;

if (isset($_GET['id'])) {
    $product = $productService->getProductById($_GET['id']);
}

// Redirect or show error if product not found (optional, but good UX)
if (!$product || $product['is_archived'] == 1) {
    echo "<div class='container mt-5'><p>Product not found or no longer available.</p></div>";
    // Alternatively redirect: header('Location: index.php?page=menu'); exit;
} else {
?>
    <div class="container-fluid solo-product-section mt-5 mb-5 w-75">
        <div class="row g-1 justify-content-center gap-4">

            <!-- IMAGE -->
            <div class="col-md-4 d-flex justify-content-center align-items-center">
                <div class="product-img-wrapper">
                    <img src="<?php
                                $img = !empty($product['image_path']) ? $product['image_path'] : '';
                                echo (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, '/'))
                                    ? UrlHelper::getBaseUrl() . '/public/assets/products/' . trim($img)
                                    : (!empty($img) ? $img : UrlHelper::getBaseUrl() . '/public/assets/products/not_available.png');
                                ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        class="product-img">
                </div>
            </div>


            <!-- PRODUCT DETAILS CARD -->
            <div class="col-md-6">
                <div class="details-section p-4 position-relative">

                    <!-- HEART ICON -->
                    <div class="container-fluid d-flex justify-content-between align-items-center p-0">
                        <h2 class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <button class="favorite <?php echo (isset($_SESSION['user_id']) && $productService->isFavorite($_SESSION['user_id'], $product['product_id'])) ? 'active' : ''; ?>" id="favoriteBtn">
                            <i class="bi bi-heart-fill wishlist-icon"></i>
                        </button>
                    </div>

                    <p class="price mt-2">₱<?php echo number_format($product['price'], 2); ?></p>

                    <?php if (!empty($product['description'])): ?>
                        <p class="mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                    <?php endif; ?>

                    <!-- Quantity selector -->
                    <div class="quantity d-flex align-items-center mb-3">
                        <button class="btn btn-outline-secondary btn-slctr">-</button>
                        <span class="mx-3 qty-value">1</span>
                        <button class="btn btn-outline-secondary btn-slctr">+</button>
                    </div>

                    <button class="btn btn-primary-custom"
                        data-id="<?php echo $product['product_id']; ?>"
                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                        data-price="<?php echo $product['price']; ?>"
                        id="addToCartBtn">
                        Add to cart
                    </button>
                </div>
            </div>

        </div>
    </div>
<?php } ?>

<script src="<?= UrlHelper::getBaseUrl() ?>/scripts/users/solo_product.js"></script>
<script>
    // Favorite Button Toggle (Visual only for now)
    const userId = <?= $_SESSION['user_id'] ?? 'null' ?>;
    const favBtn = document.getElementById('favoriteBtn');
    console.log(userId);

    if (favBtn) {
        favBtn.addEventListener('click', async function() {
            if (!userId) {
                alert('Please log in to add favorites');
                return;
            }
            this.classList.toggle('active');
            try {
                const resp = await fetch(`${window.BASE_URL}/backend/api/add_favorite.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `user_id=${userId}&product_id=<?= $product['product_id'] ?>`
                });
                const result = await resp.json();
                if (!result.success) {
                    this.classList.toggle('active');
                    alert(result.message || 'Failed');
                    return;
                }
                // Optional: log action
                console.log(result.action);
            } catch (e) {
                this.classList.toggle('active');
                alert('Network error');
            }
        });
    }
</script>