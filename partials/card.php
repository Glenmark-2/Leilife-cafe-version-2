<?php
$title = $title ?? "Product Name";
$description = $description ?? "";
$price = $price ?? 0;
$size = $size ?? "";
$image = $image ?? "";
$isActive = $isActive ?? false; // if selected
?>

<div class="col-6 col-sm-6 col-md-4 col-lg-3">

    <div class="product-card <?= $isActive ? 'active' : '' ?>"
        data-id="<?= $id ?? '' ?>"
        data-name="<?= ucwords(strtolower(htmlspecialchars($title))) ?>"
        data-price="<?= $price ?>"
        data-image="<?= $image ?>">

        <div class="product-image">
            <img src="<?= $image ?>" alt="<?= htmlspecialchars($title) ?>" onerror="this.src='<?= UrlHelper::getBaseUrl() ?>/public/assets/products/food_photo.png'">
        </div>

        <h5 class="product-title"><?= ucwords(strtolower(htmlspecialchars($title))) ?></h5>

        <p class="product-desc"><?= htmlspecialchars($description) ?></p>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small"><?= htmlspecialchars($size) ?></span>
            <span class="fw-bold">₱ <?= number_format($price, 2) ?></span>
        </div>

        <div class="d-flex align-items-center mb-3">
            <button class="btn btn-outline-secondary btn-sm">-</button>
            <span class="mx-2">1</span>
            <button class="btn btn-outline-secondary btn-sm">+</button>
        </div>

        <button class="btn btn-warning w-100 rounded-pill fw-semibold">
            ADD TO CART
        </button>

    </div>

</div>