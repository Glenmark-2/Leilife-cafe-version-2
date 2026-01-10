<?php

require_once __DIR__ . '/../services/CartService.php';

class CartController {
    private $cartService;

    public function __construct(CartService $cartService) {
        $this->cartService = $cartService;
    }

    public function handleRequest() {
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $this->sendResponse(false, 'User not logged in', null, 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        $input = json_decode(file_get_contents("php://input"), true);
        $action = isset($_GET['action']) ? $_GET['action'] : (isset($input['action']) ? $input['action'] : '');

        switch ($action) {
            case 'get_cart':
                $this->getCart($userId);
                break;
            case 'add_item':
                $this->addItem($userId, $input);
                break;
            case 'update_qty':
                $this->updateQty($userId, $input);
                break;
            case 'remove_item':
                $this->removeItem($userId, $input);
                break;
            case 'merge_cart':
                $this->mergeCart($userId, $input);
                break;
            default:
                $this->sendResponse(false, 'Invalid action', null, 400);
                break;
        }
    }

    private function getCart($userId) {
        $cart = $this->cartService->getCart($userId);
        
        // Format for frontend
        $formattedItems = [];
        if ($cart && !empty($cart->items)) {
            foreach ($cart->items as $item) {
                // Map to frontend expected structure
                $itemData = [
                    'id' => $item->product_id,
                    'name' => $item->product_name,
                    'price' => (float)$item->product_price,
                    'qty' => (int)$item->quantity,
                    'image' => $item->product_image
                ];
                // Debug log
                // error_log("CartItem: " . json_encode($itemData));
                $formattedItems[] = $itemData;
            }
        }

        $this->sendResponse(true, 'Cart fetched', $formattedItems);
    }

    private function addItem($userId, $input) {
        if (!isset($input['product_id']) || !isset($input['qty'])) {
            $this->sendResponse(false, 'Missing parameters');
            return;
        }
        
        $success = $this->cartService->addItem($userId, $input['product_id'], $input['qty']);
        if ($success) {
            // Return updated cart
            $this->getCart($userId);
        } else {
            $this->sendResponse(false, 'Failed to add item');
        }
    }

    private function updateQty($userId, $input) {
        if (!isset($input['product_id']) || !isset($input['qty'])) {
            $this->sendResponse(false, 'Missing parameters');
            return;
        }

        $success = $this->cartService->updateItemQty($userId, $input['product_id'], $input['qty']);
        if ($success) {
             $this->getCart($userId);
        } else {
            $this->sendResponse(false, 'Failed to update quantity');
        }
    }

    private function removeItem($userId, $input) {
        if (!isset($input['product_id'])) {
            $this->sendResponse(false, 'Missing parameters');
            return;
        }

        $success = $this->cartService->removeItem($userId, $input['product_id']);
        if ($success) {
             $this->getCart($userId);
        } else {
            $this->sendResponse(false, 'Failed to remove item');
        }
    }

    private function mergeCart($userId, $input) {
        // Input should contain 'items' array
        $items = isset($input['items']) ? $input['items'] : [];
        if (!empty($items)) {
            $this->cartService->mergeCart($userId, $items);
        }
        // Return final cart
        $this->getCart($userId);
    }

    private function sendResponse($success, $message, $data = null, $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'cart' => $data
        ]);
        exit;
    }
}
