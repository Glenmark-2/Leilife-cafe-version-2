  const navBar = document.getElementById('navBar');
  const hamburgerButton = document.getElementById('hamburger');

  hamburgerButton.addEventListener('click', ()=> {
    if(navBar.style.display === 'flex'){
      navBar.style.display = 'none';
    } else {
      navBar.style.display = 'flex';
    }
  });