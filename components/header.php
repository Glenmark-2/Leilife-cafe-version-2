    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Leilife Cafe & Resto</title>

      <!-- Bootstrap CSS (latest CDN) -->
      <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;600;700&display=swap" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
      <link rel="stylesheet" href="../css/users/components/header.css">
      <link rel="stylesheet" href="../css/users/components/footer.css">
      <link rel="stylesheet" href="../global_styles.css">
      <link rel="stylesheet" href="../css/users/components/cart.css">


      <!-- Your CSS -->
      <?php
      $page_styles = include __DIR__ . '/../backend/config/styles_config.php';
      if (isset($page_styles[$page])) {
        foreach ($page_styles[$page] as $css_file) {
          echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
        }
      }
      ?>
    </head>

    <body>

      <!-- Navbar -->
      <nav class="navbar navbar-expand-sm sticky-top" style="background-color: #d0b28c;">
        <div class="container-fluid mx-3 mx-sm-5">
          <!-- Logo -->
          <a class="navbar-brand d-flex align-items-center" href="?page=home">
            <img class="logo" src="/Leilife_2nd/public/assets/leilife.png" alt="Leilife logo">
          </a>

          <!-- Hamburger toggle button -->
          <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <!-- Desktop menu (middle + right buttons) -->
          <div class="d-none d-sm-flex w-100 justify-content-between">
            <div class="mx-auto d-flex gap-2">
              <a class="btn btn-text" href="index.php?page=home">Home</a>
              <a class="btn btn-text" href="index.php?page=menu">Menu</a>
              <a class="btn btn-text" href="index.php?page=home#contact-us">Contact</a>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <?php 
              require_once __DIR__ . '/../backend/helpers/SessionManager.php';
              if (SessionManager::isLoggedIn()): 
              ?>
                  <span class="me-2 text-dark">Welcome, <?php echo htmlspecialchars(SessionManager::get('user_name')); ?></span>
                  <a class="btn btn-text" href="/Leilife_2nd/backend/api/logout_user.php">Logout</a>
              <?php else: ?>
                  <a class="btn btn-text" href="#" id="webLogin">Login</a>
                  <a class="btn btn-text" href="index.php?page=sign_up">Sign up</a>
              <?php endif; ?>
              
              <a href="#" class="btn btn-link p-0" id="cartBtn">
                <i class="bi bi-cart-fill" style="color:black; font-size:1.5rem;"></i>
              </a>

            </div>
          </div>
        </div>

        <!-- Mobile dropdown menu -->
        <div id="navbarDropdownContent">
          <a class="btn btn-text" href="index.php?page=home">Home</a>
          <a class="btn btn-text" href="index.php?page=menu">Menu</a>
          <a class="btn btn-text" href="index.php?page=home#about-us">About</a>
          <a class="btn btn-text" href="index.php?page=home#contact-us">Contact</a>
          <hr style="width: 80%; border-top: 1px solid #cccccc; margin: 0.5rem auto;">
          <?php if (SessionManager::isLoggedIn()): ?>
              <span class="btn btn-text">Welcome, <?php echo htmlspecialchars(SessionManager::get('user_name')); ?></span>
              <a class="btn btn-text" href="/Leilife_2nd/backend/api/logout_user.php">Logout</a>
          <?php else: ?>
              <a class="btn btn-text" href="#" id="mobileLogin">Login</a>
              <a class="btn btn-text" href="index.php?page=sign_up">Sign up</a>
          <?php endif; ?>
          <a class="btn btn-text" href="#" id="mobileCartBtn">Cart</a>
        </div>
      </nav>

      <div class="cart-container d-none" id="cart-container">
          <?php include __DIR__ . "/cart.php"; ?>
      </div>

      <div class="container-fluid px-0">

        <!-- login modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
          <?php include "login.php"; ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../scripts/users/components/header.js"></script>