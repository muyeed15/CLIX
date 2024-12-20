document.addEventListener('DOMContentLoaded', function() {
  const profileForm = document.querySelector('form');
  const profilePictureInput = document.getElementById('profilePicture');
  const profilePreview = document.getElementById('profilePreview');
  const newPasswordInput = document.getElementById('floatingNewPassword');
  const confirmPasswordInput = document.getElementById('floatingConfirmNewPassword');
  const phoneInput = document.getElementById('floatingPhone');
  const dobInput = document.getElementById('floatingDOB');
  const nidInput = document.getElementById('floatingNID');

  if (profilePictureInput) {
    profilePictureInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          alert('File size must be less than 5MB');
          this.value = '';
          return;
        }

        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
          alert('Only JPG, PNG, and GIF files are allowed');
          this.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          profilePreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  if (newPasswordInput) {
    const requirementsDiv = document.createElement('div');
    requirementsDiv.className = 'password-requirements';
    newPasswordInput.parentNode.insertBefore(requirementsDiv, newPasswordInput.nextSibling);

    function validatePassword(password) {
      const minLength = password.length >= 8;
      const hasUpper = /[A-Z]/.test(password);
      const hasLower = /[a-z]/.test(password);
      const hasNumber = /[0-9]/.test(password);

      return minLength && hasUpper && hasLower && hasNumber;
    }

    newPasswordInput.addEventListener('input', function() {
      const isValid = validatePassword(this.value);
      this.setCustomValidity(isValid ? '' : 'Password does not meet requirements');
      requirementsDiv.style.color = isValid ? '#28a745' : '#6c757d';
    });

    if (confirmPasswordInput) {
      function validatePasswordMatch() {
        if (confirmPasswordInput.value !== newPasswordInput.value) {
          confirmPasswordInput.setCustomValidity('Passwords do not match');
        } else {
          confirmPasswordInput.setCustomValidity('');
        }
      }

      newPasswordInput.addEventListener('change', validatePasswordMatch);
      confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    }
  }

  if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
      let value = this.value.replace(/\D/g, '');

      if (value.length > 0) {
        if (value.length <= 11) { // For BD numbers
          if (value.length > 6) {
            value = value.replace(/(\d{5})(\d{1,6})/, '$1-$2');
          } else if (value.length > 3) {
            value = value.replace(/(\d{3})(\d{1,3})/, '$1-$2');
          }
        }
      }

      this.value = value;

      const isValid = value.length >= 10 && value.length <= 11;
      this.setCustomValidity(isValid ? '' : 'Please enter a valid phone number');
    });
  }

  if (dobInput) {
    dobInput.addEventListener('change', function() {
      const birthDate = new Date(this.value);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();

      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }

      if (age < 18) {
        this.setCustomValidity('You must be at least 18 years old');
      } else {
        this.setCustomValidity('');
      }
    });
  }

  if (nidInput) {
    nidInput.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');

      const isValid = this.value.length === 0 ||
        this.value.length === 10 ||
        this.value.length === 13 ||
        this.value.length === 17;

      this.setCustomValidity(isValid ? '' : 'Please enter a valid NID number');
    });
  }

  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(event) {
      if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      this.classList.add('was-validated');
    });
  });

  // Animation
  document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('focused');
    });

    input.addEventListener('blur', function() {
      if (!this.value) {
        this.parentElement.classList.remove('focused');
      }
    });

    if (input.value) {
      input.parentElement.classList.add('focused');
    }
  });

  const successAlerts = document.querySelectorAll('.alert-success');
  successAlerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease-out';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 3000);
  });
});
