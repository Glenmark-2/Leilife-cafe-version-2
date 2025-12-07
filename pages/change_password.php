<div id="body">
    <h3>Enter your Email</h3>
    <form onsubmit="event.preventDefault(); console.log('Email submitted');">
        <div class="mb-3 box">
            <label for="email" class="form-label">Email Address <span style="color: red;">*</span></label>
            <div class="input-wrapper">
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="inputs"
                    placeholder="Enter your email"
                    required>
            </div>
        </div>
        <button type="submit" class="btn-primary-custom center-btn">Submit</button>
    </form>


    <h3>Change your Password</h3>
    <form>
        <div class="mb-3 box">
            <label for="new_password" class="form-label">New password<span style="color: red;">*</span></label>
            <div class="input-wrapper">
                <input type="password" id="new_password" name="new_password" class="inputs" placeholder="Enter new password" required>
                <span class="toggle-eye" role="button" aria-label="Toggle password visibility"
                    onclick="togglePassword('new_password', this)" title="Show / hide password">
                    <!-- initial closed-eye SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black">
                        <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z" />
                        <circle cx="12" cy="12" r="2.5" />
                    </svg>
                </span>
            </div>
            <p id="strengthText"></p>
        </div>

        <div class="mb-3 box">
            <label for="confirm_password" class="form-label">Confirm password<span style="color: red;">*</span></label>
            <div class="input-wrapper">
                <input type="password" id="confirm_password" name="confirm_password" class="inputs" placeholder="Re-enter new password" required>
                <span class="toggle-eye" role="button" aria-label="Toggle password visibility"
                    onclick="togglePassword('confirm_password', this)" title="Show / hide password">
                    <!-- initial closed-eye SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black">
                        <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z" />
                        <circle cx="12" cy="12" r="2.5" />
                    </svg>
                </span>
            </div>
        </div>
        <button type="submit" class="btn-primary-custom center-btn">Change password</button>
    </form>
</div>

<script src="../scripts/users/change_password.js"></script>