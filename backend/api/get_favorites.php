<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../services/ProductService.php';

try {
    $productService = new ProductService();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = $_GET['user_id'] ?? null;
        
        if (!$userId) {
            throw new Exception("User ID is required");
        }
        
        $favorites = $productService->getUserFavorites($userId);
        
        $mappedFavorites = array_map(function($fav) {
            return [
                'id' => $fav['product_id'],
                'name' => $fav['product_name'],
                'price' => (float)$fav['price'],
                'image' => $fav['image_path']
            ];
        }, $favorites);
        
        echo json_encode([
            "status" => "success",
            "products" => $mappedFavorites
        ]);
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
