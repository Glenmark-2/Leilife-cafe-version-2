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
    <div>

    </div>
</div>

<div class="container-lg second-section mt-4 mb-5 w-75 bg-light">
    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="/Leilife_2nd/public/assets/image 39.png" class="justify-content-center" alt="...">
            </div>
            <div class="carousel-item">
                <img src="/Leilife_2nd/public/assets/image 39.png" class="justify-content-center" alt="...">
            </div>
            <div class="carousel-item">
                <img src="/Leilife_2nd/public/assets/image 39.png" class="justify-content-center" alt="...">
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

<div class="container mt-5 px-3 mb-5 w-75">
    <div class="row shadow-sm rounded-4 overflow-hidden" style="background-color: #ececec;">

        <!-- IMAGE -->
        <div class="col-lg-6 col-md-12 p-0 hero-img-wrapper">
            <img src="/Leilife_2nd/public/assets/image 41.png" class="hero-img" alt="">
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