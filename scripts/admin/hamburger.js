function toggleSidebar() {
  const navBar = document.getElementById('navBar');
  navBar.classList.toggle('open');
}

// Ensure button exists before attaching event (optional, if using onclick in HTML)
const hamburgerButton = document.getElementById('hamburger');
if (hamburgerButton) {
  hamburgerButton.addEventListener('click', toggleSidebar);
} else {
  // In case the button is dynamically added or onclick is used
  // console.log("Hamburger button not found via ID immediately");
}