<?php
// Debug settings - disabled for production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/ProductService.php';

$productService = new ProductService();

// Basic server-side validation
if (empty($_POST['name']) || strlen($_POST['name']) < 3) {
    echo json_encode(['success' => false, 'message' => 'Product name must be at least 3 characters.']);
    exit;
}

if (!isset($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid price (greater than 0).']);
    exit;
}

if (empty($_POST['category_id'])) {
    echo json_encode(['success' => false, 'message' => 'Category is required.']);
    exit;
}

$data = [
    'name' => $_POST['name'],
    'description' => $_POST['description'] ?? '',
    'price' => $_POST['price'],
    'category_id' => $_POST['category_id'],
    'is_available' => $_POST['is_available'] ?? 1,
    'image_path' => 'not_available.png' // Default image
];

// Handle Image Upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
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
        } else {
            echo json_encode(['success' => false, 'message' => 'Error moving the uploaded file.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(',', $allowedfileExtensions)]);
        exit;
    }
}

$productId = $productService->createProduct($data);

if ($productId) {
    echo json_encode(['success' => true, 'message' => 'Product added successfully.', 'product_id' => $productId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to database.']);
}
