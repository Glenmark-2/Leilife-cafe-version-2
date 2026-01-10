<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../backend/services/StaffService.php';
$staffService = new StaffService();
$allStaff = $staffService->getAllStaffs();
$archivedStaff = $staffService->getArchivedStaffs();
?>

<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Staff Management</p>
    </div>
    <button type="button" class="btn-primary-custom btns" id="toggle-archive">View Archive</button>
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
            <tbody id="staff-body">
                <!-- Data will be populated by JS -->
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <!-- Pagination -->
    <!-- Pagination -->
    <div class="pagination-container">
        <button id="prevPage" class="btn-primary-custom" disabled>Previous</button>
        <span id="page-info">Showing 0 of 0 staff</span>
        <button id="nextPage" class="btn-primary-custom" disabled>Next</button>
    </div>
</div>

<script>
    const initialStaff = <?php echo json_encode($allStaff); ?>;
    const initialArchived = <?php echo json_encode($archivedStaff); ?>;
</script>

<!-- add staff modal -->
<div class="modal fade" id="addStaffModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <form id="staffForm" novalidate data-mode="add">
                <input type="hidden" id="staff_id" name="staff_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addStaffModalLabel">Add Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- 1. ASK FIRST: Account Type -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Account Type *</label>
                        <div class="d-flex gap-3 p-2 bg-light rounded border">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accountCategory" id="categoryStaff" value="staff" checked>
                                <label class="form-check-label" for="categoryStaff">Staff Account</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accountCategory" id="categoryAdminDriver" value="admin_driver">
                                <label class="form-check-label" for="categoryAdminDriver">Admin/Driver Account</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 text-center">
                        <label for="staffPicture" class="form-label d-block fw-bold">Photo Profile</label>
                        <div class="mb-2">
                            <img id="staff-img-preview" src="<?= UrlHelper::getBaseUrl() ?>/public/assets/default_user.png" alt="Preview" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #d0b28c;">
                        </div>
                        <input type="file" class="form-control form-control-sm mx-auto" id="staffPicture" name="staffPicture" style="max-width: 250px;" accept="image/*">
                    </div>

                    <hr>

                    <!-- Common Fields -->
                    <div class="mb-3">
                        <label for="fullName" class="form-label fw-bold">Full Name *</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" required placeholder="Enter full name">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="staffRole" class="form-label fw-bold">Role *</label>
                            <select class="form-select" id="staffRole" name="role" required>
                                <!-- Populated dynamically by JS based on category -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shift" class="form-label fw-bold">Shift *</label>
                            <select class="form-select" id="shift" name="shift" required>
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Active">Active</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Admin/Driver Specific Fields -->
                    <div id="auth-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold font-monospace">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="At least 8 characters">
                            <div id="passwordStrength" class="form-text small mt-1"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4" id="saveStaffBtn">Save Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>