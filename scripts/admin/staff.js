document.addEventListener('DOMContentLoaded', function () {
    // --- Modal Initialization ---
    const addStaffModalEl = document.getElementById('addStaffModal');
    let staffModal;
    if (addStaffModalEl) {
        staffModal = new bootstrap.Modal(addStaffModalEl);
    }

    const form = document.getElementById('staffForm');
    const modalTitle = document.getElementById('addStaffModalLabel');
    const saveBtn = document.getElementById('saveStaffBtn');
    const staffImgPreview = document.getElementById('staff-img-preview');
    const accountCategoryRadios = document.querySelectorAll('input[name="accountCategory"]');
    const roleSelect = document.getElementById('staffRole');
    const authFields = document.getElementById('auth-fields');
    const staffPicture = document.getElementById('staffPicture');
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('passwordStrength');

    const addStaffBtnTrigger = document.getElementById('add-staff');
    if (addStaffBtnTrigger) {
        addStaffBtnTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            form.reset();
            form.dataset.mode = 'add';
            modalTitle.innerText = 'Add Staff Member';
            saveBtn.innerText = 'Save Staff';
            document.getElementById('staff_id').value = '';
            staffImgPreview.src = `${window.BASE_URL}/public/assets/default_user.png`;
            passwordInput.setAttribute('required', 'required');
            updateCategory();
            if (staffModal) staffModal.show();
        });
    }

    function updateCategory() {
        const checked = document.querySelector('input[name="accountCategory"]:checked');
        if (!checked) return;
        const category = checked.value;
        const currentRole = roleSelect.value;
        roleSelect.innerHTML = '';

        if (category === 'staff') {
            const roles = ['Manager', 'Supervisor', 'Cook', 'Cashier', 'Server', 'Staff'];
            roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.textContent = r;
                roleSelect.appendChild(opt);
            });
            authFields.style.display = 'none';
            toggleAuthRequired(false);
        } else {
            const roles = ['Admin', 'Driver'];
            roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.textContent = r;
                roleSelect.appendChild(opt);
            });
            authFields.style.display = 'block';
            toggleAuthRequired(true);
        }

        if (currentRole && Array.from(roleSelect.options).some(opt => opt.value === currentRole)) {
            roleSelect.value = currentRole;
        }
    }

    function toggleAuthRequired(required) {
        ['username', 'email'].forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            if (required) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });

        // Password is only strictly required on ADD for auth accounts
        if (form.dataset.mode === 'add' && required) {
            passwordInput.setAttribute('required', 'required');
        } else {
            passwordInput.removeAttribute('required');
        }
    }

    accountCategoryRadios.forEach(r => r.addEventListener('change', updateCategory));

    // Image Preview
    if (staffPicture) {
        staffPicture.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => staffImgPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Password Strength
    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            const val = this.value;
            if (!val) {
                passwordStrength.innerText = '';
                return;
            }
            if (val.length < 8) {
                passwordStrength.innerText = 'Weak (min 8 chars)';
                passwordStrength.className = 'form-text text-danger small';
            } else if (/[A-Z]/.test(val) && /[0-9]/.test(val)) {
                passwordStrength.innerText = 'Strong';
                passwordStrength.className = 'form-text text-success small';
            } else {
                passwordStrength.innerText = 'Good';
                passwordStrength.className = 'form-text text-warning small';
            }
        });
    }

    // --- Save Logic ---
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const formData = new FormData(this);
            const mode = form.dataset.mode;
            const apiEndpoint = mode === 'edit' ? '../backend/api/admin/update_staff.php' : '../backend/api/admin/add_staff.php';

            saveBtn.disabled = true;
            saveBtn.innerText = 'Processing...';

            fetch(apiEndpoint, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message || 'Success!');
                        location.reload();
                    } else {
                        alert(result.message || 'Error processing request');
                        saveBtn.disabled = false;
                        saveBtn.innerText = mode === 'edit' ? 'Update Staff' : 'Save Staff';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An unexpected error occurred.');
                    saveBtn.disabled = false;
                    saveBtn.innerText = mode === 'edit' ? 'Update Staff' : 'Save Staff';
                });
        });
    }

    window.editStaff = function (id) {
        fetch(`../backend/api/admin/get_staff.php?id=${id}`)
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    const staff = result.data;
                    form.reset();
                    form.dataset.mode = 'edit';
                    modalTitle.innerText = 'Edit Staff Member';
                    saveBtn.innerText = 'Update Staff';

                    document.getElementById('staff_id').value = staff.staff_id;
                    document.getElementById('fullName').value = staff.full_name;
                    document.getElementById('shift').value = staff.shift;
                    document.getElementById('status').value = staff.status;

                    const isAuth = ['admin', 'driver'].includes(staff.role);
                    const categoryVal = isAuth ? 'admin_driver' : 'staff';
                    document.querySelector(`input[name="accountCategory"][value="${categoryVal}"]`).checked = true;

                    updateCategory();

                    // If it's a general position, set it
                    if (!isAuth) {
                        roleSelect.value = staff.position || staff.role;
                    } else {
                        roleSelect.value = staff.role.charAt(0).toUpperCase() + staff.role.slice(1);
                        document.getElementById('username').value = staff.admin_user || staff.driver_user || '';
                        document.getElementById('email').value = staff.admin_mail || staff.driver_mail || '';
                        passwordInput.removeAttribute('required'); // Password optional on edit
                    }

                    staffImgPreview.src = staff.photo_path && staff.photo_path !== 'default_user.png'
                        ? `${window.BASE_URL}/public/assets/staffs/${staff.photo_path}`
                        : `${window.BASE_URL}/public/assets/default_user.png`;

                    if (staffModal) staffModal.show();
                } else {
                    alert(result.message);
                }
            });
    };

    // --- Table & Pagination Logic ---
    const staffBody = document.getElementById('staff-body');
    const searchInput = document.getElementById('search-input');
    const toggleArchiveBtn = document.getElementById('toggle-archive');
    const pageInfo = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');

    let allStaffData = [...initialStaff];
    let archivedStaffData = [...initialArchived];
    let showingArchived = false;
    let filteredStaff = [];

    let currentPage = 1;
    const itemsPerPage = 10;

    function renderTable() {
        if (!staffBody) return;
        staffBody.innerHTML = '';

        const data = filteredStaff;
        const totalItems = data.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
        if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageItems = data.slice(start, end);

        if (totalItems === 0) {
            if (pageInfo) pageInfo.innerText = 'Showing 0 of 0 staff';
            staffBody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px;">No staff found.</td></tr>`;
            return;
        }

        if (pageInfo) pageInfo.innerText = `Showing ${start + 1} to ${Math.min(end, totalItems)} of ${totalItems} staff`;

        pageItems.forEach(staff => {
            const row = document.createElement('tr');

            const photo = staff.photo_path && staff.photo_path !== 'default_user.png'
                ? `${window.BASE_URL}/public/assets/staffs/${staff.photo_path}`
                : `${window.BASE_URL}/public/assets/default_user.png`;

            const roleDisplay = staff.role.charAt(0).toUpperCase() + staff.role.slice(1);
            const positionDisplay = staff.position || roleDisplay;

            const archiveIcon = staff.is_archived == 1 ? 'bi-arrow-counterclockwise' : 'bi-archive';
            const archiveTitle = staff.is_archived == 1 ? 'Restore' : 'Archive';

            row.innerHTML = `
                <td class="nameCol">
                    <div class="staffNameDiv d-flex align-items-center gap-2">
                        <img src="${photo}" alt="" class="staffPhoto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" onerror="this.onerror=null;this.src='${window.BASE_URL}/public/assets/default_user.png'">
                        <p class="mb-0">${staff.full_name}</p>
                    </div>
                </td>
                <td class="posCol">${positionDisplay}</td>
                <td class="shiftCol">${staff.shift}</td>
                <td class="statCol">
                    <span class="badge ${staff.status === 'Active' ? 'bg-success' : 'bg-secondary'}">${staff.status}</span>
                </td>
                <td class="actionCol">
                    <div class="action-header-buttons d-flex justify-content-center gap-2">
                        <button title="Edit" type="button" class="edit-btn" onclick="editStaff(${staff.staff_id})">
                            <i class="bi bi-pencil-square" style="font-size: 1.2rem; color: #d0b28c;"></i>
                        </button>
                        <button title="${archiveTitle}" type="button" class="archive-btn" onclick="toggleArchive(${staff.staff_id}, ${staff.is_archived})">
                            <i class="bi ${archiveIcon}" style="font-size: 1.2rem; color: #6c757d;"></i>
                        </button>
                    </div>
                </td>
            `;
            staffBody.appendChild(row);
        });
    }

    function filterStaff() {
        const searchTerm = (searchInput ? searchInput.value : '').toLowerCase();
        const sourceData = showingArchived ? archivedStaffData : allStaffData;

        filteredStaff = sourceData.filter(s => {
            return s.full_name.toLowerCase().includes(searchTerm) ||
                (s.position && s.position.toLowerCase().includes(searchTerm)) ||
                s.role.toLowerCase().includes(searchTerm);
        });

        currentPage = 1;
        renderTable();
    }

    if (searchInput) searchInput.addEventListener('input', filterStaff);

    if (toggleArchiveBtn) {
        toggleArchiveBtn.addEventListener('click', () => {
            showingArchived = !showingArchived;
            toggleArchiveBtn.innerText = showingArchived ? 'View Active' : 'View Archive';
            filterStaff();
        });
    }

    if (prevPageBtn) prevPageBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTable(); } });
    if (nextPageBtn) nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredStaff.length / itemsPerPage);
        if (currentPage < totalPages) { currentPage++; renderTable(); }
    });

    window.toggleArchive = function (id, currentStatus) {
        const newStatus = currentStatus == 1 ? 0 : 1;
        const confirmMsg = newStatus == 1 ? 'Archive this staff member?' : 'Restore this staff member?';
        if (!confirm(confirmMsg)) return;

        fetch('../backend/api/admin/archive_staff.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, is_archived: newStatus })
        }).then(res => res.json()).then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Error updating status');
            }
        });
    };

    // Initial Load
    filterStaff();
});