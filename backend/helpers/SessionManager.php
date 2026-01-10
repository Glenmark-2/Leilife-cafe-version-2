<?php
require_once __DIR__ . '/UrlHelper.php';

class SessionManager {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session settings
            ini_set('session.cookie_httponly', 1); // Prevent XSS access to cookies
            ini_set('session.use_only_cookies', 1); // Prevent session ID in URL
            session_set_cookie_params([
                'path' => '/',
                'domain' => '', // Default domain
                'secure' => (getenv('APP_ENV') === 'production'), // Auto-enable for production
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            // ini_set('session.cookie_secure', 1); // Enable this if you are using HTTPS
            
            session_start();
        }
    }

    public static function set($key, $value) {
        self::startSession();
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        self::startSession();
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key) {
        self::startSession();
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        self::startSession();
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            // Redirect to home/login page or send 401 Unauthorized
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                 header('Content-Type: application/json');
                 echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                 exit;
            } else {
                 header("Location: " . UrlHelper::getFullUrl('/public/index.php?page=home&login=true')); 
                 exit;
            }
        }
    }
    
    // Check if user is already logged in (for login/signup pages)
    public static function requireGuest() {
        if (self::isLoggedIn()) {
            header("Location: " . UrlHelper::getFullUrl('/public/index.php?page=home'));
            exit;
        }
    }
}
