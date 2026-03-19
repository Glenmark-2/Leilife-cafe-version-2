<?php require_once __DIR__ . '/../backend/helpers/UrlHelper.php'; ?>
<div class="banner-container">
    <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/image 37.png" alt="Homepage Banner" class="darken-img">
    <div class="banner-text">
        <h1>Welcome to Leilife Cafe</h1>
        <p>Your perfect spot for coffee and meals</p>
        <button class="btn-primary-custom" onclick="window.location.href='index.php?page=menu'">Order Now</button>
    </div>
</div>
<div class="container first-section mt-5">
    <h4 class="fw-bold mb-4">Our Favorites</h4>
    <p class="mb-5">Experience the best of Leilife with our customer-loved signature dishes and drinks.</p>

    <div class="row gy-5 gx-4 justify-content-center">
        <?php
        require_once __DIR__ . '/../backend/services/ProductService.php';
        $productService = new ProductService();

        // Fetch valid products (not archived)
        $allProducts = $productService->getAllProductsAdmin(['is_archived' => 0]);

        // Shuffle to get random "Favorites" each load, or you could pick specific IDs
        if (!empty($allProducts)) {
            shuffle($allProducts);
            $featuredProducts = array_slice($allProducts, 0, 3);
        } else {
            $featuredProducts = [];
        }

        foreach ($featuredProducts as $index => $product) {
            $title = ucwords(strtolower($product['name']));
            $price = $product['price'];
            $id = $product['product_id'];
            // Using category name as "Size" placeholder since size isn't in DB, or empty string
            $size = $product['category_name'] ?? '';

            // Check if image is a full URL or relative path
            $img = $product['image_path'];
            if ($img && strpos($img, 'http') !== 0) {
                // dynamic path handling
                $image = UrlHelper::getBaseUrl() . "/public/assets/products/" . $img;
            } else {
                $image = $img ?: UrlHelper::getBaseUrl() . "/public/assets/products/food_photo.png";
            }

            $description = $product['description'];

            // LOGIC FOR IS_ACTIVE: Highlight the 2nd card (index 1) to make it stand out
            // This answers "how should i use it" -> use it to highlight a specific item
            $isActive = false; // Hover effect takes over now 

            include __DIR__ . "/../partials/card.php";
        }

        if (empty($featuredProducts)) {
            echo '<p class="text-center">No featured products available at the moment.</p>';
        }
        ?>
    </div>
    <div>

    </div>
</div>

<div class="container-lg second-section mt-4 mb-5 custom-w-75 bg-light rounded-4 overflow-hidden shadow-sm p-0">
    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner" style="max-height: 400px;">
            <div class="carousel-item active">
                <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/products/lasagna_supreme.jpg" class="d-block w-100" style="object-fit: cover; height: 400px;" alt="Lasagna Supreme" onerror="this.src='<?= UrlHelper::getBaseUrl() ?>/public/assets/products/food_photo.png'">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h5>Lasagna Supreme</h5>
                    <p>Layers of pasta, meat sauce, and cheese.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/products/matcha_latte.jpg" class="d-block w-100" style="object-fit: cover; height: 400px;" alt="Matcha Latte" onerror="this.src='<?= UrlHelper::getBaseUrl() ?>/public/assets/products/food_photo.png'">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h5>Matcha Latte</h5>
                    <p>Premium green tea milk for a refreshing sip.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/products/chicken_teriyaki_bowl.jpg" class="d-block w-100" style="object-fit: cover; height: 400px;" alt="Chicken Teriyaki" onerror="this.src='<?= UrlHelper::getBaseUrl() ?>/public/assets/products/food_photo.png'">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h5>Chicken Teriyaki</h5>
                    <p>Sweet and savory grilled chicken perfection.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<div class="container mt-5 px-3 mb-5 custom-w-75">
    <div class="row shadow-sm rounded-4 overflow-hidden" style="background-color: #ececec;">

        <!-- IMAGE -->
        <div class="col-lg-6 col-md-12 p-0 hero-img-wrapper">
            <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/image 41.png" class="hero-img" alt="">
        </div>

        <!-- TEXT -->
        <div class="col-lg-6 col-md-12 d-flex flex-column justify-content-center p-4 p-md-5">
            <h2 class="fw-bold mb-3" style="font-size: 1.8rem; line-height: 1.2;">
                When Coffee Meets Good Food,<br>Great Conversations Begin.
            </h2>

            <p class="mb-4" style="font-size: 1rem; line-height: 1.6;">
                At Leilife Cafe and Resto, we believe every meal should be a moment
                to savor. From freshly brewed coffee to hearty meals, we combine
                quality ingredients, skilled preparation, and a warm ambiance to
                create the perfect dining experience for every guest.
            </p>

            <div>
                <button class="btn-primary-custom">
                    Explore
                </button>
            </div>

        </div>
    </div>
</div>

<div class="container mt-5 mb-5">

    <div class="row align-items-center gy-4 text-md-start text-center">

        <!-- LEFT TITLE -->
        <div class="col-md-6 col-12">
            <h3 class="section-title">
                Savor Every Bite & Sip at<br>
                Leilife Cafe and Resto!
            </h3>
        </div>

        <!-- RIGHT PARAGRAPH -->
        <div class="col-md-6 col-12">
            <p class="section-text">
                At Leilife Cafe and Resto, we take pride in serving delicious meals and perfectly brewed coffee.
                From freshly prepared dishes to expertly crafted beverages, every bite and sip is made to give you
                a warm and memorable dining experience.
            </p>
        </div>
    </div>
</div>

<div class="info-cards">
    <?php
    include __DIR__ . "/../partials/info_card.php";
    echo infoCard("🍽️", "Fresh & Flavorful Dishes", "We use only the freshest ingredients...");
    echo infoCard("☕", "Perfectly Brewed Coffee", "Our skilled baristas ensure each cup...");
    echo infoCard("❤️", "A Taste to Remember", "Enjoy hearty meals and comforting drinks...");
    ?>
</div>


<div class="about-us">
    <div class="about-us-title">
        <h3>About us</h3>
        <p>At Leilife Cafe and Resto, we believe every meal should be a moment to savor. From freshly brewed coffee to hearty meals, we combine quality ingredients, skilled preparation, and a warm ambiance to create the perfect dining experience for every guest.</p>
    </div>

    <div class="container mt-5 px-3 mb-5 custom-w-75" id="about-us">
        <div class="row shadow-sm rounded-4 overflow-hidden" style="background-color: #ececec;">

            <!-- TEXT -->
            <div class="col-lg-6 col-md-12 d-flex flex-column justify-content-center p-4 p-md-5">
                <h3>Where Good Food Meets Great Company</h3>
                <p>Enjoy the perfect blend of flavors in our menu — from aromatic coffee to delicious comfort food. Whether you're here for a quick coffee break or a full meal, our passion for great taste shines through in every bite and sip.</p>

                <div id="time">
                    <h3>OPENING HOURS</h3>
                </div>

                <p style="margin: 0; text-align:right">Monday – Friday: 8:00 AM – 10:00 PM</p>
                <p style="margin: 0; text-align:right">Saturday – Sunday: 7:00 AM – 11:00 PM</p>

            </div>

            <!-- IMAGE -->
            <div class="col-lg-6 col-md-12 p-0 hero-img-wrapper">
                <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/about_us.png" class="hero-img" alt="">
            </div>
        </div>
    </div>
</div>

<div class="contact-us" id="contact-us">
    <h3>Contact us</h3>
    <div class="contact-us-box">
        <!-- LEFT SIDE -->
        <div class="contact-info">
            <h5 style="text-align: left;">Get In Touch</h5>

            <p>
                Have questions or want to reach out? We're here to help! Choose any of the contact methods below to get in touch with us.
                We'll respond to your inquiries as quickly as possible.
            </p>

            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <span>leilifecafe&resto@gmail.com</span>
            </div>

            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Lunduyan Langaray, Brgy 14. Caloocan City</span>
            </div>

            <div class="info-item">
                <i class="fas fa-phone"></i>
                <span>0912345678</span>
            </div>
            <br>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>
                    Monday – Friday: 8:00 AM – 10:00 PM <br>
                    Saturday – Sunday: 7:00 AM – 11:00 PM
                </span>
            </div>
        </div>


        <!-- RIGHT SIDE -->
        <div class="contact-form">
            <h2>Your Details</h2>
            <p>Let us know how we get back to you</p>
            <form id="contactForm">
                <div class="form-row">
                    <input class="inputs" type="text" placeholder="Name" name="name">
                    <input class="inputs" type="email" placeholder="Email Address" name="email" required>
                </div>
                <input class="long-inputs" type="text" placeholder="Subject" class="form-control" name="subject">

                <br>
                <textarea class="long-inputs" placeholder="Comments/Questions:" name="message"
                    required>
                </textarea>

                <div id="submitDiv">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal">Send</button>

                </div>
            </form>
        </div>
    </div>

    <!-- Custom Cart Notification Modal -->
    <div id="cart-notification" class="cart-notification-overlay">
        <div class="cart-notification-modal">
            <div style="text-align: center;">
                <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/leilife.png" alt="Leilife" class="mb-2" style="width: 50px; height: auto;">
                <h5 class="fw-bold mb-2" style="color: #5a4b40;">Great Choice!</h5>
            </div>
            <p id="cart-notification-message" class="mb-0 text-center" style="color: #6c757d; font-size: 0.95rem;">Item added to your cart.</p>
        </div>
    </div>


</div>

<!-- Option 2: Floating Sticky App Downloader -->
<div class="floating-app-download d-none d-md-flex">
    <div class="qr-content shadow">
        <h6 class="mb-2 fw-bold text-center" style="color: #5a4b40;">Get Our App!</h6>
        <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/qr_download.png" alt="Scan QR" class="img-fluid rounded border p-1 bg-white">
        <p class="mt-2 mb-0 text-center" style="font-size: 0.8rem; color: #6c757d;">Scan to download</p>
    </div>
    <div class="floating-btn shadow">
        <i class="fas fa-mobile-alt me-2"></i>
        <span>Download our App</span>
    </div>
</div>

<script src="../scripts/users/home.js"></script>