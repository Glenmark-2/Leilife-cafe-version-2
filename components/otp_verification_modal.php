<!-- OTP Verification Modal -->
<div class="modal fade" id="otpVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="otpVerificationModalLabel">
                    <i class="bi bi-shield-check text-primary"></i> Email Verification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeOtpModal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-envelope-check" style="font-size: 3rem; color: #d4a373;"></i>
                    </div>
                    <p class="mb-2">We've sent a verification code to:</p>
                    <p class="fw-bold text-primary mb-1" id="otp-modal-email" style="font-size: 1.1rem;"></p>
                    <small class="text-muted">Code expires in 5 minutes</small>
                </div>

                <div class="mb-4">
                    <label for="otp-modal-input" class="form-label fw-bold text-center d-block">Enter 6-Digit Code</label>
                    <input
                        type="text"
                        class="form-control form-control-lg text-center"
                        id="otp-modal-input"
                        maxlength="6"
                        placeholder="000000"
                        style="letter-spacing: 12px; font-size: 24px; font-weight: bold; border: 2px solid #d4a373; border-radius: 10px;"
                        autocomplete="off">
                    <div class="invalid-feedback text-center" id="otp-error">
                        Invalid code. Please try again.
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary-custom btn-lg" id="verifyOtpModalBtn">
                        <i class="bi bi-check-circle"></i> Verify Code
                    </button>
                    <button type="button" class="btn btn-link text-decoration-none" id="resendOtpModalBtn">
                        <i class="bi bi-arrow-clockwise"></i> Resend Code
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>