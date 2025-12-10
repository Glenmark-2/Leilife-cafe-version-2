<style>
    #search_add {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        width: 100%;
        margin: 10px 0;
    }

    #search_add form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 220px;
        height: 42px;
        box-sizing: border-box;
    }

    #search_add label {
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        display: flex;
        align-items: center;
        height: 100%;
        box-sizing: border-box;
    }

    #search-input {
        flex: 1;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: 42px;
        line-height: 42px;
        box-sizing: border-box;
        width: 90%;
        min-height: 20%;
    }

    #search-input:focus {
        box-shadow: 0 0 0 2px rgba(80, 194, 86, 0.2);
    }

    #search_add>div {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 42px;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    table {
        width: 100%;
    }

    .table-wrapper {
        padding: 10px;
        border-radius: 10px;
        background-color: white;

    }

    th {
        font-size: 1rem;
        padding: 10px;
        border-bottom: 1px solid gray;
    }

    td {
        padding: 10px;
    }

    .archive-icon,
    .edit-icon {
        width: 30px;
        height: 30px;
    }

    .archive-btn,
    .edit-btn {
        background-color: transparent;
        border: none;
    }

    .modal-body {
    min-height: 70vh; /* Set to 80% of viewport height to force scroll */
    overflow-y: auto;
}
</style>
<div id="title-content">
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
                    <th>Name</th>
                    <th>Position</th>
                    <th>Shift</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ellie</td>
                    <td>Manager</td>
                    <td>Day</td>
                    <td>Available</td>
                    <td>
                        <button title="edit" type="button" class="edit-btn"><img src="__DIR_ ./../../public/assets/pencil.png" class="edit-icon"></button>
                        <button title="archive" type="button" class="archive-btn"><img src="__DIR_ ./../../public/assets/archive.png" class="archive-icon"></button>
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
<script>
    const staffModal = new bootstrap.Modal(document.getElementById('addStaffModal'));
    document.getElementById('add-staff').addEventListener('click', function(e) {
        e.preventDefault();
        staffModal.show();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('staffRole');
        const conditionalFields = document.getElementById('conditional-fields');
        const staffOnlyInfo = document.getElementById('staff-only-info');
        const form = document.getElementById('staffForm');
        const addStaffBtn = document.getElementById('addStaffBtn');
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');

        function updateFormVisibility(selectedRole) {
            conditionalFields.style.display = 'none';
            staffOnlyInfo.style.display = 'none';

            const allConditionalInputs = form.querySelectorAll('#conditional-fields input, #conditional-fields select');
            allConditionalInputs.forEach(input => {
                input.removeAttribute('required');
                input.disabled = true;
            });

            // Determine which fields to show and require
            let showFields = [];
            let enableSubmit = true;

            if (selectedRole === 'Admin' || selectedRole === 'Driver') {
                conditionalFields.style.display = 'block';

                if (selectedRole === 'Admin') {
                    showFields = form.querySelectorAll('.conditional-admin');
                } else if (selectedRole === 'Driver') {
                    showFields = form.querySelectorAll('.conditional-driver');
                }

                // Enable and set required for the selected fields
                showFields.forEach(field => {
                    const input = field.querySelector('input, select');
                    if (input) {
                        input.setAttribute('required', 'required');
                        input.disabled = false;
                    }
                    field.style.display = 'block';
                });

                // Hide the fields not relevant for the current role
                const otherFields = form.querySelectorAll('#conditional-fields > div');
                otherFields.forEach(field => {
                    if (!showFields.includes(field)) {
                        field.style.display = 'none';
                    }
                });

            } else if (selectedRole === 'Staff') {
                staffOnlyInfo.style.display = 'block';
                enableSubmit = true;
            } else {
                enableSubmit = false;
            }

            // Final control of the submit button
            addStaffBtn.disabled = !enableSubmit;
        }

        function checkPasswordStrength() {
            const password = passwordInput.value;
            let strength = '';

            if (password.length === 0) {
                passwordStrength.textContent = '';
                passwordStrength.className = 'form-text';
                return;
            }

            if (password.length < 8) {
                strength = 'Weak (Min 8 characters)';
                passwordStrength.className = 'form-text text-danger';
            } else if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                strength = 'Medium';
                passwordStrength.className = 'form-text text-warning';
            } else {
                strength = 'Strong';
                passwordStrength.className = 'form-text text-success';
            }
            passwordStrength.textContent = `Strength: ${strength}`;
        }

        // Event Listeners
        roleSelect.addEventListener('change', (e) => updateFormVisibility(e.target.value));
        passwordInput.addEventListener('input', checkPasswordStrength);

        // Initial setup (if you want the submit button disabled until a role is selected)
        updateFormVisibility(roleSelect.value);
    });
</script>