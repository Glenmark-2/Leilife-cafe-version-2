<?php
require_once __DIR__ . '/../backend/helpers/SessionManager.php';
SessionManager::requireGuest();
?>
<div class="body">
    <div class="top-div">
        <h3>Ready to sign up to Leilife?</h3>
        <p>Tell us more about you so we can give you a better delivery experience.</p>
    </div>

    <div class="mid-div">
        <form id="signupForm">
            <p>User Details</p>
            <div class="col-md">
                <div class="form-floating">
                    <input type="text" class="form-control" id="fname" placeholder="First name" required>
                    <label for="fname">First name</label>
                </div>
                <div class="form-floating">
                    <input type="text" class="form-control" id="lname" placeholder="Last name" required>
                    <label for="lname">Last name</label>
                </div>
            </div>
            <br>
            <p>Login & Contact Details</p>
            <div class="col-md">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" placeholder="email" required>
                    <label for="email">Email</label>
                </div>
                <div class="form-floating">
                    <input type="tel" class="form-control" id="phone_number" placeholder="Phone number">
                    <label for="phone_number">Phone number</label>
                </div>
            </div>
            <br>
            <div class="col-md">
                <div class="form-floating">
                    <input type="password" class="form-control" id="signup_password" placeholder="Password" required>
                    <label for="signup_password">Password</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="signup_confirm_password" placeholder="Confirm password" required>
                    <label for="signup_confirm_password">Confirm password</label>
                </div>
            </div>

            <div class="terms">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">
                    By registering your details, you agree with our
                    <a href="#" id="openTerms">Terms & Conditions</a>.</label>
            </div>

            <div class="create">
                <button type="submit" class="btn-primary-custom">Create your Account</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Terms  Conditions</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Welcome to Leilife! Before creating your account, please read our terms:</p>
                <ul>
                    <li>You agree to provide accurate personal information.</li>
                    <li>Your account is personal and cannot be shared.</li>
                    <li>Orders are subject to our refund and cancellation policies.</li>
                    <li>We may update these terms from time to time.</li>
                </ul>
                <p>
                    By signing up, you acknowledge that you have read and agreed to these Terms & Conditions.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../components/verification_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/users/sign_up.js"></script>

