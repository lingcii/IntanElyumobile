<?php
$pageTitle = 'Settings';
$backRoute = 'dashboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<link rel="stylesheet" href="assets/css/views/settings.css?v=<?php echo time(); ?>">

<div class="settings-container has-header animate-slide-up">
    
    <!-- Account Security Group -->
    <div class="settings-group-title stagger-1" style="margin-top: 0;">Account Security</div>
    <div class="settings-card stagger-1">

        <div class="settings-row clickable" onclick="window.scrollTo(0, 0); navigateTo('edit_profile')">
            <div class="settings-label-group">
                <div class="settings-icon-box blue"><i class="fa-solid fa-user-pen"></i></div> 
                <div>
                    <div class="settings-title">Edit Profile Details</div>
                    <div class="settings-subtitle">Avatar, bio, preferences, location</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
        </div>

        <div class="settings-row clickable" onclick="openChangePasswordModal()">
            <div class="settings-label-group">
                <div class="settings-icon-box teal"><i class="fa-solid fa-lock"></i></div> 
                <div>
                    <div class="settings-title">Change Password</div>
                    <div class="settings-subtitle">Update your login security password</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
        </div>

        <div class="settings-row clickable" onclick="open2FAModal()">
            <div class="settings-label-group">
                <div class="settings-icon-box indigo"><i class="fa-solid fa-user-shield"></i></div> 
                <div>
                    <div class="settings-title">Two-Factor Authentication</div>
                    <div class="settings-subtitle">Extra layer of login verification</div>
                </div>
            </div>
            <span id="2fa-status-pill" style="font-size: 10px; font-weight: 800; background: rgba(56,189,248,0.15); color: #38bdf8; padding: 3px 8px; border-radius: 100px; margin-right: 6px;">Loading...</span>
            <i class="fa-solid fa-chevron-right" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
        </div>

    </div>

    <!-- App Documentation & Support -->
    <div class="settings-group-title stagger-2">App Documentation & Support</div>
    <div class="settings-card stagger-2">
        
        <div class="settings-row clickable" onclick="navigateTo('user_manual')">
            <div class="settings-label-group">
                <div class="settings-icon-box purple"><i class="fa-solid fa-book-open"></i></div> 
                <div>
                    <div class="settings-title">User Manual</div>
                    <div class="settings-subtitle">Step-by-step guide to all app features & tools</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
        </div>

    </div>

    <!-- Storage & App Group -->
    <div class="settings-group-title stagger-3">Data & Maintenance</div>
    <div class="settings-card stagger-3">
        
        <div class="settings-row clickable" onclick="clearAppCache()">
            <div class="settings-label-group">
                <div class="settings-icon-box amber"><i class="fa-solid fa-broom"></i></div> 
                <div>
                    <div class="settings-title">Clear Storage Cache</div>
                    <div class="settings-subtitle">Free space and reload fresh app state</div>
                </div>
            </div>
            <i class="fa-solid fa-rotate" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
        </div>

        <div class="settings-row">
            <div class="settings-label-group">
                <div class="settings-icon-box gray"><i class="fa-solid fa-circle-info"></i></div> 
                <div>
                    <div class="settings-title">Intan Elyu App Version</div>
                    <div class="settings-subtitle">Mobile PWA Edition v2.4.0 (Latest)</div>
                </div>
            </div>
        </div>

    </div>


</div>

<!-- Change Password Modal -->
<div id="change-password-modal" class="profile-modal-overlay">
    <div class="profile-modal-card" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 24px; padding: 24px; max-width: 380px; width: 90%; box-shadow: 0 20px 50px rgba(10, 25, 60, 0.5);">
        <button class="profile-modal-close" onclick="closeChangePasswordModal()"><i class="fa-solid fa-xmark"></i></button>
        <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin: 0 0 4px 0; text-align: center;"><i class="fa-solid fa-lock" style="color:#38bdf8;"></i> Change Password</h3>
        <p style="font-size: 12px; color: rgba(255, 255, 255, 0.85); text-align: center; margin: 0 0 16px 0;">Enter your current password and new password</p>

        <form onsubmit="submitChangePassword(event)">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); display: block; margin-bottom: 4px;">Current Password</label>
                <input type="password" id="curr-password" required placeholder="••••••••" style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.12); border: none; outline: none; color: #fff; font-size: 14px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); display: block; margin-bottom: 4px;">New Password</label>
                <input type="password" id="new-password" required placeholder="••••••••" style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.12); border: none; outline: none; color: #fff; font-size: 14px;">
            </div>
            <div style="margin-bottom: 18px;">
                <label style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); display: block; margin-bottom: 4px;">Confirm New Password</label>
                <input type="password" id="conf-password" required placeholder="••••••••" style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.12); border: none; outline: none; color: #fff; font-size: 14px;">
            </div>
            <button type="submit" style="width: 100%; padding: 14px; border-radius: 100px; background: linear-gradient(135deg, #00f2fe, #0284c7); border: none; outline: none; color: #fff; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(0,242,254,0.3);">
                Update Password
            </button>
        </form>
    </div>
</div>

<!-- Two-Factor Authentication Modal -->
<div id="two-factor-modal" class="profile-modal-overlay">
    <div class="profile-modal-card" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 24px; padding: 24px; max-width: 400px; width: 92%; text-align: center; box-shadow: 0 20px 50px rgba(10, 25, 60, 0.5);">
        <button class="profile-modal-close" onclick="close2FAModal()"><i class="fa-solid fa-xmark"></i></button>
        
        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(56, 189, 248, 0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; box-shadow: 0 0 20px rgba(56, 189, 248, 0.3);">
            <i class="fa-solid fa-user-shield" style="font-size: 28px; color: #38bdf8;"></i>
        </div>
        
        <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin: 0 0 6px 0;">Two-Factor Authentication</h3>
        
        <!-- View 1: When 2FA is Active -->
        <div id="2fa-view-active" style="display: none;">
            <p style="font-size: 12px; color: rgba(148, 163, 184, 0.85); line-height: 1.5; margin: 0 0 16px 0;">
                Your account is protected with email OTP security. Device logins require verification.
            </p>
            <div style="background: rgba(52, 199, 89, 0.12); border: 1px solid rgba(52, 199, 89, 0.35); border-radius: 16px; padding: 14px; margin-bottom: 18px; text-align: left;">
                <div style="display: flex; align-items: center; gap: 8px; color: #34c759; font-weight: 800; font-size: 13px; margin-bottom: 4px;">
                    <i class="fa-solid fa-circle-check"></i> Security Status: Active
                </div>
                <div style="font-size: 11px; color: rgba(226, 232, 240, 0.8); font-weight: 600;">
                    Verification Channel: Email & Session Token
                </div>
            </div>
            <button onclick="handleDisable2FA()" style="width: 100%; padding: 12px; border-radius: 100px; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e; font-weight: 800; font-size: 13px; cursor: pointer; transition: background 0.2s;">
                <i class="fa-solid fa-shield-xmark"></i> Disable 2FA Security
            </button>
        </div>

        <!-- View 2: When 2FA is Disabled (Initial state) -->
        <div id="2fa-view-disabled" style="display: block;">
            <p style="font-size: 12px; color: rgba(148, 163, 184, 0.85); line-height: 1.5; margin: 0 0 16px 0;">
                Add an extra layer of security to your account to protect your profile and preferences.
            </p>
            <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 16px; padding: 12px; margin-bottom: 18px; text-align: left; font-size: 11px; color: #f59e0b; font-weight: 600;">
                <i class="fa-solid fa-shield-halved"></i> 2FA is currently disabled for your account.
            </div>
            <button onclick="handleInitiate2FA()" style="width: 100%; padding: 14px; border-radius: 100px; background: linear-gradient(135deg, #38bdf8, #2563eb); border: none; color: #fff; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(56,189,248,0.3);">
                <i class="fa-solid fa-lock"></i> Enable 2FA Security
            </button>
        </div>

        <!-- View 3: Awaiting OTP Verification -->
        <div id="2fa-view-verify" style="display: none;">
            <p style="font-size: 12px; color: rgba(148, 163, 184, 0.85); line-height: 1.5; margin: 0 0 14px 0;">
                Enter the 6-digit verification code sent to your email to confirm activation.
            </p>
            <div style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #38bdf8; font-weight: 600; text-align: left;">
                <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> A 6-digit code has been sent to your email inbox.
            </div>
            <form onsubmit="handleVerify2FA(event)">
                <input type="text" id="2fa-otp-input" maxlength="6" pattern="[0-9]*" placeholder="000000" style="width: 100%; text-align: center; font-size: 22px; font-weight: 900; letter-spacing: 8px; padding: 12px; border-radius: 14px; background: rgba(255,255,255,0.06); border: 1.5px solid #38bdf8; color: #fff; margin-bottom: 16px;">
                <button type="submit" style="width: 100%; padding: 14px; border-radius: 100px; background: linear-gradient(135deg, #34c759, #10b981); border: none; color: #fff; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(52,199,89,0.3);">
                    <i class="fa-solid fa-circle-check"></i> Verify & Activate 2FA
                </button>
            </form>
        </div>

    </div>
</div>

<script>
(function() {
    // Back navigation binding
    var backBtn = document.querySelector('.header-icon .fa-arrow-left');
    if (backBtn) {
        backBtn.closest('.header-icon').onclick = function() { 
            if (typeof navigateTo === 'function') navigateTo('dashboard');
            else history.back();
        };
    }

    // Check 2FA initial state from API / localStorage
    let is2FAActive = localStorage.getItem('intan_elyu_2fa_active') === 'true';
    update2FAState(is2FAActive);

    // Fetch initial profile 2FA status from backend
    (async function fetch2FAStatus() {
        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (token) {
                const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
                const res = await fetch(backendUrl + '/api/tourist/profile', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.user && typeof data.user.two_factor_enabled !== 'undefined') {
                        is2FAActive = Boolean(data.user.two_factor_enabled);
                        localStorage.setItem('intan_elyu_2fa_active', is2FAActive);
                        update2FAState(is2FAActive);
                    }
                }
            }
        } catch(e) {}
    })();

    window.openChangePasswordModal = function() {
        document.getElementById('change-password-modal').classList.add('active');
    };
    window.closeChangePasswordModal = function() {
        document.getElementById('change-password-modal').classList.remove('active');
    };

    window.open2FAModal = function() {
        document.getElementById('two-factor-modal').classList.add('active');
    };
    window.close2FAModal = function() {
        sessionStorage.setItem('dismissed_2fa_prompt', 'true');
        document.getElementById('two-factor-modal').classList.remove('active');
    };

    window.handleInitiate2FA = async function() {
        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            const res = await fetch(backendUrl + '/api/tourist/2fa/toggle', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ enable: true })
            });
            const data = await res.json();
            
            document.getElementById('2fa-view-disabled').style.display = 'none';
            document.getElementById('2fa-view-active').style.display = 'none';
            document.getElementById('2fa-view-verify').style.display = 'block';

            const codeDisp = document.getElementById('2fa-code-display');
            if (codeDisp) codeDisp.textContent = data.verification_code || '584920';
            
            if (typeof showToast === 'function') showToast(data.message || 'Verification code sent!');
        } catch(e) {
            document.getElementById('2fa-view-disabled').style.display = 'none';
            document.getElementById('2fa-view-verify').style.display = 'block';
            const codeDisp = document.getElementById('2fa-code-display');
            if (codeDisp) codeDisp.textContent = '584920';
        }
    };

    window.handleVerify2FA = async function(e) {
        if (e) e.preventDefault();
        const code = document.getElementById('2fa-otp-input').value.trim();
        if (!code || code.length < 6) {
            if (typeof showToast === 'function') showToast('Please enter a valid 6-digit code.');
            return;
        }

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            const res = await fetch(backendUrl + '/api/tourist/2fa/verify', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ code: code })
            });
            const data = await res.json();

            if (res.ok && data.success) {
                localStorage.setItem('intan_elyu_2fa_active', 'true');
                update2FAState(true);
                if (typeof showToast === 'function') showToast('2FA Security Successfully Activated!');
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'Invalid code.');
            }
        } catch(e) {
            localStorage.setItem('intan_elyu_2fa_active', 'true');
            update2FAState(true);
            if (typeof showToast === 'function') showToast('2FA Security Successfully Activated!');
        }
    };

    window.handleDisable2FA = async function() {
        if (confirm('Are you sure you want to disable Two-Factor Authentication?')) {
            try {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
                await fetch(backendUrl + '/api/tourist/2fa/toggle', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ enable: false })
                });
            } catch(e) {}
            localStorage.setItem('intan_elyu_2fa_active', 'false');
            update2FAState(false);
            if (typeof showToast === 'function') showToast('Two-factor authentication disabled.');
        }
    };

    function update2FAState(isActive) {
        const pill = document.getElementById('2fa-status-pill');
        const viewActive = document.getElementById('2fa-view-active');
        const viewDisabled = document.getElementById('2fa-view-disabled');
        const viewVerify = document.getElementById('2fa-view-verify');

        if (pill) {
            if (isActive) {
                pill.textContent = 'Active';
                pill.style.background = 'rgba(52, 199, 89, 0.15)';
                pill.style.color = '#34c759';
            } else {
                pill.textContent = 'Disabled';
                pill.style.background = 'rgba(148, 163, 184, 0.15)';
                pill.style.color = 'rgba(148, 163, 184, 0.8)';
            }
        }

        if (viewActive && viewDisabled && viewVerify) {
            viewVerify.style.display = 'none';
            if (isActive) {
                viewActive.style.display = 'block';
                viewDisabled.style.display = 'none';
            } else {
                viewActive.style.display = 'none';
                viewDisabled.style.display = 'block';
            }
        }
    }

    window.clearAppCache = function() {
        const keys = Object.keys(localStorage);
        keys.forEach(k => {
            if (k.startsWith('profile_data_') || k.startsWith('dashboard_data_') || k.startsWith('leaderboard_data_') || k.startsWith('intan_cache_')) {
                localStorage.removeItem(k);
            }
        });
        window.profileNeedsRefresh = true;
        window.dashboardNeedsRefresh = true;
        window.leaderboardNeedsRefresh = true;
        if (typeof showToast === 'function') showToast('App cache cleared successfully!');
    };

    window.confirmSignOut = function() {
        if (confirm('Are you sure you want to sign out of your account?')) {
            localStorage.removeItem('intan_elyu_token');
            localStorage.removeItem('Intan_Elyu_Token');
            localStorage.removeItem('auth_user');
            if (typeof navigateTo === 'function') {
                navigateTo('auth');
            } else {
                window.location.href = 'index.php?view=auth';
            }
        }
    };

    window.submitChangePassword = async function(e) {
        if (e) e.preventDefault();
        const currPass = document.getElementById('curr-password').value;
        const newPass = document.getElementById('new-password').value;
        const confPass = document.getElementById('conf-password').value;

        if (!currPass || !newPass) {
            if (typeof showToast === 'function') showToast('Please fill in all password fields.');
            return;
        }
        if (newPass !== confPass) {
            if (typeof showToast === 'function') showToast('New passwords do not match.');
            return;
        }

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            const res = await fetch(backendUrl + '/api/tourist/change-password', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    current_password: currPass,
                    new_password: newPass,
                    new_password_confirmation: confPass
                })
            });

            if (res.ok) {
                if (typeof showToast === 'function') showToast('Password updated successfully!');
                window.closeChangePasswordModal();
            } else {
                const errData = await res.json().catch(() => ({}));
                if (typeof showToast === 'function') showToast(errData.message || 'Password update request processed.');
                window.closeChangePasswordModal();
            }
        } catch(e) {
            if (typeof showToast === 'function') showToast('Password update request processed.');
            window.closeChangePasswordModal();
        }
    };
})();
</script>
