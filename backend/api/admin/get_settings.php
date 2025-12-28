<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../repositories/SettingsRepository.php';

try {
    $repo = new SettingsRepository();
    $settings = $repo->getSettings();

    if ($settings) {
        // Decode JSON opening hours if it's a string
        if (isset($settings['opening_hours']) && is_string($settings['opening_hours'])) {
            $settings['opening_hours'] = json_decode($settings['opening_hours'], true);
        }

        echo json_encode([
            "success" => true,
            "data" => $settings
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Settings not found"
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
