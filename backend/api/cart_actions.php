<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Init DB
$database = new Database();
$db = $database->getConnection();

// Init Dependencies
$cartRepo = new CartRepository($db);
$cartService = new CartService($cartRepo);
$controller = new CartController($cartService);

// Handle Request
$controller->handleRequest();
