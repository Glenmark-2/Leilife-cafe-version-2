<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';

$database = new Database();
$db = $database->getConnection();

$productRepo = new ProductRepository($db);

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if(!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$favorites = $productRepo->getFavoritesByUserId($user_id);

$mappedFavorites = [];
if($favorites) {
    foreach($favorites as $fav) {
        $mappedFavorites[] = [
            'id' => $fav['product_id'],
            'name' => $fav['product_name'],
            'price' => $fav['price'],
            'image' => $fav['image_path'], 
            'qty' => 0 
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'success' => true,
    'products' => $mappedFavorites
]);
?>
