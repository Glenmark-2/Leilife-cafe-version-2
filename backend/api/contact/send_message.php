<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/InboxService.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Fallback to $_POST if not JSON
    $data = $_POST;
}

if (!isset($data['email']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Email and Message are required.']);
    exit;
}

$inboxService = new InboxService();
$success = $inboxService->sendMessage([
    'name' => htmlspecialchars($data['name']),
    'email' => htmlspecialchars($data['email']),
    'subject' => htmlspecialchars($data['subject'] ?? 'No Subject'),
    'message' => htmlspecialchars($data['message'])
]);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}
