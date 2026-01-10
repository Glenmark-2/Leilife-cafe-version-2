      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="close">
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="logo">
            <img src="<?= UrlHelper::getBaseUrl() ?>/public/assets/leilife.png" alt="Logo">
          </div>

          <h5>Welcome back!</h3>

            <form id="loginForm">

              <div class="mb-3">
                <label for="login" class="form-label">Email or Username <span style="color: red;">*</span></label>
                <input type="text" id="login_email" name="login" class="inputs" placeholder="Enter your email or username" required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password <span style="color: red;">*</span></label>
                <input type="password" id="login_password" name="password" class="inputs" placeholder="Enter your password" required>

              </div>

              <button type="submit" class="btn-primary-custom center-btn">Login</button>
              
              <div class="text-center my-3 text-muted">OR</div>
              
              <div id="g_id_onload"
                   data-client_id="<?php require_once __DIR__ . '/../backend/helpers/EnvLoader.php'; EnvLoader::load(__DIR__ . '/../.env'); echo getenv('GOOGLE_CLIENT_ID'); ?>"
                   data-context="signin"
                   data-ux_mode="popup"
                   data-callback="handleGoogleCredentialResponse"
                   data-auto_prompt="false">
              </div>

              <div class="d-flex justify-content-center w-100">
                  <div class="g_id_signin"
                       data-type="standard"
                       data-shape="rectangular"
                       data-theme="outline"
                       data-text="signin_with"
                       data-size="large"
                       data-logo_alignment="left">
                  </div>
              </div>
            </form>

            <!-- Script removed from here, will be lazy loaded -->
            <script>
                function handleGoogleCredentialResponse(response) {
                    console.log("Google Token:", response.credential);
                    
                    fetch(`${window.BASE_URL}/backend/api/google_login.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: response.credential })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                             const role = data.user.role;
                             let redirectUrl = 'index.php?page=home';
                             
                             if (role === 'admin') {
                                 redirectUrl = 'admin.php?page=dashboard';
                             } else if (role === 'driver') {
                                 redirectUrl = 'driver.php?page=available';
                             }
                             
                             window.location.replace(redirectUrl);
                        } else {
                             alert("Google Login Failed: " + data.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("An error occurred.");
                    });
                }
            </script>
            <p id="forgotPass"><a>forgot your password?</a></p>
            <div id="terms">
              <p>By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
            </div>
            <div id="signup">
              <p>Don't have an account? <a href="<?= UrlHelper::getBaseUrl() ?>/public/index.php?page=sign_up">Sign up</a></p>
            </div>


        </div>

      </div>