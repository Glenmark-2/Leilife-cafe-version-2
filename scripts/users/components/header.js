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

    // Check for login=true in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('login') === 'true') {
      loadGoogleScript();
      loginModal.show();
      // Clean URL
      const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=home";
      window.history.replaceState({ path: newUrl }, '', newUrl);
    }
  }

  // --- Cart Modal ---
  const cartBtn = document.getElementById("cartBtn");
  const mobileCartBtn = document.getElementById("mobileCartBtn");
  const cartModal = document.getElementById("cart-container");

  // Sticky Bag Button handled by ID
  const stickyBagBtn = document.getElementById("sticky-bag-btn");

  function toggleCart(e) {
    if (e) e.preventDefault(); // Check if e exists
    cartModal.classList.toggle("d-none");
    cartModal.classList.toggle("d-flex");
    document.body.classList.toggle("cart-open");
  }

  // --- Swipe Down to Close (Mobile Cart) ---
  if (cartModal) {
    let touchStartY = 0;
    let touchEndY = 0;
    const swipeThreshold = 50; // Minimum distance to be considered a swipe

    cartModal.addEventListener('touchstart', e => {
      // Only track if we are at the top of the container (scrolled to top)
      // or if the touch is on the header part (pickup-box etc)
      // Otherwise we might interfere with scrolling inside the list.
      // Easiest is to check if scrollTop is 0
      if (cartModal.scrollTop <= 0) {
        touchStartY = e.changedTouches[0].screenY;
      } else {
        touchStartY = -1; // Ignore this swipe attempt as we are scrolled down
      }
    }, { passive: true });

    cartModal.addEventListener('touchmove', e => {
      // Prevent default only if we are swiping down and at top?
      // Be careful not to block normal scrolling.
      // For simplicity, just tracking end position here.
    }, { passive: true });

    cartModal.addEventListener('touchend', e => {
      if (touchStartY === -1) return;

      touchEndY = e.changedTouches[0].screenY;
      const distance = touchEndY - touchStartY;

      if (distance > swipeThreshold) {
        // Swiped down
        // Close Cart
        cartModal.classList.add("d-none");
        cartModal.classList.remove("d-flex");
        document.body.classList.remove("cart-open");
      }
    }, { passive: true });
  }

  // --- Initialize Listeners ---
  if (cartBtn) cartBtn.addEventListener("click", toggleCart);
  if (mobileCartBtn) mobileCartBtn.addEventListener("click", toggleCart);
  if (stickyBagBtn) stickyBagBtn.addEventListener("click", toggleCart);

  document.addEventListener("click", function (e) {
    // If the clicked element was removed from DOM (e.g. cart item removed), ignore
    if (!e.target.isConnected) return;

    if (cartModal &&
      !cartModal.classList.contains('d-none') && // Only check if open
      !cartModal.contains(e.target) &&
      cartBtn && !cartBtn.contains(e.target) &&
      mobileCartBtn && !mobileCartBtn.contains(e.target) &&
      stickyBagBtn && !stickyBagBtn.contains(e.target)) {
      cartModal.classList.add("d-none");
      cartModal.classList.remove("d-flex");
      document.body.classList.remove("cart-open");
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
