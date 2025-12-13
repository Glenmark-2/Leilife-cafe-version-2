<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Staff Management</p>
    </div>
    <button type="button" class="btn-primary-custom btns">View Archive</button>
</div>

<div id="search_add">
    <form class="search-bar" role="search" style="margin-bottom: 0;">
        <input type="search" id="search-input" placeholder="Search staff name" aria-label="Search staff">
    </form>
    <div class="add-container">
        <button type="button" id="add-staff" class="btn-primary-custom btns">Add staff</button>
    </div>
</div>

<div class="table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th class="nameCol">Name</th>
                    <th class="posCol">Position</th>
                    <th class="shiftCol">Shift</th>
                    <th class="statCol">Status</th>
                    <th class="actionCol">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="nameCol">
                        <div class="staffNameDiv">
                            <img src="__DIR__./../../public/assets/cheesy_bacon_&_egg.jpeg" alt="" class="staffPhoto">
                            <p>Ellie</p>
                        </div>
                    </td>
                    <td class="posCol">Manager</td>
                    <td class="shiftCol">Day</td>
                    <td class="statCol">Available</td>
                    <td class="actionCol">
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

<!-- add staff modal -->
<div class="modal fade" id="addStaffModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="staffForm" action="your_backend_script.php" method="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">Add Staff Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3 text-center">
                        <label for="staffPicture" class="form-label d-block">Upload Photo</label>
                        <input type="file" class="form-control mx-auto" id="staffPicture" style="width: 80%;" accept="image/*" required>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="staffRole" class="form-label fw-bold">Select Role *</label>
                        <select class="form-select" id="staffRole" name="role" required>
                            <option value="" selected disabled>-- Select Staff Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Driver">Driver</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>

                    <div id="conditional-fields" style="display: none;">

                        <div class="mb-3 conditional-admin conditional-driver">
                            <label for="fullName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullName" name="fullName">
                        </div>

                        <div class="mb-3 conditional-admin conditional-driver">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username">
                        </div>

                        <div class="mb-3 conditional-admin conditional-driver">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>

                        <div class="mb-3 conditional-admin conditional-driver">
                            <label for="shift" class="form-label">Shift *</label>
                            <select class="form-select" id="shift" name="shift">
                                <option value="" selected disabled>-- Select Shift --</option>
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>

                        <div class="mb-3 conditional-admin conditional-driver">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6">
                            <div id="passwordStrength" class="form-text"></div>
                        </div>

                        <input type="hidden" name="status" value="Active" id="statusField">

                    </div>

                    <div id="staff-only-info" style="display: none;">
                        <div class="mb-3 conditional-admin conditional-driver">
                            <label for="fullName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullName" name="fullName">
                        </div>

                        <div class="mb-3 conditional-staff">
                            <label for="staffPosition" class="form-label">Position *</label>
                            <select class="form-select" id="staffPosition" name="staffPosition">
                                <option value="" selected disabled>-- Select Position --</option>
                                <option value="Manager">Manager</option>
                                <option value="Supervisor">Supervisor</option>
                            </select>
                        </div>

                        <div class="mb-3 conditional-staff conditional-driver">
                            <label for="shift" class="form-label">Shift *</label>
                            <select class="form-select" id="shift" name="shift">
                                <option value="" selected disabled>-- Select Shift --</option>
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>

                        <div class="mb-3 conditional-staff conditional-driver">
                            <label for="status" class="form-label">Status *</label>
                            <input type="text" class="form-control" id="status" name="status" value="Active">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addStaffBtn" disabled>Add Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>