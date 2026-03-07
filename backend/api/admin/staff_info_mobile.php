<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Essential for mobile/React Native
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../services/StaffService.php';

try {
    $staffService = new StaffService();

    $allStaff = $staffService->getAllStaffs();
    $archivedStaff = $staffService->getArchivedStaffs();

    echo json_encode([
        'success' => true,
        'data' => [
            'active' => $allStaff,
            'archived' => $archivedStaff
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch staff info: ' . $e->getMessage()
    ]);
}
