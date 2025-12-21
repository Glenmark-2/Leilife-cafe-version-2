<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/StaffService.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['is_archived'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or archive status.']);
    exit;
}

$staffService = new StaffService();
$success = $staffService->archiveStaff($data['id'], $data['is_archived']);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update archive status.']);
}
