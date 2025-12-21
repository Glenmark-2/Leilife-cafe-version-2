<?php
require_once __DIR__ . '/../repositories/InboxRepository.php';

class InboxService {
    private $inboxRepo;

    public function __construct() {
        $this->inboxRepo = new InboxRepository();
    }

    public function sendMessage($data) {
        // Validation: only email and message are strictly required
        if (empty($data['email']) || empty($data['message'])) {
            return false;
        }
        return $this->inboxRepo->create($data);
    }

    public function getInboxMessages() {
        return $this->inboxRepo->getAllMessages();
    }

    public function markAsRead($id) {
        return $this->inboxRepo->updateStatus($id, 'read');
    }

    public function archiveMessage($id, $isArchived) {
        return $this->inboxRepo->updateArchivedStatus($id, $isArchived);
    }
}
