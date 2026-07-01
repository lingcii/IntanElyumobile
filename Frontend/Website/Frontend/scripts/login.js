document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const successModal = document.getElementById('successModal');
    const errorModal = document.getElementById('errorModal');
    const errorModalText = document.getElementById('errorModalText');
    const closeErrorModal = document.getElementById('closeErrorModal');

    // Close error modal handlers
    if (closeErrorModal && errorModal) {
        closeErrorModal.addEventListener('click', () => {
            errorModal.classList.remove('active');
        });

        // Close on background click
        errorModal.addEventListener('click', (e) => {
            if (e.target === errorModal) {
                errorModal.classList.remove('active');
            }
        });
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Login form submitted');
        
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = loginForm.querySelector('.btn-login');
        
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        console.log('Email:', email);

        // UI Loading State
        emailInput.disabled = true;
        passwordInput.disabled = true;
        submitBtn.disabled = true;
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Signing In...';

        try {
            console.log('Calling login API...');
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
            console.log('Login API response:', response);

            if (response.success && response.user) {
                console.log('Login successful! Syncing session...');
                // Send user data directly to sync-session.php
                const syncResponse = await fetch('sync-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ user: response.user })
                });
                console.log('Sync-session response status:', syncResponse.status);
                const syncData = await syncResponse.json();
                console.log('Sync-session data:', syncData);
                
                if (syncData.success) {
                    // Hide any active error message
                    if (errorMessage) errorMessage.style.display = 'none';

                    // Clear previous session storage states so they start fresh on the dashboard
                    sessionStorage.clear();
                    
                    // Determine redirect URL based on role
                    let redirectUrl = 'views/LUPTO/dashboard.php';
                    const role = response.user.role;
                    if (role === 'picto' || role === 'pitco') {
                        redirectUrl = 'views/PICTO/dashboard.php';
                    } else if (role === 'lupto') {
                        redirectUrl = 'views/LUPTO/dashboard.php';
                    } else if (role === 'municipal' || role.endsWith('_mto')) {
                        redirectUrl = 'views/MUNICIPAL/dashboard.php';
                    }

                    // Show success modal
                    if (successModal) {
                        successModal.classList.add('active');
                    }

                    // Wait 1.5 seconds before redirecting to let the user see the modal
                    await new Promise(resolve => setTimeout(resolve, 1500));

                    // Redirect immediately
                    window.location.href = redirectUrl;
                } else {
                    throw new Error('Failed to sync session');
                }
            }
        } catch (err) {
            console.error('Login error:', err);
            
            // Show error popup modal
            if (errorModal && errorModalText) {
                errorModalText.textContent = err.message || 'Invalid email or password.';
                errorModal.classList.add('active');
            } else if (errorMessage) {
                errorMessage.textContent = err.message || 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
            }
            
            // Revert UI Loading State
            emailInput.disabled = false;
            passwordInput.disabled = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
});
