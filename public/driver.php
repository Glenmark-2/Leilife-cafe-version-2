<?php
// public/index.php

// 1) Determine requested page
$page = $_GET['page'] ?? 'dashboard';

// 2) Load route map + middleware
$routes = include __DIR__ . '/../backend/config/driver_routes.php';
require_once __DIR__ . '/../backend/middleware/auth.php';

// 3) Bootstrap data layer (so pages can use $appData without including init again)
require_once __DIR__ . '/../backend/db_script/init.php';

// 4) Resolve target file; fall back to 404 if unknown
$target = $routes[$page] ?? $routes['404'];

// 5) Enforce access control
// requireLogin($page, 'driver');

// 6) Render layout + page
if ($page === 'user-receipt') {
    // Load directly without layout
    include $target;
    exit;
}

include __DIR__ . '/../components/driver/header.php';

echo '<div id="pageContent">';
include $target;
echo '</div>';

// Expose current page and Pusher config to JS
echo '<script>
    window.currentPage = "' . htmlspecialchars($page) . '";
    window.pusherConfig = {
        key: "' . getenv('PUSHER_KEY') . '",
        cluster: "' . getenv('PUSHER_CLUSTER') . '"
    };
</script>';

// Include Pusher library + Realtime script
echo '<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>';
echo '<script src="../scripts/driver/realtime.js"></script>';

include __DIR__ . '/../components/driver/navigation.php';
