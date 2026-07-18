<?php
// Password Reset View
?>
<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Intan Elyu Logo">
        </div>
        <h1 id="reset-title">Reset Password</h1>
        
        <!-- Animated Seamless SVG Wave -->
        <div class="wave-bottom">
            <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                <path class="wave-layer wave-1" fill="rgba(30,41,59,0.3)" d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,100 L0,100 Z"></path>
                <path class="wave-layer wave-2" fill="rgba(30,41,59,0.5)" d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,100 L0,100 Z"></path>
                <path class="wave-layer wave-3" fill="#1e293b" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,100 L0,100 Z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Bottom Section -->
    <div class="auth-bottom" style="padding-top: 40px;">
        <div class="forms-wrapper" style="padding: 0 20px;">
            <div class="form-panel" style="width:100%; display:block; opacity:1; transform:none;">
                <form id="form-reset-password" onsubmit="handleResetPassword(event)">
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reset-password-val" class="auth-input" placeholder="New Password" required minlength="8">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reset-password-confirm" class="auth-input" placeholder="Confirm Password" required minlength="8">
                    </div>
                    
                    <button type="submit" id="btn-submit-reset" class="btn-circle-submit" style="margin-top: 24px;">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    const email = params.get('email');
    const backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';

    if (!token || !email) {
        if (typeof showToast === 'function') showToast('Invalid or missing password reset token.');
        setTimeout(() => {
            if (typeof navigateTo === 'function') navigateTo('auth');
        }, 2000);
    }

    window.handleResetPassword = async function(e) {
        e.preventDefault();
        
        const password = document.getElementById('reset-password-val').value;
        const confirmPassword = document.getElementById('reset-password-confirm').value;

        if (password !== confirmPassword) {
            if (typeof showToast === 'function') showToast('Passwords do not match.');
            return;
        }

        const btn = document.getElementById('btn-submit-reset');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;

        try {
            const response = await fetch(backendUrl + '/api/auth/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    email: email,
                    password: password,
                    password_confirmation: confirmPassword
                })
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || data.error || 'Failed to reset password.');
            }

            if (typeof showToast === 'function') showToast('Password reset successfully! Log in now.');
            
            setTimeout(() => {
                const cleanUrl = new URL(window.location.origin + window.location.pathname);
                cleanUrl.searchParams.set('view', 'auth');
                window.history.replaceState({ view: 'auth' }, '', cleanUrl);
                
                if (typeof navigateTo === 'function') navigateTo('auth');
            }, 2000);

        } catch (error) {
            console.error('Reset Password Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    };
})();
</script>
