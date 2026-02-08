<?php
// pages/forgot_password.php
require_once __DIR__ . '/../backend/helpers/UrlHelper.php';
?>
<link rel="stylesheet" href="../css/users/sign_up.css">
<style>
    .body {
        min-height: 80vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 40px 20px;
    }

    .mid-div {
        max-width: 450px;
        width: 100%;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .inputs {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }

    .inputs:focus {
        border-color: #d4a373;
        box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.2);
        outline: none;
    }

    .btn-primary-custom {
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: transform 0.2s ease;
    }

    .btn-primary-custom:active {
        transform: scale(0.98);
    }

    #otp-display {
        letter-spacing: 5px;
        font-size: 24px;
        font-weight: bold;
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
    }

    .step-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #ddd;
    }

    .step-dot.active {
        background: #d4a373;
        width: 25px;
        border-radius: 5px;
    }
</style>

<div class="body">
    <div class="mid-div">
        <div class="step-indicator">
            <div class="step-dot active" id="dot-1"></div>
            <div class="step-dot" id="dot-2"></div>
            <div class="step-dot" id="dot-3"></div>
        </div>

        <!-- Step 1: Email Request -->
        <div id="step-1">
            <h3 class="text-center mb-2">Forgot Password?</h3>
            <p class="text-center text-muted mb-4 small">No worries! Enter your email below to get an OTP.</p>
            <form id="form-request-otp">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" id="email" class="inputs" placeholder="Enter your registered email" required>
                </div>
                <button type="submit" class="btn-primary-custom w-100" id="btn-send-otp">Send Verification Code</button>
            </form>
        </div>

        <!-- Step 2: OTP Verification -->
        <div id="step-2" style="display: none;">
            <h3 class="text-center mb-2">Verify OTP</h3>
            <p class="text-center text-muted mb-4 small">We sent a 6-digit code to <br><b id="display-email"></b></p>
            <form id="form-verify-otp">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Verification Code</label>
                    <input type="text" id="otp" class="inputs text-center" placeholder="000 000" maxlength="6" required style="letter-spacing: 8px; font-size: 20px;">
                </div>
                <button type="submit" class="btn-primary-custom w-100" id="btn-verify-otp">Verify Code</button>
                <p class="text-center mt-3 small">
                    Didn't get code? <a href="javascript:void(0)" id="resend-otp" class="text-decoration-none fw-bold" style="color: #d4a373;">Resend</a>
                </p>
            </form>
        </div>

        <!-- Step 3: Password Reset -->
        <div id="step-3" style="display: none;">
            <h3 class="text-center mb-2">New Password</h3>
            <p class="text-center text-muted mb-4 small">Set a strong password to protect your account.</p>
            <form id="form-reset-password">
                <div class="mb-3">
                    <label class="form-label small fw-bold">New Password</label>
                    <input type="password" id="new_password" class="inputs" placeholder="Minimum 8 characters" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Confirm New Password</label>
                    <input type="password" id="confirm_password" class="inputs" placeholder="Repeat your new password" required>
                </div>
                <button type="submit" class="btn-primary-custom w-100" id="btn-reset-password">Reset Password</button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="<?= UrlHelper::getBaseUrl() ?>/public/index.php?page=home" class="text-decoration-none small text-muted">← Back to Login</a>
        </div>
    </div>
</div>

<script src="../scripts/users/forgot_password.js"></script>