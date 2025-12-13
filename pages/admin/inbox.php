<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Inbox</p>
    </div>
    <button type="button" class="btn-primary-custom btns">View Archive</button>
</div>

<div id="search_add">
    <form class="search-bar" role="search" style="margin-bottom: 0;">
        <input type="search" id="search-input" placeholder="Search messages" aria-label="Search staff">
    </form>
    <select id="sortInbox" class="btn-primary-custom btns">
        <option value="unread">Unread</option>
        <option value="date">Newest</option>
        <option value="type">By Type</option>
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
                    <th class="actionsCol">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="nameCol">Ellie</td>
                    <td class="emailCol">Manager@gmail.com</td>
                    <td class="subCol">Basta</td>
                    <td class="dateCol">Oct 28, 2025 09:46 PM</td>
                    <td class="actionsCol">
                        <div class="action-header-buttons">
                            <button title="edit" type="button" class="edit-btn"><img src="../public/assets/pencil.png" class="edit-icon"></button>
                            <button title="archive" type="button" class="archive-btn"><img src="../public/assets/archive.png" class="archive-icon"></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>