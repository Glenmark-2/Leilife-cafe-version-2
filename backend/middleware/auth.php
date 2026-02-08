<?php
require_once __DIR__ . '/../helpers/SessionManager.php';
require_once __DIR__ . '/../helpers/UrlHelper.php';

function requireLogin($page, $required_role = null)
{
    // Pages that require authentication (example list for customers)
    $protected_pages = ['checkout', 'profile', 'order_tracking'];

    // Pages only for guests
    $guest_pages = ['sign_up', 'login', 'forgot_password'];

    if (in_array($page, $protected_pages)) {
        SessionManager::requireLogin();
    }

    if (in_array($page, $guest_pages)) {
        SessionManager::requireGuest();
    }

    // Strict Role Enforcement if provided
    if ($required_role) {
        SessionManager::requireLogin(); // Must be logged in first
        $user_role = SessionManager::get('user_role');

        if ($user_role !== $required_role) {
            // Wrong role? kick them out to their correct home or generic home
            if ($user_role === 'admin') {
                header("Location: " . UrlHelper::getFullUrl('/public/admin.php'));
            } elseif ($user_role === 'driver') {
                header("Location: " . UrlHelper::getFullUrl('/public/driver.php'));
            } else {
                header("Location: " . UrlHelper::getFullUrl('/public/index.php'));
            }
            exit;
        }
    }

    return true;
}
