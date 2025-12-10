document.addEventListener('DOMContentLoaded', function () {
        console.log('Sign up script loaded.');

        const modalElement = document.getElementById('staticBackdrop');
        let modal;
        if (modalElement) {
                if (typeof bootstrap !== 'undefined') {
                        modal = new bootstrap.Modal(modalElement);
                } else {
                        console.error('Bootstrap is not defined. Ensure bootstrap.bundle.min.js is loaded.');
                }
        } else {
                console.error('Modal element with ID "staticBackdrop" not found.');
        }

        const openTermsBtn = document.getElementById('openTerms');
        if (openTermsBtn) {
                openTermsBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (modal) modal.show();
                });
        }

        const signupForm = document.getElementById('signupForm');
        if (signupForm) {
                signupForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        console.log('Form submitted');

                        const firstNameInput = document.getElementById('fname');
                        const lastNameInput = document.getElementById('lname');
                        const emailInput = document.getElementById('email');
                        const phoneNumberInput = document.getElementById('phone_number');
                        const passwordInput = document.getElementById('signup_password');
                        const confirmPasswordInput = document.getElementById('signup_confirm_password');
                        const termsInput = document.getElementById('terms');

                        if (!firstNameInput || !lastNameInput || !emailInput || !passwordInput || !confirmPasswordInput || !termsInput) {
                                console.error('One or more input fields are missing in the DOM.');
                                alert('Internal Error: Missing form fields.');
                                return;
                        }

                        const firstName = firstNameInput.value.trim();
                        const lastName = lastNameInput.value.trim();
                        const email = emailInput.value.trim();
                        const phoneNumber = phoneNumberInput.value.trim();
                        const password = passwordInput.value;
                        const confirmPassword = confirmPasswordInput.value;
                        const terms = termsInput.checked;

                        if (password !== confirmPassword) {
                                console.log('Password check failed.');
                                console.log('Password:', password, 'Length:', password.length);
                                console.log('Confirm Password:', confirmPassword, 'Length:', confirmPassword.length);
                                alert('Passwords do not match.');
                                return;
                        }

                        const data = {
                                first_name: firstName,
                                last_name: lastName,
                                email: email,
                                phone_number: phoneNumber,
                                password: password,
                                confirm_password: confirmPassword
                        };

                        console.log('Sending data:', data);

                        fetch('../backend/api/register_user.php', {
                                method: 'POST',
                                headers: {
                                        'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(data)
                        })
                                .then(response => response.json())
                                .then(result => {
                                        console.log('JSON result:', result);
                                        console.log('JSON result:', result);
                                        if (result.success) {
                                                if (result.verification_required) {
                                                        const verificationModalElement = document.getElementById('verificationModal');
                                                        if (verificationModalElement && typeof bootstrap !== 'undefined') {
                                                                const verificationModal = new bootstrap.Modal(verificationModalElement);
                                                                verificationModal.show();

                                                                // Redirect after short delay
                                                                setTimeout(() => {
                                                                        window.location.replace('../public/index.php?page=home');
                                                                }, 5000);
                                                        } else {
                                                                alert('Registration Successful! Please check your email.');
                                                                window.location.replace('../public/index.php?page=home');
                                                        }
                                                } else {
                                                        alert('Registration Successful!');
                                                        window.location.replace('../public/index.php?page=home');
                                                }
                                        } else {
                                                alert('Error: ' + result.message);
                                        }
                                })
                                .catch(error => {
                                        console.error('Fetch Error:', error);
                                        alert('An unexpected error occurred. Please check the console.');
                                });
                });
        } else {
                console.error('Signup Form with ID "signupForm" not found.');
        }
});