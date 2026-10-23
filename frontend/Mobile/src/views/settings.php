<?php
$pageTitle = 'Settings';
$backRoute = 'dashboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

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
            <span id="2fa-status-pill"
                style="font-size: 10px; font-weight: 800; background: rgba(56,189,248,0.15); color: #38bdf8; padding: 3px 8px; border-radius: 100px; margin-right: 6px;">Loading...</span>
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

        <div class="settings-row clickable" onclick="if (typeof window.checkForAppUpdate === 'function') { window.checkForAppUpdate(); } else { window.open('https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk', '_system'); }">
            <div class="settings-label-group">
                <div class="settings-icon-box blue"><i class="fa-solid fa-cloud-arrow-down"></i></div>
                <div>
                    <div class="settings-title">Check for App Updates</div>
                    <div class="settings-subtitle">v0.0.0 (Dev Build 1) &bull; Tap to update</div>
                </div>
            </div>
            <span style="font-size: 10px; font-weight: 800; background: rgba(56,189,248,0.2); color: #38bdf8; padding: 3px 10px; border-radius: 100px; margin-right: 6px;">Check</span>
            <i class="fa-solid fa-chevron-right" style="color: rgba(255,255,255,0.3); font-size: 12px;"></i>
        </div>

        <div class="settings-row clickable" onclick="confirmSignOut()">
            <div class="settings-label-group">
                <div class="settings-icon-box logout red"
                    style="background: #FF3B30 !important; box-shadow: 0 2px 8px rgba(255, 59, 48, 0.35) !important;"><i
                        class="fa-solid fa-arrow-right-from-bracket" style="color: #ffffff !important;"></i></div>
                <div>
                    <div class="settings-title" style="color: #FF3B30;">Sign Out</div>
                    <div class="settings-subtitle" style="color: rgba(255, 255, 255, 0.78);">Log out of your tourist
                        account</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(255, 255, 255, 0.3); font-size: 12px;"></i>
        </div>

    </div>


</div>

<!-- Change Password Modal -->
<div id="change-password-modal" class="profile-modal-overlay">
    <div class="profile-modal-card"
        style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 24px; padding: 24px; max-width: 380px; width: 90%; box-shadow: 0 20px 50px rgba(10, 25, 60, 0.5);">
        <button class="profile-modal-close" onclick="closeChangePasswordModal()"><i
                class="fa-solid fa-xmark"></i></button>
        <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin: 0 0 4px 0; text-align: center;"><i
                class="fa-solid fa-lock" style="color:#38bdf8;"></i> Change Password</h3>
        <p style="font-size: 12px; color: rgba(255, 255, 255, 0.85); text-align: center; margin: 0 0 16px 0;">Enter your
            current password and new password</p>

        <form onsubmit="submitChangePassword(event)">
            <div style="margin-bottom: 12px;">
                <label
                    style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); display: block; margin-bottom: 4px;">Current
                    Password</label>
                <input type="password" id="curr-password" required placeholder="••••••••"
                    style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.12); border: none; outline: none; color: #fff; font-size: 14px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label
                    style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); display: block; margin-bottom: 4px;">New
                    Password</label>
                <input type="password" id="new-password" required placeholder="••••••••"
                    oninput="validateChangePasswordMetrics()"
                    style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.12); border: none; outline: none; color: #fff; font-size: 14px;">

                <div id="change-pwd-strength-container" class="pwd-strength-wrapper"
                    style="display: none; margin-top: 6px; margin-bottom: 8px;" data-score="0">
                    <div class="pwd-strength-segments">
                        <div class="pwd-segment seg-1"></div>
                        <div class="pwd-segment seg-2"></div>
                        <div class="pwd-segment seg-3"></div>
                        <div class="pwd-segment seg-4"></div>
                    </div>
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-top: 5px; font-size: 11px; font-weight: 700;">
                        <span id="change-pwd-strength-label"
                            style="color: #94a3b8; transition: color 0.2s ease;">Password Strength</span>
                        <span id="change-pwd-strength-score"
                            style="color: rgba(148, 163, 184, 0.7); font-size: 10px;">0/4</span>
                    </div>
                    <div class="pwd-checklist"
                        style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px; font-size: 10.5px; font-weight: 600;">
                        <span id="chg-len8" class="pwd-chk-item"><i class="fa-solid fa-circle"
                                style="font-size: 6px; vertical-align: middle;"></i> 8+ chars</span>
                        <span id="chg-num" class="pwd-chk-item"><i class="fa-solid fa-circle"
                                style="font-size: 6px; vertical-align: middle;"></i> a number</span>
                        <span id="chg-cap" class="pwd-chk-item"><i class="fa-solid fa-circle"
                                style="font-size: 6px; vertical-align: middle;"></i> a capital</span>
                        <span id="chg-sym" class="pwd-chk-item"><i class="fa-solid fa-circle"
                                style="font-size: 6px; vertical-align: middle;"></i> a symbol</span>
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 18px;">
                <label
                    style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); display: block; margin-bottom: 4px;">Confirm
                    New Password</label>
                <input type="password" id="conf-password" required placeholder="••••••••"
                    style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.12); border: none; outline: none; color: #fff; font-size: 14px;">
            </div>
            <button type="submit"
                style="width: 100%; padding: 14px; border-radius: 100px; background: linear-gradient(135deg, #00f2fe, #0284c7); border: none; outline: none; color: #fff; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(0,242,254,0.3);">
                Update Password
            </button>
        </form>
    </div>
</div>

<!-- Two-Factor Authentication Modal -->
<div id="two-factor-modal" class="profile-modal-overlay" onclick="if(event.target===this) close2FAModal();">
    <div class="profile-modal-card"
        style="background: #ffffff !important; border: none !important; outline: none !important; border-radius: 24px; padding: 0 !important; max-width: 385px; width: 92%; max-height: 88vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.45); color: #0f172a !important; box-sizing: border-box; text-align: center; position: relative;">
        
        <!-- Modal Top Header Banner (Royal Blue) with Exit Button on Right -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 14px 18px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-shield-halved" style="color: #00f2fe; font-size: 15px;"></i> Two-Factor Security
                </h4>
                <p style="margin: 2px 0 0 0; font-size: 11px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Account Protection
                </p>
            </div>
            <button type="button" onclick="close2FAModal()" aria-label="Close"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 12px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 14px;"></i>
            </button>
        </div>

        <!-- Middle Body Area (Pure White Background) -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 22px 20px 18px 20px; text-align: center; color: #0f172a !important; box-sizing: border-box;">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff !important; color: #1e3a8a !important; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.12); border: 1.5px solid #dbeafe;">
                <i class="fa-solid fa-shield-halved" style="font-size: 28px; color: #1e3a8a;"></i>
            </div>

            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; letter-spacing: -0.3px;">Two-Factor Authentication</h3>

            <!-- View 1: When 2FA is Active -->
            <div id="2fa-view-active" style="display: none;">
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 16px 0;">
                    Your account is protected with email verification codes. Device logins require OTP verification.
                </p>
                <div
                    style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 16px; padding: 14px; text-align: left;">
                    <div
                        style="display: flex; align-items: center; gap: 8px; color: #16a34a; font-weight: 800; font-size: 13px; margin-bottom: 4px;">
                        <i class="fa-solid fa-circle-check"></i> Security Status: Active
                    </div>
                    <div style="font-size: 11.5px; color: #475569; font-weight: 600;">
                        Verification Channel: Registered Email Address
                    </div>
                </div>
            </div>

            <!-- View 2: When 2FA is Disabled (Initial state) -->
            <div id="2fa-view-disabled" style="display: block;">
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 16px 0;">
                    Add an extra layer of security to your tourist account. When logging in, you will be prompted for a 6-digit security code sent to your email.
                </p>
                <div
                    style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 16px; padding: 12px 14px; text-align: left; font-size: 12px; color: #b45309; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 15px;"></i>
                    <span>2FA is currently disabled for your account.</span>
                </div>
            </div>

            <!-- View 3: Awaiting OTP Verification -->
            <div id="2fa-view-verify" style="display: none;">
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 14px 0;">
                    Enter the 6-digit verification code sent to your email to confirm activation.
                </p>
                <div
                    style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 14px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #0284c7; font-weight: 700; text-align: left; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span id="settings-2fa-verify-msg">A 6-digit code has been sent to your email.</span>
                </div>
                <form id="settings-2fa-form" onsubmit="handleVerify2FA(event); return false;">
                    <div id="settings-2fa-boxes" style="display: flex; justify-content: center; gap: 8px; margin-bottom: 16px;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleSettings2FAOtpInput(this, 0)" onkeydown="handleSettings2FAOtpKeydown(this, event, 0)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 1)" onkeydown="handleSettings2FAOtpKeydown(this, event, 1)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 2)" onkeydown="handleSettings2FAOtpKeydown(this, event, 2)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 3)" onkeydown="handleSettings2FAOtpKeydown(this, event, 3)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 4)" onkeydown="handleSettings2FAOtpKeydown(this, event, 4)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 5)" onkeydown="handleSettings2FAOtpKeydown(this, event, 5)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                    </div>
                    <div style="margin-bottom: 6px; font-size: 12px; color: #64748b; font-weight: 600;">
                        Didn't get code? 
                        <span id="settings-2fa-resend-btn" onclick="handleInitiate2FA()" style="color: #0284c7; font-weight: 800; cursor: pointer; text-decoration: underline;">Resend</span>
                        <span id="settings-2fa-timer" style="display: none; color: #94a3b8; font-weight: 700;"></span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner (Royal Blue) for Action Buttons -->
        <div id="two-factor-modal-footer"
            style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 14px 18px; border: none !important; outline: none !important; display: flex; flex-direction: column; gap: 8px; box-sizing: border-box;">
            
            <!-- Footer Action 1: When 2FA is Disabled (Enable 2FA Security Button) -->
            <div id="2fa-footer-disabled" style="display: block;">
                <button type="button" id="btn-settings-enable-2fa" onclick="handleInitiate2FA()"
                    style="width: 100%; padding: 13px 20px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #1e3a8a !important; font-size: 14px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-lock" style="color: #1e3a8a !important;"></i> Enable 2FA Security
                </button>
            </div>

            <!-- Footer Action 2: When 2FA is Awaiting Verification (Verify & Activate 2FA Button) -->
            <div id="2fa-footer-verify" style="display: none;">
                <button type="button" id="btn-settings-verify-2fa" onclick="handleVerify2FA(event)"
                    style="width: 100%; padding: 13px 20px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #16a34a !important; font-size: 14px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-circle-check" style="color: #16a34a !important;"></i> Verify &amp; Activate 2FA
                </button>
            </div>

            <!-- Footer Action 3: When 2FA is Active (Disable 2FA Security Button) -->
            <div id="2fa-footer-active" style="display: none;">
                <button type="button" onclick="handleDisable2FA()"
                    style="width: 100%; padding: 12px 18px; border: 1.5px solid #fecdd3 !important; outline: none !important; border-radius: 100px; background: #fff1f2 !important; color: #e11d48 !important; font-size: 13.5px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-shield-xmark" style="color: #e11d48 !important;"></i> Disable 2FA Security
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        // Back navigation binding
        var backBtn = document.querySelector('.header-icon .fa-arrow-left');
        if (backBtn) {
            backBtn.closest('.header-icon').onclick = function () {
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
                const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                if (typeof authUser.two_factor_enabled !== 'undefined') {
                    is2FAActive = Boolean(authUser.two_factor_enabled);
                    update2FAState(is2FAActive);
                }
            } catch (e) { }


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
            } catch (e) { }
        })();

        window.openChangePasswordModal = function () {
            document.getElementById('change-password-modal').classList.add('active');
        };
        window.closeChangePasswordModal = function () {
            document.getElementById('change-password-modal').classList.remove('active');
        };

        window.open2FAModal = function () {
            const isVerifying = document.getElementById('2fa-view-verify') && document.getElementById('2fa-view-verify').style.display === 'block';
            if (!isVerifying) {
                update2FAState(is2FAActive);
            }
            document.getElementById('two-factor-modal').classList.add('active');
        };
        window.close2FAModal = function () {
            sessionStorage.setItem('dismissed_2fa_prompt', 'true');
            document.getElementById('two-factor-modal').classList.remove('active');
        };

        window.handleSettings2FAOtpInput = function (el, index) {
            el.value = el.value.replace(/[^0-9]/g, '');
            const boxes = document.querySelectorAll('.settings-2fa-otp-box');
            if (el.value && index < boxes.length - 1) {
                boxes[index + 1].focus();
            }
            const fullCode = Array.from(boxes).map(b => b.value).join('');
            if (fullCode.length === 6) {
                window.handleVerify2FA();
            }
        };

        window.handleSettings2FAOtpKeydown = function (el, e, index) {
            const boxes = document.querySelectorAll('.settings-2fa-otp-box');
            if (e.key === 'Backspace' && !el.value && index > 0) {
                boxes[index - 1].focus();
            }
        };

        window.handleSettings2FAOtpPaste = function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            const boxes = document.querySelectorAll('.settings-2fa-otp-box');
            for (let i = 0; i < boxes.length && i < pasted.length; i++) {
                boxes[i].value = pasted[i];
            }
            if (pasted.length >= 6) {
                if (boxes[5]) boxes[5].focus();
                window.handleVerify2FA();
            }
        };

        window.handleInitiate2FA = async function () {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required to configure 2FA security.', 'warning');
                return;
            }

            const btn = document.getElementById('btn-settings-enable-2fa');
            const resendBtn = document.getElementById('settings-2fa-resend-btn');
            const timerEl = document.getElementById('settings-2fa-timer');

            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending Code...';
                btn.disabled = true;
            }
            if (resendBtn) resendBtn.style.pointerEvents = 'none';

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

                if (!res.ok) {
                    throw new Error(data.message || data.error || 'Failed to send 2FA verification code.');
                }

                document.getElementById('2fa-view-disabled').style.display = 'none';
                document.getElementById('2fa-view-active').style.display = 'none';
                document.getElementById('2fa-view-verify').style.display = 'block';

                const fDisabled = document.getElementById('2fa-footer-disabled');
                const fActive = document.getElementById('2fa-footer-active');
                const fVerify = document.getElementById('2fa-footer-verify');
                if (fDisabled) fDisabled.style.display = 'none';
                if (fActive) fActive.style.display = 'none';
                if (fVerify) fVerify.style.display = 'block';

                const msgEl = document.getElementById('settings-2fa-verify-msg');
                if (msgEl && data.email) {
                    msgEl.textContent = `A 6-digit code has been sent to ${data.email}.`;
                }

                const boxes = document.querySelectorAll('.settings-2fa-otp-box');
                boxes.forEach(b => b.value = '');
                if (boxes[0]) setTimeout(() => boxes[0].focus(), 150);

                if (typeof showToast === 'function') showToast('Verification code sent to your email!', 'success');

                // Start 60s cooldown
                let timeLeft = 60;
                if (resendBtn) resendBtn.style.display = 'none';
                if (timerEl) {
                    timerEl.style.display = 'inline';
                    timerEl.textContent = `(${timeLeft}s)`;
                }
                const timer = setInterval(() => {
                    timeLeft--;
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        if (timerEl) timerEl.style.display = 'none';
                        if (resendBtn) {
                            resendBtn.style.display = 'inline';
                            resendBtn.style.pointerEvents = 'auto';
                        }
                    } else if (timerEl) {
                        timerEl.textContent = `(${timeLeft}s)`;
                    }
                }, 1000);
            } catch (e) {
                console.error('2FA Initiate Error:', e);
                if (typeof showToast === 'function') showToast(e.message || 'Error configuring 2FA security.', 'error');
                if (resendBtn) resendBtn.style.pointerEvents = 'auto';
            } finally {
                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-lock" style="color: #1e3a8a !important; margin-right: 6px;"></i> Enable 2FA Security';
                    btn.disabled = false;
                }
            }
        };

        window.handleVerify2FA = async function (e) {
            if (e) e.preventDefault();
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required to verify 2FA security.', 'warning');
                return;
            }

            const boxes = document.querySelectorAll('.settings-2fa-otp-box');
            const code = Array.from(boxes).map(b => b.value.trim()).join('');
            if (!code || code.length < 6) {
                if (typeof showToast === 'function') showToast('Please enter all 6 digits of the code.', 'warning');
                return;
            }

            const btn = document.getElementById('btn-settings-verify-2fa');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';
                btn.disabled = true;
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
                    if (typeof showToast === 'function') showToast('🔒 Two-Factor Authentication Activated!', 'success');
                    setTimeout(() => {
                        window.close2FAModal();
                    }, 1200);
                } else {
                    throw new Error(data.message || 'Invalid 6-digit verification code.');
                }
            } catch (e) {
                console.error('2FA Verification Error:', e);
                if (typeof showToast === 'function') showToast(e.message || 'Verification failed. Please try again.', 'error');
                const boxContainer = document.getElementById('settings-2fa-boxes');
                if (boxContainer) {
                    boxContainer.classList.remove('shake-attention');
                    void boxContainer.offsetWidth;
                    boxContainer.classList.add('shake-attention');
                }
                boxes.forEach(b => b.value = '');
                if (boxes[0]) boxes[0].focus();
            } finally {
                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #16a34a !important; margin-right: 6px;"></i> Verify &amp; Activate 2FA';
                    btn.disabled = false;
                }
            }
        };

        window.handleDisable2FA = async function () {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required to update 2FA settings.', 'warning');
                return;
            }
            if (confirm('Are you sure you want to disable Two-Factor Authentication? Your login security level will be reduced.')) {
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
                        body: JSON.stringify({ enable: false })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Failed to disable 2FA.');
                } catch (e) {
                    console.error('Disable 2FA error:', e);
                }
                localStorage.setItem('intan_elyu_2fa_active', 'false');
                update2FAState(false);
                if (typeof showToast === 'function') showToast('Two-Factor Authentication disabled.', 'info');
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
                    pill.style.background = 'rgba(34, 197, 94, 0.12)';
                    pill.style.color = '#16a34a';
                    pill.style.border = '1.5px solid #22c55e';
                } else {
                    pill.textContent = 'Disabled';
                    pill.style.background = '#f1f5f9';
                    pill.style.color = '#475569';
                    pill.style.border = '1.5px solid #94a3b8';
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

            const fActive = document.getElementById('2fa-footer-active');
            const fDisabled = document.getElementById('2fa-footer-disabled');
            const fVerify = document.getElementById('2fa-footer-verify');
            if (fVerify) fVerify.style.display = 'none';
            if (fActive && fDisabled) {
                if (isActive) {
                    fActive.style.display = 'block';
                    fDisabled.style.display = 'none';
                } else {
                    fActive.style.display = 'none';
                    fDisabled.style.display = 'block';
                }
            }
        }

        window.clearAppCache = function () {
            const keys = Object.keys(localStorage);
            keys.forEach(k => {
                if (k.startsWith('intan_elyu_offline_') || k.includes('queue') || k.includes('sync')) {
                    return; // Preserve offline sync queues
                }
                if (k.startsWith('profile_data_') || k.startsWith('dashboard_data_') || k.startsWith('leaderboard_data_') || k.startsWith('intan_cache_')) {
                    localStorage.removeItem(k);
                }
            });
            window.profileNeedsRefresh = true;
            window.dashboardNeedsRefresh = true;
            window.leaderboardNeedsRefresh = true;
            if (typeof showToast === 'function') showToast('App cache cleared successfully!');
        };

        window.confirmSignOut = function () {
            if (typeof window.showLogoutConfirmationModal === 'function') {
                window.showLogoutConfirmationModal();
            } else if (typeof handleLogout === 'function') {
                handleLogout();
            } else {
                if (confirm('Are you sure you want to log out?')) {
                    if (typeof window.purgeAllUserSessionCaches === 'function') {
                        window.purgeAllUserSessionCaches();
                    } else {
                        localStorage.removeItem('intan_elyu_token');
                        localStorage.removeItem('Intan_Elyu_Token');
                        localStorage.removeItem('api_token');
                        localStorage.removeItem('auth_user');
                        localStorage.removeItem('auth_user_backup');
                        localStorage.removeItem('intan_elyu_user');
                    }
                    window.location.href = 'index.php?view=auth';
                }
            }
        };

        window.validateChangePasswordMetrics = function () {
            const pwdEl = document.getElementById('new-password');
            const container = document.getElementById('change-pwd-strength-container');
            const label = document.getElementById('change-pwd-strength-label');
            const scoreEl = document.getElementById('change-pwd-strength-score');

            const chkLen8 = document.getElementById('chg-len8');
            const chkNum = document.getElementById('chg-num');
            const chkCap = document.getElementById('chg-cap');
            const chkSym = document.getElementById('chg-sym');

            if (!pwdEl || !container) return;

            const pwd = pwdEl.value;
            if (pwd.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            const len8 = pwd.length >= 8;
            const hasNum = /\d/.test(pwd);
            const hasCap = /[A-Z]/.test(pwd);
            const hasSym = /[^A-Za-z0-9]/.test(pwd);

            const score = [len8, hasNum, hasCap, hasSym].filter(Boolean).length;
            container.dataset.score = score;
            if (scoreEl) scoreEl.textContent = score + '/4';

            if (chkLen8) chkLen8.className = 'pwd-chk-item' + (len8 ? ' passed' : '');
            if (chkNum) chkNum.className = 'pwd-chk-item' + (hasNum ? ' passed' : '');
            if (chkCap) chkCap.className = 'pwd-chk-item' + (hasCap ? ' passed' : '');
            if (chkSym) chkSym.className = 'pwd-chk-item' + (hasSym ? ' passed' : '');

            const labels = ['Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const colors = ['#f87171', '#f87171', '#fb923c', '#facc15', '#34d399'];
            if (label) {
                label.textContent = labels[score] || 'Password Strength';
                label.style.color = colors[score] || '#94a3b8';
            }
        };

        window.submitChangePassword = async function (e) {
            if (e) e.preventDefault();
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required to change your account password.');
                return;
            }
            const currPass = document.getElementById('curr-password').value;
            const newPass = document.getElementById('new-password').value;
            const confPass = document.getElementById('conf-password').value;

            if (!currPass || !newPass || !confPass) {
                if (typeof showToast === 'function') showToast('Please fill in all password fields.');
                return;
            }
            if (newPass.length < 8) {
                if (typeof showToast === 'function') showToast('New password must be at least 8 characters.');
                return;
            }
            if (!/[A-Za-z]/.test(newPass) || !/\d/.test(newPass)) {
                if (typeof showToast === 'function') showToast('Password must contain both letters and numbers.');
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

                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    if (typeof showToast === 'function') showToast('Password updated successfully!');
                    document.getElementById('curr-password').value = '';
                    document.getElementById('new-password').value = '';
                    document.getElementById('conf-password').value = '';
                    const container = document.getElementById('change-pwd-strength-container');
                    if (container) container.style.display = 'none';
                    window.closeChangePasswordModal();
                } else {
                    if (typeof showToast === 'function') showToast(data.message || 'Failed to update password.');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('Network error while updating password.');
            }
        };
    })();
</script>