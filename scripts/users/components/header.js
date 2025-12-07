      const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
      document.getElementById('webLogin').addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.show();
      });

      document.getElementById('mobileLogin').addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.show();
      });