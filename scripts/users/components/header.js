document.addEventListener("DOMContentLoaded", function () {
  // --- Login Modal ---
  const loginModalEl = document.getElementById('loginModal');
  if (loginModalEl) {
    const loginModal = new bootstrap.Modal(loginModalEl);
    const webLogin = document.getElementById('webLogin');
    const mobileLogin = document.getElementById('mobileLogin');

    if (webLogin) webLogin.addEventListener('click', e => { e.preventDefault(); loginModal.show(); });
    if (mobileLogin) mobileLogin.addEventListener('click', e => { e.preventDefault(); loginModal.show(); });
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
});
