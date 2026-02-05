<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->value) || !isset($data->field)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit;
}

$value = trim($data->value);
$field = $data->field; // 'email' or 'username'

if ($field === 'email') {
    // Check users, admins, drivers
    $query = "
        SELECT 1 FROM users WHERE email = :email
        UNION ALL
        SELECT 1 FROM admins WHERE email = :email
        UNION ALL
        SELECT 1 FROM drivers WHERE email = :email
        LIMIT 1
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $value);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'available' => false, 'message' => 'Email is already in use.']);
    } else {
        echo json_encode(['success' => true, 'available' => true, 'message' => 'Email is available.']);
    }
} elseif ($field === 'username') {
    // Check admins, drivers (users table doesn't have username)
    $query = "
        SELECT 1 FROM admins WHERE username = :username
        UNION ALL
        SELECT 1 FROM drivers WHERE username = :username
        LIMIT 1
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $value);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'available' => false, 'message' => 'Username is already taken.']);
    } else {
        echo json_encode(['success' => true, 'available' => true, 'message' => 'Username is available.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid field type.']);
}
