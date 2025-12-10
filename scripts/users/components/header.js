document.addEventListener("DOMContentLoaded", function () {
  // --- Login Modal ---
  const loginModalEl = document.getElementById('loginModal');
  if (loginModalEl) {
    const loginModal = new bootstrap.Modal(loginModalEl);
    const webLogin = document.getElementById('webLogin');
    const mobileLogin = document.getElementById('mobileLogin');

    let isGoogleScriptLoaded = false;
    const loadGoogleScript = () => {
      if (!isGoogleScriptLoaded) {
        const script = document.createElement('script');
        script.src = 'https://accounts.google.com/gsi/client';
        script.async = true;
        script.defer = true;
        document.body.appendChild(script);
        isGoogleScriptLoaded = true;
      }
    };

    if (webLogin) webLogin.addEventListener('click', e => {
      e.preventDefault();
      loadGoogleScript();
      loginModal.show();
    });
    if (mobileLogin) mobileLogin.addEventListener('click', e => {
      e.preventDefault();
      loadGoogleScript();
      loginModal.show();
    });
  }

  // --- Cart Modal ---
  const cartBtn = document.getElementById("cartBtn");
  const mobileCartBtn = document.getElementById("mobileCartBtn");
  const cartModal = document.getElementById("cart-container");

  function toggleCart(e) {
    e.preventDefault();
    cartModal.classList.toggle("d-none");
    cartModal.classList.toggle("d-flex");
  }

  if (cartBtn) cartBtn.addEventListener("click", toggleCart);
  if (mobileCartBtn) mobileCartBtn.addEventListener("click", toggleCart);

  document.addEventListener("click", function (e) {
    if (cartModal &&
      !cartModal.contains(e.target) &&
      cartBtn && !cartBtn.contains(e.target) &&
      mobileCartBtn && !mobileCartBtn.contains(e.target)) {
      cartModal.classList.add("d-none");
      cartModal.classList.remove("d-flex");
    }
  });

  // --- Mobile Dropdown ---
  const dropdown = document.getElementById('navbarDropdownContent');
  const toggleButton = document.querySelector('.navbar-toggler');

  if (dropdown && toggleButton) {
    // Toggle dropdown
    toggleButton.addEventListener('click', function (e) {
      e.stopPropagation(); // prevent triggering document click
      dropdown.classList.toggle('show');
      toggleButton.setAttribute('aria-expanded', dropdown.classList.contains('show'));
    });

    // Close dropdown when clicking a link
    dropdown.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', function () {
        dropdown.classList.remove('show');
        toggleButton.setAttribute('aria-expanded', 'false');
      });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
      if (!dropdown.contains(e.target) && !toggleButton.contains(e.target)) {
        dropdown.classList.remove('show');
        toggleButton.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // --- Login Form Submission ---
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const emailInput = document.getElementById('login_email');
      const passwordInput = document.getElementById('login_password');

      if (!emailInput || !passwordInput) return;

      const email = emailInput.value.trim();
      const password = passwordInput.value;

      if (!email || !password) {
        alert("Please enter both email and password.");
        return;
      }

      const data = {
        email: email,
        password: password
      };

      fetch('../backend/api/login_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            // Redirect based on role
            const role = result.user.role;
            let redirectUrl = 'index.php?page=home';

            if (role === 'admin') {
              redirectUrl = 'admin.php?page=dashboard';
            } else if (role === 'driver') {
              redirectUrl = 'driver.php?page=available';
            }

            window.location.replace(redirectUrl);
          } else {
            alert(result.message || "Login failed");
          }
        })
        .catch(error => {
          console.error("Login Error:", error);
          alert("An error occurred during login.");
        });
    });
  }
});
