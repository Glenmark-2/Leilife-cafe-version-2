<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../services/ProductService.php';

$productService = new ProductService();

try {
    $rawCategories = $productService->getAllCategories();

    // "All" entry first
    $allEntry = [
        "category_id" => "all",
        "name" => "All",
        "parent_id" => null
    ];

    // Separate main categories and subcategories
    $mainCategories = array_filter($rawCategories, function ($cat) {
        return $cat['parent_id'] === null;
    });

    $subCategories = array_filter($rawCategories, function ($cat) {
        return $cat['parent_id'] !== null;
    });

    // Merge in order: All, Main Categories, Sub Categories
    $sortedCategories = array_merge(
        [$allEntry],
        array_values($mainCategories),
        array_values($subCategories)
    );

    echo json_encode([
        "status" => "success",
        "data" => $sortedCategories
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
