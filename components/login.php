      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="close">
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="logo">
            <img src="../public/assets/leilife.png" alt="Logo">
          </div>

          <h5>Welcome back!</h3>

            <form>

              <div class="mb-3">
                <label for="login" class="form-label">Email or Username <span style="color: red;">*</span></label>
                <input type="text" id="login" name="login" class="inputs" placeholder="Enter your email or username" required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password <span style="color: red;">*</span></label>
                <input type="password" id="password" name="password" class="inputs" placeholder="Enter your password" required>

              </div>

              <button type="submit" class="btn-primary-custom center-btn">Login</button>
              <button type="button" class="btn-primary-custom center-btn googleBtn">
                <img id="googleLogo" src="../public/assets/google.logo.webp" alt="google logo">
                Continue with google</button>
            </form>
            <p id="forgotPass"><a>forgot your password?</a></p>
            <div id="terms">
              <p>By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
            </div>
            <div id="signup">
              <p>Don't have an account? <a href="#">Sign up</a></p>
            </div>


        </div>

      </div>