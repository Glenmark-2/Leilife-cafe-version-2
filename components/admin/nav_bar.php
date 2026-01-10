<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page) ? ucwords(str_replace('_', ' ', $page)) . ' | ' : '' ?>Admin - Leilife</title>
  <link rel="icon" type="image/png" href="<?= UrlHelper::getBaseUrl() ?>/public/assets/Mask%20group.png">
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../global_styles.css">
  <link rel="stylesheet" href="../css/admin/nav_bar.css">


  <?php
  require_once __DIR__ . '/../../backend/helpers/UrlHelper.php';
  $page_styles = include __DIR__ . '/../../backend/config/admin_styles_config.php';
  if (isset($page_styles[$page])) {
    foreach ($page_styles[$page] as $css_file) {
      echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
    }
  }
  ?>
  <script>
      window.BASE_URL = "<?php echo UrlHelper::getBaseUrl(); ?>";
  </script>
</head>

<body>

  <aside id="navBar">
    <div id="top">
      <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/leilife.png" style="width: 30px;">
      <p id="sidebar-title">Leilife Cafe & Resto</p>
    </div>

    <?php include "sidebar_button.php";
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/home.png", "Dashboard", "admin.php?page=dashboard");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/fast-food.png", "Products", "admin.php?page=products");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/people.png", "Staffs", "admin.php?page=staff");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/messages.png", "Inbox", "admin.php?page=inbox");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/review.png", "Reviews", "admin.php?page=reviews");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/sales.png", "Sales", "admin.php?page=sales");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/analytics.png", "Analytics", "admin.php?page=analytics");
    echo sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/settings.png", "Settings", "admin.php?page=settings");

    ?>

    <div id="logout">
      <?= sidebarButton(UrlHelper::getBaseUrl() . "/public/assets/logout.png", "Logout", UrlHelper::getFullUrl('/backend/api/logout_user.php')); ?>
    </div>


  </aside>