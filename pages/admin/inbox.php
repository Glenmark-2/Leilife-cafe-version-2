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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ellie</td>
                    <td>Manager@gmail.com</td>
                    <td>Basta</td>
                    <td>Oct 28, 2025 09:46 PM</td>
                    <td>
                        <button title="edit" type="button" class="edit-btn"><img src="../public/assets/pencil.png" class="edit-icon"></button>
                        <button title="archive" type="button" class="archive-btn"><img src="../public/assets/archive.png" class="archive-icon"></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>