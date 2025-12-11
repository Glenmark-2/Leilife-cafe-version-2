<?php
function sidebarButton($imagePath, $title, $page)
{
    // $currentPage = $_GET['page'] ?? 'dashboard';
    // $active = ($currentPage === $page) ? 'active' : '';

    // $href = ($page === 'logout') ? '/Leilife/backend/admin/admin_logout.php' : "/Leilife/public/admin.php?page=$page";

    // echo "
    // <div id='box'>
    //     <a href='#' class='sidebar-btn $active' style='text-decoration:none; width:100%;'>
    //         <img src='$imagePath' alt='$alt'>
    //         <p id='text'>$text</p>
    //     </a>
    // </div>
    // ";
    // Get current page from URL query parameter
    $currentPage = $_GET['page'] ?? 'dashboard';
    
    // Extract target page from href (e.g., "admin.php?page=staff" -> "staff")
    $targetPage = 'dashboard'; // default
    if (strpos($page, 'page=') !== false) {
        $parts = explode('page=', $page);
        $targetPage = $parts[1];
    } else if ($page === '#' || $page === '') {
        $targetPage = 'none';
    }

    $activeClass = ($currentPage === $targetPage) ? 'active' : '';

    echo "
    <a href='$page' class='buttonDiv $activeClass'>
        <img src='$imagePath' id='logo' class='icons'>
        <p class='label'>$title</p>
    </a>";
}
