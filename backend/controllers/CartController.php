<?php

require_once __DIR__ . '/../services/CartService.php';

class CartController
{
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function handleRequest()
    {
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $input = json_decode(file_get_contents("php://input"), true);
        $action = isset($_GET['action']) ? $_GET['action'] : (isset($input['action']) ? $input['action'] : '');

        // Support both session (web) and user_id parameter (mobile)
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($input['user_id']) ? $input['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : null));

        if (!$userId && $action !== 'check_guest_availability') {
            $this->sendResponse(false, 'User not logged in or User ID missing', null, 401);
            return;
        }

        switch ($action) {
            case 'check_guest_availability':
                $this->checkGuestAvailability($input);
                break;
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
            case 'clear_cart':
                $this->clearCart($userId);
                break;
            case 'merge_cart':
                $this->mergeCart($userId, $input);
                break;
            default:
                $this->sendResponse(false, 'Invalid action: ' . $action, null, 400);
                break;
        }
    }

    private function clearCart($userId)
    {
        $success = $this->cartService->clearCart($userId);
        if ($success) {
            $this->getCart($userId);
        } else {
            $this->sendResponse(false, 'Failed to clear cart');
        }
    }

    private function getCart($userId)
    {
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
                    'image' => $item->product_image,
                    'is_available' => (bool)$item->is_available
                ];
                // Debug log
                // error_log("CartItem: " . json_encode($itemData));
                $formattedItems[] = $itemData;
            }
        }

        $this->sendResponse(true, 'Cart fetched', $formattedItems);
    }

    private function addItem($userId, $input)
    {
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

    private function updateQty($userId, $input)
    {
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

    private function removeItem($userId, $input)
    {
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

    private function mergeCart($userId, $input)
    {
        // Input should contain 'items' array
        $items = isset($input['items']) ? $input['items'] : [];
        if (!empty($items)) {
            $this->cartService->mergeCart($userId, $items);
        }
        // Return final cart
        $this->getCart($userId);
    }

    private function checkGuestAvailability($input)
    {
        $items = isset($input['items']) ? $input['items'] : [];
        if (empty($items)) {
            $this->sendResponse(true, 'No items to check', []);
            return;
        }

        // We need ProductRepository to check availability
        require_once __DIR__ . '/../repositories/ProductRepository.php';
        $productRepo = new ProductRepository();

        $results = [];
        foreach ($items as $item) {
            $product = $productRepo->findById($item['id']);
            $results[] = [
                'id' => $item['id'],
                'name' => $item['name'] ?? ($product ? $product['name'] : 'Unknown'),
                'price' => (float)($product ? $product['price'] : ($item['price'] ?? 0)),
                'qty' => (int)$item['qty'],
                'image' => $product ? $product['image_path'] : ($item['image'] ?? null),
                'is_available' => $product ? (bool)$product['is_available'] : false
            ];
        }

        $this->sendResponse(true, 'Availability checked', $results);
    }

    private function sendResponse($success, $message, $data = null, $code = 200)
    {
        http_response_code($code);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'cart' => $data
        ]);
        exit;
    }
}
