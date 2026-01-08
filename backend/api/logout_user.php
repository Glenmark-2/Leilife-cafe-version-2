<?php
require_once __DIR__ . '/../helpers/SessionManager.php';
require_once __DIR__ . '/../helpers/UrlHelper.php';

// Destroy the session
SessionManager::destroy();

// Redirect to home page
header("Location: " . UrlHelper::getFullUrl('/public/index.php?page=home'));
exit;
