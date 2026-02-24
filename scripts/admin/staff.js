document.addEventListener('DOMContentLoaded', function () {
    // --- Modal Initialization ---
    const addStaffModalEl = document.getElementById('addStaffModal');
    let staffModal;
    if (addStaffModalEl) {
        staffModal = new bootstrap.Modal(addStaffModalEl);
    }

    // OTP Modal Initialization
    const otpModalEl = document.getElementById('otpVerificationModal');
    let otpModal;
    if (otpModalEl) {
        otpModal = new bootstrap.Modal(otpModalEl);
    }
    const closeOtpModalBtn = document.getElementById('closeOtpModal');
    if (closeOtpModalBtn) {
        closeOtpModalBtn.addEventListener('click', () => otpModal?.hide());
    }
    
    // When OTP modal closes, always bring back the Staff modal
    if (otpModalEl) {
        otpModalEl.addEventListener('hidden.bs.modal', function () {
            if (staffModal) staffModal.show();
        });
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
    const emailInput = document.getElementById('email');

    // OTP Modal Elements
    const otpModalEmail = document.getElementById('otp-modal-email');
    const otpModalInput = document.getElementById('otp-modal-input');
    const verifyOtpModalBtn = document.getElementById('verifyOtpModalBtn');
    const resendOtpModalBtn = document.getElementById('resendOtpModalBtn');
    const otpError = document.getElementById('otp-error');
    const verificationStatus = document.getElementById('verification-status');

    let emailVerified = false;
    let currentVerificationEmail = '';

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
            roleSelect.disabled = false;
            
            // Show all fields for Add mode
            document.getElementById('account-type-section').style.display = 'block';
            document.getElementById('photo-section').style.display = 'block';
            document.getElementById('common-fields-divider').style.display = 'block';
            
            // Reset Auth fields for Add mode
            if (usernameInput) usernameInput.readOnly = false;
            if (emailInput) emailInput.readOnly = false;
            document.getElementById('password-section').style.display = 'block';

            updateCategory();
            
            // Reset verification state
            emailVerified = false;
            currentVerificationEmail = '';
            verificationStatus.style.display = 'none';
            if (emailInput) emailInput.removeAttribute('readonly');
            updateSaveButtonText();

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

        if (form.dataset.mode === 'add' && required) {
            passwordInput.setAttribute('required', 'required');
        } else {
            passwordInput.removeAttribute('required');
        }
        
        updateSaveButtonText();
    }

    function updateSaveButtonText() {
        if (!saveBtn) return;
        
        const isAdminDriver = document.querySelector('input[name="accountCategory"]:checked')?.value === 'admin_driver';
        const isAddMode = form.dataset.mode === 'add';
        
        if (!isAddMode || !isAdminDriver) {
            saveBtn.innerHTML = '<i class="bi bi-floppy"></i> Save Staff';
            return;
        }
        
        if (!emailVerified) {
            saveBtn.innerHTML = '<i class="bi bi-envelope-check"></i> Verify Email';
        } else {
            saveBtn.innerHTML = '<i class="bi bi-person-plus"></i> Add Staff';
        }
    }

    accountCategoryRadios.forEach(r => {
        r.addEventListener('change', () => {
            updateCategory();
            emailVerified = false;
            verificationStatus.style.display = 'none';
            if (emailInput) emailInput.removeAttribute('readonly');
            updateSaveButtonText();
        });
    });

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

    // --- DUPLICATION CHECKS ---
    const usernameInput = document.getElementById('username');

    async function checkAvailability(field, value) {
        if (!value) return true; // Let required validation handle empty
        try {
            const res = await fetch(`${window.BASE_URL}/backend/api/admin/check_availability.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ field, value })
            });
            const data = await res.json();
            return data;
        } catch (err) {
            console.error('Availability check failed', err);
            return { success: false, available: true }; // Assume available on error to avoid blocking logic, or handle stricter
        }
    }

    if (usernameInput) {
        usernameInput.addEventListener('blur', async function() {
            if (form.dataset.mode !== 'add') return; // Only validate on Add
            const val = this.value.trim();
            if (!val) return;
            
            // Remove existing feedback
            this.classList.remove('is-invalid', 'is-valid');
            let feedback = this.parentNode.querySelector('.invalid-feedback');
            if(feedback) feedback.remove();

            const result = await checkAvailability('username', val);
            
            if (result.success && !result.available) {
                this.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.innerText = result.message;
                this.parentNode.appendChild(div);
            } else if (result.success && result.available) {
                this.classList.add('is-valid');
            }
        });
    }

    // --- FORM SUBMIT LOGIC ---
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const isAdminDriver = document.querySelector('input[name="accountCategory"]:checked')?.value === 'admin_driver';
            const mode = form.dataset.mode;

            // Handle Verification Flow for Admin/Driver
            if (mode === 'add' && isAdminDriver && !emailVerified) {
                const email = emailInput.value.trim();
                if (!email) {
                    alert('Please enter a valid email address.');
                    emailInput.focus();
                    return;
                }

                // Check Email Availability First
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Checking Email...';
                
                const emailCheck = await checkAvailability('email', email);
                if (emailCheck.success && !emailCheck.available) {
                    alert(emailCheck.message);
                    saveBtn.disabled = false;
                    updateSaveButtonText();
                    return;
                }

                // Initial Send OTP Logic
                saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending Code...';

                try {
                    const res = await fetch(`${window.BASE_URL}/backend/api/admin/request_staff_verification.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    const data = await res.json();

                    if (data.success) {
                        currentVerificationEmail = email;
                        otpModalEmail.textContent = email;
                        otpModalInput.value = '';
                        otpError.style.display = 'none';
                        otpModalInput.classList.remove('is-invalid');
                        
                        // Swap Modals: Hide Staff Modal -> Show OTP Modal
                        if (staffModal) staffModal.hide();
                        if (otpModal) otpModal.show();
                        
                        // Focus on input when modal opens
                        setTimeout(() => otpModalInput.focus(), 500);

                    } else {
                        alert(data.message || 'Failed to send verification code.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error sending code. Please try again.');
                } finally {
                    saveBtn.disabled = false;
                    updateSaveButtonText();
                }
                return; // Stop form submission
            }

            // Check Username Availability before Final Submit (for Admin/Driver)
            if (isAdminDriver) {
                 const username = usernameInput.value.trim();
                 // If editing, we might want to skip check if username didn't change, 
                 // but checking anyway is safe as long as backend handles "except self". 
                 // But check_availability.php is simple. 
                 // For now, let's only block on 'add' mode or if we add "except current id" logic.
                 // To keep it simple, checking on ADD is most critical.
                 if (mode === 'add') {
                     const userCheck = await checkAvailability('username', username);
                     if (userCheck.success && !userCheck.available) {
                         alert(userCheck.message);
                         usernameInput.focus();
                         return;
                     }
                 }
            }

            // Normal Save/Update Logic
            const formData = new FormData(this);
            if (!formData.has('role')) {
                formData.append('role', roleSelect.value);
            }
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
                        updateSaveButtonText(); // Reset text
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An unexpected error occurred.');
                    saveBtn.disabled = false;
                    updateSaveButtonText();
                });
        });
    }

    // --- OTP MODAL LOGIC ---
    
    // Auto-focus logic handled in submit handler

    // Verify OTP Button (Inside Modal)
    if (verifyOtpModalBtn) {
        verifyOtpModalBtn.addEventListener('click', async function() {
            const otp = otpModalInput.value.trim();
            if (otp.length !== 6) {
                otpModalInput.classList.add('is-invalid');
                otpError.style.display = 'block';
                return;
            }

            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Verifying...';

            try {
                const res = await fetch(`${window.BASE_URL}/backend/api/admin/verify_staff_otp.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: currentVerificationEmail, otp })
                });
                const data = await res.json();

                if (data.success) {
                    // Success!
                    emailVerified = true;
                    if (otpModal) otpModal.hide();
                    
                    // Update Main Form UI
                    verificationStatus.style.display = 'block';
                    emailInput.setAttribute('readonly', 'readonly');
                    updateSaveButtonText();
                    
                    // Show success toast or alert?
                    // alert('Email verified successfully!');
                    
                } else {
                    otpModalInput.classList.add('is-invalid');
                    otpError.textContent = data.message || 'Invalid code';
                    otpError.style.display = 'block';
                    otpModalInput.value = '';
                }
            } catch (err) {
                console.error(err);
                alert('Verification error: ' + err.message);
            } finally {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    }

    // Resend OTP Button (Inside Modal)
    if (resendOtpModalBtn) {
        resendOtpModalBtn.addEventListener('click', async function() {
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

            try {
                const res = await fetch(`${window.BASE_URL}/backend/api/admin/request_staff_verification.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: currentVerificationEmail })
                });
                const data = await res.json();
                if(data.success) {
                    otpModalInput.value = '';
                    otpModalInput.classList.remove('is-invalid');
                    otpError.style.display = 'none';
                    alert('New code sent!');
                } else {
                    alert(data.message);
                }
            } catch (err) {
                alert('Failed to resend code.');
            } finally {
                // Cooldown
                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                }, 10000); 
            }
        });
    }

    // Edit Staff Logic
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
                    const categoryRadio = document.querySelector(`input[name="accountCategory"][value="${categoryVal}"]`);
                    if (categoryRadio) categoryRadio.checked = true;

                    updateCategory();

                    // Hide restricted fields for Edit mode
                    document.getElementById('account-type-section').style.display = 'none';
                    authFields.style.display = 'none'; 

                    // Ensure photo section and divider are visible for editing
                    document.getElementById('photo-section').style.display = 'block';
                    document.getElementById('common-fields-divider').style.display = 'block';


                    if (!isAuth) {
                        roleSelect.value = staff.position || staff.role;
                        roleSelect.disabled = false;
                        authFields.style.display = 'none';
                    } else {
                        roleSelect.value = staff.role.charAt(0).toUpperCase() + staff.role.slice(1);
                        roleSelect.disabled = true; 
                        
                        // Show Auth fields but make them read-only
                        authFields.style.display = 'block';
                        if (usernameInput) {
                            usernameInput.value = staff.admin_user || staff.driver_user || '';
                            usernameInput.readOnly = true;
                        }
                        if (emailInput) {
                            emailInput.value = staff.admin_mail || staff.driver_mail || '';
                            emailInput.readOnly = true;
                        }
                        
                        // Hide password in edit mode to keep it simple
                        document.getElementById('password-section').style.display = 'none';

                        passwordInput.removeAttribute('required'); 
                    }

                    // Re-enable for FormData capture (special case for hidden/readonly inputs)
                    // If they are readonly, they are still captured by FormData. 
                    // If they are disabled, they are NOT. 
                    // Username/Email should NOT be disabled, just readonly.

                    staffImgPreview.src = staff.photo_path && staff.photo_path !== 'default_user.png'
                        ? `${window.BASE_URL}/public/assets/staffs/${staff.photo_path}`
                        : `${window.BASE_URL}/public/assets/default_user.png`;

                    // Reset verification UI for edit mode
                    emailVerified = false; 
                    verificationStatus.style.display = 'none';
                    if (emailInput) emailInput.removeAttribute('readonly');
                    
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

    filterStaff();
});