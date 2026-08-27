<?php
// Password Reset View
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
?>
<link rel="stylesheet" href="assets/css/views/auth.css?v=<?= time() ?>">
<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img id="auth-logo-img" src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%; display: block;">
        </div>
        <h1 id="reset-title" style="color: #ffffff; font-weight: 800;">Welcome to Elyu</h1>
        
        <!-- Animated Seamless SVG Wave -->
        <div class="wave-bottom">
            <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="resetWaveGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#00f2fe" stop-opacity="0.85" />
                        <stop offset="40%" stop-color="#06b6d4" stop-opacity="0.65" />
                        <stop offset="100%" stop-color="#0284c7" stop-opacity="0.25" />
                    </linearGradient>
                    <linearGradient id="resetWaveGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.9" />
                        <stop offset="50%" stop-color="#0284c7" stop-opacity="0.75" />
                        <stop offset="100%" stop-color="#1e3a8a" stop-opacity="0.45" />
                    </linearGradient>
                    <linearGradient id="resetWaveGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#96afe7" stop-opacity="1" />
                        <stop offset="100%" stop-color="#96afe7" stop-opacity="1" />
                    </linearGradient>
                </defs>
                <path class="wave-layer wave-1" fill="url(#resetWaveGrad1)" d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,160 L0,160 Z"></path>
                <path class="wave-layer wave-2" fill="url(#resetWaveGrad2)" d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,160 L0,160 Z"></path>
                <path class="wave-layer wave-3" fill="#96afe7" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,160 L0,160 Z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Bottom Section -->
    <div class="auth-bottom">
        <div class="forms-wrapper" style="padding: 0 20px; width: 100%; max-width: 350px; margin: 0 auto;">
            <div class="form-panel forgot-form" style="width:100%; display:block; opacity:1; transform:none;">
                <a href="#" class="back-link" onclick="handleResetBack(event)">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

                <div class="fp-header" style="margin-bottom: 24px; text-align: center;">
                    <h3 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-bottom: 6px;">Reset Password</h3>
                    <p style="font-size: 13px; color: #000000; font-weight: 500;">Create a new password for your account<?php if (!empty($email)): ?><br><strong style="color: #1d4ed8; font-family: monospace;"><?= htmlspecialchars($email) ?></strong><?php endif; ?></p>
                </div>

                <form id="form-reset-password" onsubmit="handleResetPassword(event)">
                    <input type="hidden" id="reset-token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
                    <input type="hidden" id="reset-email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">

                    <div class="input-group" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reset-password-val" class="auth-input" placeholder="New Password (min 8 chars)" required minlength="8">
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('reset-password-val', this)"></i>
                    </div>
                    <div class="input-group" style="margin-bottom: 28px;">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reset-password-confirm" class="auth-input" placeholder="Confirm New Password" required minlength="8">
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('reset-password-confirm', this)"></i>
                    </div>
                    
                    <button type="submit" id="btn-submit-reset" class="btn-circle-submit">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, iconEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (iconEl) {
                iconEl.classList.remove('fa-eye');
                iconEl.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (iconEl) {
                iconEl.classList.remove('fa-eye-slash');
                iconEl.classList.add('fa-eye');
            }
        }
    }

    (function () {
        const params = new URLSearchParams(window.location.search);
        const token = document.getElementById('reset-token')?.value || params.get('token') || '';
        const email = document.getElementById('reset-email')?.value || params.get('email') || '';
        const backendUrl = (typeof window.getBackendUrl === 'function') ? window.getBackendUrl() : (window.backendUrl || window.location.origin);
        const isApk = navigator.userAgent.includes('IntanElyuAPK') || !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());

        window.handleResetBack = function (e) {
            if (e) e.preventDefault();
            if (typeof navigateTo === 'function') {
                navigateTo('auth', true, true);
            } else {
                window.location.href = 'index.php?view=auth' + (email ? '&email=' + encodeURIComponent(email) : '');
            }
        };

        window.launchInApk = function () {
            const customScheme = 'intanelyu://?view=reset-password&token=' + encodeURIComponent(token) + '&email=' + encodeURIComponent(email);
            const intentUrl = 'intent://app.intan-elyu.online/?view=reset-password&token=' + encodeURIComponent(token) + '&email=' + encodeURIComponent(email) + '#Intent;scheme=https;package=com.intan.elyu;end;';
            window.location.href = customScheme;
            setTimeout(() => {
                try { window.location.href = intentUrl; } catch (e) { }
            }, 500);
        };

        const apkBox = document.getElementById('apk-launch-box');
        if (apkBox && isApk) {
            apkBox.style.display = 'none';
        } else if (!isApk && (params.get('open_app') === '1' || /android/i.test(navigator.userAgent))) {
            // Auto-attempt launch into APK
            window.launchInApk();
        }

        if (!token || !email) {
            console.warn('Token or email query parameter missing from URL.');
        }

        window.handleResetPassword = async function (e) {
            e.preventDefault();

            const password = document.getElementById('reset-password-val').value;
            const confirmPassword = document.getElementById('reset-password-confirm').value;

            if (password.length < 8) {
                if (typeof showToast === 'function') showToast('Password must be at least 8 characters long.');
                return;
            }

            if (password !== confirmPassword) {
                if (typeof showToast === 'function') showToast('Passwords do not match. Please verify.');
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

                // Store prefill email for login credentials screen
                if (email) {
                    sessionStorage.setItem('login_prefill_email', email);
                }

                if (typeof showToast === 'function') {
                    showToast('Password changed successfully! Please log in with your new credentials.', 'success');
                }

                setTimeout(() => {
                    const isApk = navigator.userAgent.includes('IntanElyuAPK') || !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());

                    // If on Android mobile browser, try deep link return to APK on auth view
                    if (!isApk && /android/i.test(navigator.userAgent)) {
                        try {
                            const directUrl = 'intanelyu://?view=auth&email=' + encodeURIComponent(email || '');
                            window.location.href = directUrl;
                            setTimeout(() => {
                                if (typeof navigateTo === 'function') {
                                    navigateTo('auth', true, true);
                                } else {
                                    window.location.href = 'index.php?view=auth' + (email ? '&email=' + encodeURIComponent(email) : '');
                                }
                            }, 1200);
                            return;
                        } catch (e) { }
                    }

                    if (typeof navigateTo === 'function') {
                        navigateTo('auth', true, true);
                    } else {
                        window.location.href = 'index.php?view=auth' + (email ? '&email=' + encodeURIComponent(email) : '');
                    }
                }, 1200);

            } catch (error) {
                console.error('Reset Password Error:', error);
                if (typeof showToast === 'function') showToast(error.message);
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        };
    })();
</script>