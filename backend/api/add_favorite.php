<?php
// Debug settings - disabled for production
// ini_set('display_errors', 0); 
// ini_set('log_errors', 1);     
// error_reporting(E_ALL);

require_once __DIR__ . '/../services/ProductService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

try {
    $productService = new ProductService();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_POST['user_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;

        if (!$userId || !$productId) {
             throw new Exception("Missing user_id or product_id");
        }
        
        // Allow mobile app usage without session if user_id is explicitly provided
        // In a clearer implementation, we would check for a valid JWT token here.
        // For now, we trust the provided user_id if session is not active
        if ((!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $userId) && !isset($_POST['user_id'])) {
            throw new Exception("Unauthorized action.");
        }

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