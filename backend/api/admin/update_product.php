<?php
// Debug settings - disabled for production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/ProductService.php';

// Since we are using FormData, we use $_POST and $_FILES
if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is missing']);
    exit;
}

$productId = $_POST['product_id'];
$productService = new ProductService();

// Fetch current product to get old image path
$currentProduct = $productService->getProductById($productId);
if (!$currentProduct) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Basic server-side validation
if (empty($_POST['name']) || strlen($_POST['name']) < 3) {
    echo json_encode(['success' => false, 'message' => 'Product name must be at least 3 characters.']);
    exit;
}

if (!isset($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid price (greater than 0).']);
    exit;
}

$data = [
    'name' => $_POST['name'],
    'description' => $_POST['description'],
    'price' => $_POST['price'],
    'category_id' => $_POST['category_id'],
    'is_available' => $_POST['is_available']
];

// Handle Image Upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileSize = $_FILES['image']['size'];
    $fileType = $_FILES['image']['type'];
    
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    // Sanitize file name: convert spaces to underscores
    $newFileName = str_replace(' ', '_', strtolower($_POST['name'])) . '_' . time() . '.' . $fileExtension;
    
    $uploadFileDir = __DIR__ . '/../../../public/assets/products/';
    $dest_path = $uploadFileDir . $newFileName;
    
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    
    if (in_array($fileExtension, $allowedfileExtensions)) {
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $data['image_path'] = $newFileName;
            
            // Delete old image if it exists and is not a default one
            if (!empty($currentProduct['image_path'])) {
                $oldImagePath = $uploadFileDir . $currentProduct['image_path'];
                if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'There was some error moving the file to upload directory.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions)]);
        exit;
    }
}

$success = $productService->updateProduct($productId, $data);

if ($success) {
    // Return the new image path if updated so frontend can refresh it
    $response = ['success' => true, 'message' => 'Product updated successfully.'];
    if (isset($data['image_path'])) {
        $response['new_image_path'] = $data['image_path'];
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update product database.']);
}
