document.addEventListener('DOMContentLoaded', () => {
    const inboxTableBody = document.getElementById('inbox-body');
    const searchInput = document.getElementById('search-input');
    const sortSelect = document.getElementById('sortInbox');
    const toggleArchiveBtn = document.getElementById('toggle-archive');
    const pageInfo = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');

    let messages = [...allMessages];
    let filteredMessages = [];
    let showingArchived = false;

    // Pagination variables
    let currentPage = 1;
    const itemsPerPage = 10;

    function renderTable() {
        inboxTableBody.innerHTML = '';

        const totalItems = filteredMessages.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        // Update pagination buttons state
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;

        // Slice data for current page
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageItems = filteredMessages.slice(start, end);

        // Update page info
        if (totalItems === 0) {
            pageInfo.innerText = 'Showing 0 of 0 messages';
            inboxTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px;">No ${showingArchived ? 'archived' : ''} messages found.</td></tr>`;
            return;
        }

        pageInfo.innerText = `Showing ${start + 1} to ${Math.min(end, totalItems)} of ${totalItems} messages`;

        pageItems.forEach(msg => {
            const row = document.createElement('tr');
            if (msg.status === 'unread') {
                row.style.fontWeight = 'bold';
                row.style.backgroundColor = '#f9f9f9';
            }

            const dateStr = new Date(msg.created_at).toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const archiveIcon = msg.is_archived == 1 ? 'bi-arrow-counterclockwise' : 'bi-archive';
            const archiveTitle = msg.is_archived == 1 ? 'Restore' : 'Archive';

            row.innerHTML = `
                <td class="nameCol">${msg.name || '<span class="text-muted">N/A</span>'}</td>
                <td class="emailCol">${msg.email}</td>
                <td class="subCol">${msg.subject || 'No Subject'}</td>
                <td class="dateCol">${dateStr}</td>
                <td class="actionsCol">
                    <div class="action-header-buttons">
                        <i class="bi bi-eye" style="font-size: 1.25rem; color: #d0b28c; cursor: pointer;" onclick="viewMessage(${msg.inbox_id})" title="View Message"></i>
                        <i class="bi ${archiveIcon}" style="font-size: 1.25rem; color: #6c757d; cursor: pointer;" onclick="toggleArchive(${msg.inbox_id}, ${msg.is_archived})" title="${archiveTitle}"></i>
                    </div>
                </td>
            `;
            inboxTableBody.appendChild(row);
        });
    }

    function filterMessages() {
        const searchTerm = (searchInput.value || '').toLowerCase();
        const sortBy = sortSelect.value;

        filteredMessages = messages.filter(msg => {
            const matchesSearch = (msg.name || '').toLowerCase().includes(searchTerm) ||
                (msg.email || '').toLowerCase().includes(searchTerm) ||
                (msg.subject && msg.subject.toLowerCase().includes(searchTerm)) ||
                (msg.message || '').toLowerCase().includes(searchTerm);

            const matchesArchive = (msg.is_archived == 1) == showingArchived;

            if (sortBy === 'unread') {
                return matchesSearch && matchesArchive && msg.status === 'unread';
            }

            return matchesSearch && matchesArchive;
        });

        if (sortBy === 'date' || !sortBy) {
            filteredMessages.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        }

        currentPage = 1; // Reset to first page on filter/sort change
        renderTable();
    }

    // Pagination Listeners
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredMessages.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });

    searchInput.addEventListener('input', filterMessages);
    sortSelect.addEventListener('change', filterMessages);

    toggleArchiveBtn.addEventListener('click', () => {
        showingArchived = !showingArchived;
        toggleArchiveBtn.innerText = showingArchived ? 'View Active' : 'View Archive';
        filterMessages();
    });

    window.viewMessage = function (id) {
        const msg = messages.find(m => m.inbox_id == id);
        if (!msg) return;

        // Populate Modal
        document.getElementById('msg-from').innerText = msg.name || 'N/A';
        document.getElementById('msg-email').innerText = msg.email;
        document.getElementById('msg-subject').innerText = msg.subject || 'No Subject';

        // Format date into words for modal: e.g. "October 28, 2025"
        const modalDate = new Date(msg.created_at).toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('msg-date').innerText = modalDate;
        document.getElementById('msg-body').innerText = msg.message;

        // Setup archive button in modal
        const archiveBtn = document.getElementById('delete-msg-btn');
        archiveBtn.innerText = msg.is_archived == 1 ? 'Restore Message' : 'Archive Message';
        archiveBtn.className = msg.is_archived == 1 ? 'btn btn-success px-4' : 'btn btn-danger px-4';

        const currentModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewMessageModal'));

        archiveBtn.onclick = () => {
            currentModal.hide();
            toggleArchive(msg.inbox_id, msg.is_archived);
        };

        currentModal.show();

        // Mark as read if unread
        if (msg.status === 'unread') {
            fetch('../backend/api/admin/update_inbox_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: 'read' })
            }).then(res => res.json()).then(result => {
                if (result.success) {
                    msg.status = 'read';
                    renderTable();
                }
            });
        }
    };

    window.toggleArchive = function (id, currentStatus) {
        const newStatus = currentStatus == 1 ? 0 : 1;
        const confirmMsg = newStatus == 1 ? 'Are you sure you want to archive this message?' : 'Are you sure you want to restore this message?';

        if (!confirm(confirmMsg)) return;

        fetch('../backend/api/admin/archive_inbox_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, is_archived: newStatus })
        }).then(res => res.json()).then(result => {
            if (result.success) {
                const msgIndex = messages.findIndex(m => m.inbox_id == id);
                if (msgIndex !== -1) {
                    messages[msgIndex].is_archived = newStatus;
                }
                filterMessages();
            } else {
                alert(result.message || 'Failed to update message status.');
            }
        });
    };

    // Initial filter application
    filterMessages();
});
