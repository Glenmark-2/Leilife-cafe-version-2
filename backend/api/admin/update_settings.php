<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../repositories/SettingsRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

try {
    $repo = new SettingsRepository();

    // Collect data from POST
    $data = $_POST;

    // Handle JSON for opening_hours if provided
    if (isset($data['opening_hours'])) {
        // If it's already a string, assume it's JSON from frontend
        // If it's an array, encode it
        if (is_array($data['opening_hours'])) {
            $data['opening_hours'] = json_encode($data['opening_hours']);
        }
    }

    // Convert booleans to Integers (1 or 0) for Database
    $data['is_store_open'] = isset($data['is_store_open']) && ($data['is_store_open'] === 'true' || $data['is_store_open'] === '1' || $data['is_store_open'] === 1) ? 1 : 0;
    $data['enable_cod'] = isset($data['enable_cod']) && ($data['enable_cod'] === 'true' || $data['enable_cod'] === '1' || $data['enable_cod'] === 1) ? 1 : 0;
    $data['enable_gcash'] = isset($data['enable_gcash']) && ($data['enable_gcash'] === 'true' || $data['enable_gcash'] === '1' || $data['enable_gcash'] === 1) ? 1 : 0;
    $data['enable_pickup'] = isset($data['enable_pickup']) && ($data['enable_pickup'] === 'true' || $data['enable_pickup'] === '1' || $data['enable_pickup'] === 1) ? 1 : 0;
    $data['enable_home_delivery'] = isset($data['enable_home_delivery']) && ($data['enable_home_delivery'] === 'true' || $data['enable_home_delivery'] === '1' || $data['enable_home_delivery'] === 1) ? 1 : 0;

    if ($data['enable_cod'] === 0 && $data['enable_gcash'] === 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "At least one payment method must be enabled."]);
        exit;
    }

    if ($data['enable_pickup'] === 0 && $data['enable_home_delivery'] === 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "At least one mode of delivery must be enabled."]);
        exit;
    }

    $success = $repo->updateSettings($data);

    if ($success) {
        echo json_encode([
            "success" => true,
            "message" => "Settings updated successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No changes made or update failed"
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
