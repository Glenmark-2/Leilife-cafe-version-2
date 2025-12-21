<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/InboxService.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['is_archived'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or Archive Status']);
    exit;
}

$inboxService = new InboxService();
$success = $inboxService->archiveMessage($data['id'], $data['is_archived']);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to toggle archive status']);
}
