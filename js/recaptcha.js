/**
 * reCAPTCHA Enterprise Integration
 * Handles invisible/programmatic token generation for forms
 */
(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    // Get the site key from localized data
    const siteKey = window.sitkaRecaptcha?.siteKey;
    
    if (!siteKey) {
      console.error('Sitka reCAPTCHA: Site key not configured');
      return;
    }

    // Find all forms with spam mitigation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
      const mitigationDiv = form.querySelector('.sitka-spam-mitigation');
      if (!mitigationDiv) return;

      // Mark form as having recaptcha to avoid double-binding
      if (form.dataset.sitkaRecaptchaInit) return;
      form.dataset.sitkaRecaptchaInit = 'true';

      // Store original submit handler
      const originalSubmit = form.onsubmit;

      form.addEventListener('submit', function(e) {
        // Check if we already have a fresh token
        const tokenInput = form.querySelector('input[name="g-recaptcha-response"]');
        if (tokenInput && tokenInput.value && tokenInput.dataset.timestamp) {
          const tokenAge = Date.now() - parseInt(tokenInput.dataset.timestamp);
          // Token is valid for 2 minutes, use it if less than 1 minute old
          if (tokenAge < 60000) {
            return true; // Allow form submission
          }
        }

        // Prevent default submission to get token first
        e.preventDefault();

        // Show loading state if submit button exists
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        const originalButtonText = submitButton ? submitButton.textContent || submitButton.value : null;
        if (submitButton) {
          submitButton.disabled = true;
          if (submitButton.textContent) {
            submitButton.textContent = 'Verifying...';
          } else {
            submitButton.value = 'Verifying...';
          }
        }

        // Execute reCAPTCHA
        grecaptcha.enterprise.ready(function() {
          grecaptcha.enterprise.execute(siteKey, {action: 'submit'})
            .then(function(token) {
              // Add or update token in form
              let input = form.querySelector('input[name="g-recaptcha-response"]');
              if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'g-recaptcha-response';
                form.appendChild(input);
              }
              input.value = token;
              input.dataset.timestamp = Date.now().toString();

              // Restore button state
              if (submitButton && originalButtonText) {
                submitButton.disabled = false;
                if (submitButton.textContent) {
                  submitButton.textContent = originalButtonText;
                } else {
                  submitButton.value = originalButtonText;
                }
              }

              // Submit the form
              if (originalSubmit) {
                originalSubmit.call(form);
              }
              
              // Use native submit to bypass our event listener
              HTMLFormElement.prototype.submit.call(form);
            })
            .catch(function(error) {
              console.error('Sitka reCAPTCHA error:', error);
              
              // Restore button state
              if (submitButton && originalButtonText) {
                submitButton.disabled = false;
                if (submitButton.textContent) {
                  submitButton.textContent = originalButtonText;
                } else {
                  submitButton.value = originalButtonText;
                }
              }
              
              alert('reCAPTCHA verification failed. Please try again.');
            });
        });
      });
    });
  });
})();
