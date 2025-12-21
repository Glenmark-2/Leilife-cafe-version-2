<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/ProductService.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['is_archived'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$productId = $input['product_id'];
$isArchived = $input['is_archived'];

$productService = new ProductService();
$result = $productService->archiveProduct($productId, $isArchived);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Product status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update product status']);
}
