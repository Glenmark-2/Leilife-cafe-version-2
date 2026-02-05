document.addEventListener('DOMContentLoaded', () => {
    const formRequest = document.getElementById('form-request-otp');
    const formVerify = document.getElementById('form-verify-otp');
    const formReset = document.getElementById('form-reset-password');

    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const step3 = document.getElementById('step-3');

    const dot1 = document.getElementById('dot-1');
    const dot2 = document.getElementById('dot-2');
    const dot3 = document.getElementById('dot-3');

    const emailInput = document.getElementById('email');
    const displayEmail = document.getElementById('display-email');
    const otpInput = document.getElementById('otp');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    const resendBtn = document.getElementById('resend-otp');

    let currentEmail = '';
    let currentOTP = '';

    // Step 1: Request OTP
    formRequest.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = emailInput.value;
        const btn = document.getElementById('btn-send-otp');
        
        btn.disabled = true;
        btn.textContent = 'Sending...';

        try {
            const res = await fetch(`${window.BASE_URL}/backend/api/forgot_password_request.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });
            
            if (!res.ok) {
                const text = await res.text();
                throw new Error(`HTTP ${res.status}: ${text.substring(0, 100)}`);
            }

            const data = await res.json();

            if (data.success) {
                currentEmail = email;
                displayEmail.textContent = email;
                
                // Switch UI
                step1.style.display = 'none';
                step2.style.display = 'block';
                dot1.classList.remove('active');
                dot2.classList.add('active');
            } else {
                alert(data.message || 'Failed to send OTP.');
            }
        } catch (err) {
            console.error(err);
            alert('An error occurred: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Send Verification Code';
        }
    });

    // Step 2: Verify OTP
    formVerify.addEventListener('submit', async (e) => {
        e.preventDefault();
        const otp = otpInput.value;
        const btn = document.getElementById('btn-verify-otp');

        btn.disabled = true;
        btn.textContent = 'Verifying...';

        try {
            const res = await fetch(`${window.BASE_URL}/backend/api/forgot_password_verify.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: currentEmail, otp })
            });

            if (!res.ok) {
                const text = await res.text();
                throw new Error(`HTTP ${res.status}: ${text.substring(0, 100)}`);
            }

            const data = await res.json();

            if (data.success) {
                currentOTP = otp;
                
                // Switch UI
                step2.style.display = 'none';
                step3.style.display = 'block';
                dot2.classList.remove('active');
                dot3.classList.add('active');
            } else {
                alert(data.message || 'Invalid OTP.');
            }
        } catch (err) {
            console.error(err);
            alert('An error occurred: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Verify Code';
        }
    });

    // Step 3: Reset Password
    formReset.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const btn = document.getElementById('btn-reset-password');

        if (newPassword !== confirmPassword) {
            alert('Passwords do not match.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Resetting...';

        try {
            const res = await fetch(`${window.BASE_URL}/backend/api/forgot_password_reset.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    email: currentEmail, 
                    otp: currentOTP, 
                    new_password: newPassword 
                })
            });

            if (!res.ok) {
                const text = await res.text();
                throw new Error(`HTTP ${res.status}: ${text.substring(0, 100)}`);
            }

            const data = await res.json();

            if (data.success) {
                alert('Password reset successful! You will be redirected to the home page.');
                window.location.href = 'index.php?page=home';
            } else {
                alert(data.message || 'Reset failed.');
            }
        } catch (err) {
            console.error(err);
            alert('An error occurred: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Reset Password';
        }
    });

    // Resend OTP
    resendBtn.addEventListener('click', async () => {
        resendBtn.style.pointerEvents = 'none';
        resendBtn.style.opacity = '0.5';
        
        try {
            const res = await fetch(`${window.BASE_URL}/backend/api/forgot_password_request.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: currentEmail })
            });
            const data = await res.json();
            alert(data.message);
        } catch (err) {
            alert('Resend failed: ' + err.message);
        } finally {
            setTimeout(() => {
                resendBtn.style.pointerEvents = 'auto';
                resendBtn.style.opacity = '1';
            }, 30000); // 30s throttle
        }
    });
});
