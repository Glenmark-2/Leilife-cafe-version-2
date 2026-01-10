<?php

class UrlHelper
{
    /**
     * Get the base URL of the application dynamically.
     * Detects protocol, host, and subfolder automatically.
     */
    public static function getBaseUrl()
    {
        // 1. Detect Protocol
        $protocol = 'http://';
        if (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443 ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ) {
            $protocol = 'https://';
        }

        // 2. Detect Host
        $host = $_SERVER['HTTP_HOST'];

        // 3. Detect Base Path
        // SCRIPT_NAME is usually /project_folder/public/index.php or /project_folder/backend/api/file.php
        $scriptName = $_SERVER['SCRIPT_NAME'];
        
        // Remove specific directories to get the project root
        // We look for common entry points and strip everything from there.
        $basePath = $scriptName;
        $targets = ['/public', '/backend', '/api'];
        
        foreach ($targets as $target) {
            if (($pos = strpos($basePath, $target)) !== false) {
                $basePath = substr($basePath, 0, $pos);
                break;
            }
        }

        // If no target folders found, we might be at root but with a filename (e.g. /index.php)
        // If basePath ends in .php, strip the filename
        if (substr($basePath, -4) === '.php') {
            $basePath = dirname($basePath);
        }
        
        // Handle case where it might be in root (e.g. /index.php)
        if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
            $basePath = '';
        }

        return $protocol . $host . rtrim($basePath, '/');
    }

    /**
     * Get a full URL for a given path relative to the project root.
     */
    public static function getFullUrl($path)
    {
        return self::getBaseUrl() . '/' . ltrim($path, '/');
    }
}
