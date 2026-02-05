<?php
// Debug settings - disabled for production
// ini_set('display_errors', 0); 
// ini_set('log_errors', 1);     
// error_reporting(E_ALL);

require_once __DIR__ . '/../services/ProductService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    $productService = new ProductService();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_POST['user_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;

        if (!$userId || !$productId) {
             throw new Exception("Missing user_id or product_id");
        }
        
        /* 
        // Commented out session check for mobile app compatibility. 
        // Mobile apps often don't persist PHP session cookies automatically.
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $userId) {
            throw new Exception("Unauthorized action.");
        }
        */

        // Logic for toggle
        $isFav = $productService->isFavorite($userId, $productId);
        
        if ($isFav) {
            $result = $productService->removeFavorite($userId, $productId);
            if ($result) {
                echo json_encode(["success" => true, "action" => "removed"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to remove favorite."]);
            }
        } else {
            $result = $productService->addFavorite($userId, $productId);
            if ($result) {
                echo json_encode(["success" => true, "action" => "added"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add favorite."]);
            }
        }

    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}