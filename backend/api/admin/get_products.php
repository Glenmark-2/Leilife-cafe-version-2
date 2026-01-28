<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../services/ProductService.php';

$productService = new ProductService();

try {
    // Get all products (including archived) for the admin mobile app
    $products = $productService->getAllProductsAdmin(['is_archived' => 'all']);

    echo json_encode([
        "status" => "success",
        "data" => $products
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
