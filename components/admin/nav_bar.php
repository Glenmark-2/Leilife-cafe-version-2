<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../global_styles.css">

  <?php
  $page_styles = include __DIR__ . '/../../backend/config/styles_config.php';
  if (isset($page_styles[$page])) {
    foreach ($page_styles[$page] as $css_file) {
      echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
    }
  }
  ?>
</head>

<body>
<div class="offcanvas offcanvas-start show" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasLabel">Offcanvas</h5>
    <!-- <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button> -->
  </div>
  <div class="offcanvas-body">
    PWEDE MO TO GAMITIN NOVS OR GAWA KA SARILI 
  </div>
</div>


