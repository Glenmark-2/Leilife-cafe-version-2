<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/StaffService.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$staffService = new StaffService();
$staff = $staffService->getStaff($_GET['id']);

if ($staff) {
    echo json_encode(['success' => true, 'data' => $staff]);
} else {
    echo json_encode(['success' => false, 'message' => 'Staff not found']);
}
