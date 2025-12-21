<?php
require_once __DIR__ . '/../../backend/services/InboxService.php';
$inboxService = new InboxService();
$messages = $inboxService->getInboxMessages();
?>

<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Inbox</p>
    </div>
    <button type="button" class="btn-primary-custom btns" id="toggle-archive">View Archive</button>
</div>

<div id="search_add">
    <form class="search-bar" role="search" style="margin-bottom: 0;">
        <input type="search" id="search-input" placeholder="Search messages" aria-label="Search staff">
    </form>
    <select id="sortInbox" class="btn-primary-custom btns">
        <option value="date">Newest</option>
        <option value="unread">Unread</option>
    </select>
</div>

<div class="table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th class="nameCol">Name</th>
                    <th class="emailCol">Email</th>
                    <th class="subCol">Subject</th>
                    <th class="dateCol">Date</th>
                    <th class="actionsCol" style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="inbox-body">
                <!-- Data will be populated by JS -->
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div id="pagination" class="d-flex justify-content-between align-items-center mt-3">
        <span id="page-info">Showing 0 of 0 messages</span>
        <div class="pagination-buttons d-flex gap-2">
            <button id="prevPage" class="btn btn-sm btn-outline-secondary">Previous</button>
            <button id="nextPage" class="btn btn-sm btn-outline-secondary">Next</button>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" aria-labelledby="viewMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #eee; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold" id="viewMessageModalLabel">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">From</label>
                    <p id="msg-from" class="mb-0 fw-bold"></p>
                    <p id="msg-email" class="text-primary"></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Subject</label>
                    <p id="msg-subject" class="mb-0"></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Date</label>
                    <p id="msg-date" class="mb-0 text-muted"></p>
                </div>
                <hr>
                <div class="mb-0">
                    <label class="text-muted small text-uppercase fw-bold">Message</label>
                    <div id="msg-body" class="p-3 bg-light rounded" style="white-space: pre-wrap; min-height: 150px;"></div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee;">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger px-4" id="delete-msg-btn">Delete Message</button>
            </div>
        </div>
    </div>
</div>

<script>
    const allMessages = <?php echo json_encode($messages); ?>;
</script>