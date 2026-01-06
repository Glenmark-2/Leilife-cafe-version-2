<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Leilife Driver</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- MapLibre Map CSS & JS -->
    <link href='https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css' rel='stylesheet' />
    <script src='https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js'></script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Core Driver Styles -->
    <link rel="stylesheet" href="../css/driver/driver_shell.css">
    
    <?php
    // Optional: Load page specific styles
    if (isset($pageStyles)) {
        foreach ($pageStyles as $style) {
            echo '<link rel="stylesheet" href="' . $style . '">';
        }
    }
    ?>
</head>
<body>

<header class="driver-header">
    <div class="header-left">
        <a href="?page=dashboard" class="brand-logo">
            <i class="ph-fill ph-steering-wheel" style="font-size: 1.5rem;"></i>
            <span>Leilife</span>
        </a>
    </div>

    <div class="header-right">
        <!-- Status Toggle (Visual Only for now) -->
        <div class="status-ui online" id="driverStatusDisplay">
            <div class="status-indicator"></div>
            <span class="status-text">Online</span>
        </div>

        <button class="notif-btn">
            <i class="ph ph-bell"></i>
            <span class="notif-badge"></span>
        </button>
    </div>
</header>
