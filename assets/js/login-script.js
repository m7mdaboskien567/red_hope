// ==================== PASSWORD TOGGLE ====================
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

if (togglePassword) {
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = togglePassword.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
}

// ==================== FORM VALIDATION & AJAX LOGIN ====================
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');

if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Reset previous states
        emailInput.classList.remove('error', 'success');
        passwordInput.classList.remove('error', 'success');
        
        let isValid = true;
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value.trim())) {
            emailInput.classList.add('error');
            isValid = false;
        } else {
            emailInput.classList.add('success');
        }
        
        // Validate password (basic length check)
        if (passwordInput.value.length < 4) {
            passwordInput.classList.add('error');
            isValid = false;
        } else {
            passwordInput.classList.add('success');
        }
        
        if (isValid) {
            const submitBtn = loginForm.querySelector('.btn-login');
            const originalText = submitBtn.innerHTML;
            const rememberMe = document.getElementById('rememberMe').checked;
            
            // Loading state
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Signing in...';
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('email', emailInput.value);
            formData.append('password', passwordInput.value);

            try {
                const response = await fetch('apis/login.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    if (rememberMe) {
                        localStorage.setItem('rememberedEmail', emailInput.value);
                    } else {
                        localStorage.removeItem('rememberedEmail');
                    }
                    
                    // Simple success feedback before redirect
                    submitBtn.classList.remove('btn-login');
                    submitBtn.classList.add('btn-success');
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Success!';
                    
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
<<<<<<< HEAD
                    showAlert(data.message || 'Login failed. Please try again.', 'error');
=======
                    alert(data.message || 'Login failed. Please try again.');
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    passwordInput.classList.add('error');
                }
            } catch (error) {
                console.error('Error:', error);
<<<<<<< HEAD
                showAlert('A system error occurred. Please try again later.', 'error');
=======
                alert('A system error occurred. Please try again later.');
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
    });
}

// ==================== REMEMBER ME ====================
window.addEventListener('load', () => {
    const rememberedEmail = localStorage.getItem('rememberedEmail');
    if (rememberedEmail && emailInput) {
        emailInput.value = rememberedEmail;
        const rememberMeCheck = document.getElementById('rememberMe');
        if (rememberMeCheck) rememberMeCheck.checked = true;
    }
});

// ==================== SOCIAL LOGIN ====================
const socialButtons = document.querySelectorAll('.social-btn');
socialButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const platform = btn.classList.contains('google') ? 'Google' : 'Facebook';
<<<<<<< HEAD
        showAlert(`${platform} login is currently disabled for security. Please use your email.`, 'info');
=======
        alert(`${platform} login is currently disabled for security. Please use your email.`);
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
    });
});

// ==================== FORGOT PASSWORD ====================
const forgotPasswordLink = document.querySelector('.forgot-password');
if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener('click', (e) => {
        e.preventDefault();
<<<<<<< HEAD
        showAlert('Password recovery is currently unavailable. Please contact the administrator.', 'warning');
=======
        alert('Password recovery is currently unavailable. Please contact the administrator.');
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
    });
}
