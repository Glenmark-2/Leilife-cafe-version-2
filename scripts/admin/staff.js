    const staffModal = new bootstrap.Modal(document.getElementById('addStaffModal'));
    document.getElementById('add-staff').addEventListener('click', function(e) {
        e.preventDefault();
        staffModal.show();
    });

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