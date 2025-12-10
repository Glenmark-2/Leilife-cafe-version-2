<?php
require_once __DIR__ . '/../helpers/SessionManager.php';

// Destroy the session
SessionManager::destroy();

// Redirect to home page
header("Location: ../../public/index.php?page=home");
exit;
