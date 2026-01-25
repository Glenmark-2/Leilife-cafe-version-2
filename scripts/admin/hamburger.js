function toggleSidebar() {
  const navBar = document.getElementById('navBar');
  const overlay = document.getElementById('sidebarOverlay');

  if (navBar) {
    navBar.classList.toggle('open');
  }
  if (overlay) {
    overlay.classList.toggle('show');
  }
}

