<div class="banner-container">
  <img src="/Leilife_2nd/public/assets/image 37.png" alt="Homepage Banner" class="darken-img">
  <div class="banner-text">
    <h1>Welcome to Leilife Cafe</h1>
    <p>Your perfect spot for coffee and meals</p>
    <button class="btn-primary-custom">Order Now</button>
  </div>
</div>
<div class="container first-section mt-5">
    <h4 class="fw-bold">Hey there!</h4>
    <p>Unwind with the comforting taste of Leilife Café and Resto!</p>

    <div class="row mt-5 justify-content-between">
        <?php 
            // Card 1
            $title = "Kape Masarap";
            $price = 100;
            $size = "1 x 250 ml";
            $image = "/Leilife_2nd/public/assets/image 39.png";
            $description = "Masarap kape.";
            $isActive = false;
            include __DIR__ . "/../partials/card.php";
        ?>

        <?php 
            // Card 2
            $title = "Iced Latte";
            $price = 120;
            $size = "1 x 300 ml";
            $image = "/Leilife_2nd/public/assets/image 39.png";
            $description = "Chill vibes only.";
            $isActive = false; // Example: this one is 'selected'
            include __DIR__ . "/../partials/card.php";
        ?>

        <?php 
            // Card 3
            $title = "Caramel Macchiato";
            $price = 140;
            $size = "1 x 300 ml";
            $image = "/Leilife_2nd/public/assets/image 39.png";
            $description = "Sweet and bold.";
            $isActive = false;
            include __DIR__ . "/../partials/card.php";
        ?>

    </div>