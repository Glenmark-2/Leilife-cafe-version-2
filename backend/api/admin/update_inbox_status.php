<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/InboxService.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or status']);
    exit;
}

$inboxService = new InboxService();
$success = $inboxService->markAsRead($data['id']);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
