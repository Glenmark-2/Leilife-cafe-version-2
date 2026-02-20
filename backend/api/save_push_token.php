<?php
// Let's force errors to show if they happen so we can see them in the mobile terminal
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Use YOUR specific paths
require_once __DIR__ . '/../config/Database.php';

// Initialize Database
$database = new Database();
// CHECK THIS: In your other files, is it $database->connect() or $database->getConnection()?
$db = $database->getConnection(); 

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->user_id) && !empty($data->push_token)) {
    try {
        $role = $data->role ?? 'customer';
        $table = 'users';
        $idColumn = 'id';

        if ($role === 'admin') {
            $table = 'admins';
            $idColumn = 'staff_id';
            // Ensure column exists (one-time check/add)
            try { $db->exec("ALTER TABLE admins ADD COLUMN push_token TEXT NULL"); } catch (Exception $e) {}
        } elseif ($role === 'driver') {
            $table = 'drivers';
            $idColumn = 'driver_id';
            // Ensure column exists
            try { $db->exec("ALTER TABLE drivers ADD COLUMN push_token TEXT NULL"); } catch (Exception $e) {}
        }

        $query = "UPDATE $table SET push_token = ? WHERE $idColumn = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->push_token, $data->user_id])) {
            echo json_encode(["status" => "success", "success" => true, "message" => "Token saved for $role."]);
        } else {
            echo json_encode(["status" => "error", "success" => false, "message" => "Update failed."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing user_id or push_token"]);
}