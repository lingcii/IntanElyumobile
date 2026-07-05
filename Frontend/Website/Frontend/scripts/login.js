document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const successModal = document.getElementById('successModal');
    const errorModal = document.getElementById('errorModal');
    const errorModalText = document.getElementById('errorModalText');
    const closeErrorModal = document.getElementById('closeErrorModal');

    if (closeErrorModal && errorModal) {
        closeErrorModal.addEventListener('click', () => {
            errorModal.classList.remove('active');
        });
        errorModal.addEventListener('click', (e) => {
            if (e.target === errorModal) {
                errorModal.classList.remove('active');
            }
        });
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = loginForm.querySelector('.btn-login');

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            showError('Please fill in all fields.');
            return;
        }

        emailInput.disabled = true;
        passwordInput.disabled = true;
        submitBtn.disabled = true;
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Signing In...';

        try {
            const params = new URLSearchParams();
            params.append('email', email);
            params.append('password', password);

            const response = await fetch(window.API_CONFIG.AUTH + '/login', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                credentials: 'include',
                body: params
            }).then(async res => {
                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.error || errData.message || `HTTP ${res.status}`);
                }
                return res.json();
            });

            if (response.success && response.user) {
                if (errorMessage) errorMessage.style.display = 'none';
                sessionStorage.clear();

                let redirectUrl = 'views/LUPTO/dashboard.php';
                const role = response.user.role;
                if (role === 'picto' || role === 'pitco') {
                    redirectUrl = 'views/PICTO/dashboard.php';
                } else if (role === 'lupto') {
                    redirectUrl = 'views/LUPTO/dashboard.php';
                } else if (role === 'municipal' || role.endsWith('_mto')) {
                    redirectUrl = 'views/MUNICIPAL/dashboard.php';
                }

                if (successModal) {
                    successModal.classList.add('active');
                }

                // Sync PHP session — must await so browser doesn't cancel it on redirect
                await fetch('sync-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ user: response.user })
                });

                // Brief modal display then redirect
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 300);
            }
        } catch (err) {
            console.error('Login error:', err);
            showError(err.message || 'Invalid email or password.');
            emailInput.disabled = false;
            passwordInput.disabled = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });

    function showError(msg) {
        if (errorModal && errorModalText) {
            errorModalText.textContent = msg;
            errorModal.classList.add('active');
        } else if (errorMessage) {
            errorMessage.textContent = msg;
            errorMessage.style.display = 'block';
        }
    }
});
