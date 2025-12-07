const closedEyeSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black">
      <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z"/>
      <circle cx="12" cy="12" r="2.5"/>
    </svg>`;

  const openEyeSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black">
      <!-- simplified path adapted from the large SVG you provided, scaled for 24x24 -->
      <path d="M1.2 1.2L22.8 22.8 21.4 24.2 0.6 3.4 1.2 1.2z" fill="none"/>
      <!-- fallback shape similar to "eye-off" feel, but visible -->
      <path d="M2 12c2.8-5.3 7.9-8 10-8s7.2 2.7 10 8c-2.8 5.3-7.9 8-10 8s-7.2-2.7-10-8zM12 16a4 4 0 100-8 4 4 0 000 8z"/>
    </svg>`;

  function togglePassword(id, iconSpan) {
    try {
      const input = document.getElementById(id);
      if (!input) {
        console.warn('togglePassword: input not found for id=', id);
        return;
      }

      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';

      // set the svg depending on state
      iconSpan.innerHTML = isHidden ? openEyeSVG : closedEyeSVG;
    } catch (err) {
      console.error('togglePassword error', err);
    }
  }

  const passwordInput = document.getElementById('new_password');
  const strengthText = document.getElementById('strengthText');

  passwordInput.addEventListener('input', function () {
    const val = passwordInput.value;
    let strength = '';

    const hasLetters = /[a-zA-Z]/.test(val);
    const hasNumbers = /\d/.test(val);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(val);

    if (val.length === 0) {
      strengthText.textContent = '';
      return;
    }

    if (val.length < 8) {
      strength = 'Weak';
      strengthText.style.color = 'red';
    } else if ((hasLetters && hasNumbers) || (hasLetters && hasSpecial) || (hasNumbers && hasSpecial) && val.length > 7) {
      strength = 'Medium';
      strengthText.style.color = 'orange';
    } else if (hasLetters && hasNumbers && hasSpecial  && val.length > 7) {
      strength = 'Strong';
      strengthText.style.color = 'green';
    } else {
      strength = 'Easy';
      strengthText.style.color = 'green';
    }
    strengthText.textContent = 'Strength: ' + strength;
  });
