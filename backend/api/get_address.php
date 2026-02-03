<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if(!$user_id) {
     echo json_encode(['success' => false, 'message' => 'User ID is required']);
     exit;
}

$query = "SELECT * FROM user_addresses WHERE user_id = :user_id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$address = $stmt->fetch(PDO::FETCH_ASSOC);

if ($address) {
    echo json_encode([
        'status' => 'success',
        'success' => true,
        'address' => [
            'street' => $address['street'],
            'city' => $address['city'],
            'unit' => $address['barangay'], 
            'instructions' => '' 
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'success', 
        'success' => true, 
        'address' => [] 
    ]);
}
?>
