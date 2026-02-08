<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../../repositories/InboxRepository.php';

try {
    $repo = new InboxRepository();
    $messages = $repo->getAllMessages();
    echo json_encode([
        "success" => true,
        "messages" => $messages
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
