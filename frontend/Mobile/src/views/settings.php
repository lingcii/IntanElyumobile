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
<div id="change-password-modal" class="profile-modal-overlay" onclick="if(event.target===this) closeChangePasswordModal();">
    <div class="profile-modal-card"
        style="background: #ffffff !important; border: none !important; outline: none !important; border-radius: 22px; padding: 0 !important; max-width: 335px; width: 88%; max-height: 86vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.45); color: #0f172a !important; box-sizing: border-box; text-align: left; position: relative;">
        
        <!-- Modal Top Header Banner (Royal Blue) with Exit Button on Right -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 10px 14px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 7px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-lock" style="color: #00f2fe; font-size: 13px;"></i> Change Password
                </h4>
                <p style="margin: 1px 0 0 0; font-size: 10.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Account Credential Security
                </p>
            </div>
            <button type="button" onclick="closeChangePasswordModal()" aria-label="Close"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 10px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 13px;"></i>
            </button>
        </div>

        <!-- Middle Body Area (Pure White Background) -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 14px 14px 10px 14px; color: #0f172a !important; box-sizing: border-box;">
            
            <!-- STEP 1: Enter Password Details -->
            <div id="chg-pwd-step-1" style="display: block;">
                <p style="font-size: 11.5px; color: #64748b; margin: 0 0 12px 0; line-height: 1.4;">
                    Enter your current password and set a secure new password for your account.
                </p>

                <form id="change-password-form" onsubmit="event.preventDefault(); proceedToPasswordSecurityStep();">
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 3px;">
                            Current Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="curr-password" required placeholder="••••••••"
                                style="width: 100%; padding: 10px 38px 10px 12px; border-radius: 10px; background: #f8fafc; border: 1.5px solid #cbd5e1; outline: none; color: #0f172a; font-size: 13px; box-sizing: border-box; transition: border-color 0.2s;"
                                onfocus="this.style.borderColor='#1e3a8a'; this.style.background='#ffffff';"
                                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <button type="button" onclick="togglePasswordVisibility('curr-password', this)" aria-label="Toggle password visibility"
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; font-size: 13px; cursor: pointer; padding: 3px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 3px;">
                            New Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="new-password" required placeholder="••••••••"
                                oninput="validateChangePasswordMetrics()"
                                style="width: 100%; padding: 10px 38px 10px 12px; border-radius: 10px; background: #f8fafc; border: 1.5px solid #cbd5e1; outline: none; color: #0f172a; font-size: 13px; box-sizing: border-box; transition: border-color 0.2s;"
                                onfocus="this.style.borderColor='#1e3a8a'; this.style.background='#ffffff';"
                                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <button type="button" onclick="togglePasswordVisibility('new-password', this)" aria-label="Toggle password visibility"
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; font-size: 13px; cursor: pointer; padding: 3px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>

                        <!-- Strength meter on white -->
                        <div id="change-pwd-strength-container" class="pwd-strength-wrapper"
                            style="display: none; margin-top: 6px; margin-bottom: 6px;" data-score="0">
                            <div class="pwd-strength-segments" style="height: 5px; gap: 6px;">
                                <div class="pwd-segment seg-1" style="box-shadow: none !important; border: none !important; background: #e2e8f0;"></div>
                                <div class="pwd-segment seg-2" style="box-shadow: none !important; border: none !important; background: #e2e8f0;"></div>
                                <div class="pwd-segment seg-3" style="box-shadow: none !important; border: none !important; background: #e2e8f0;"></div>
                                <div class="pwd-segment seg-4" style="box-shadow: none !important; border: none !important; background: #e2e8f0;"></div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 5px; font-size: 10.5px; font-weight: 700;">
                                <span id="change-pwd-strength-label" style="color: #64748b; transition: color 0.2s ease;">Password Strength</span>
                                <span id="change-pwd-strength-score" style="color: #64748b; font-size: 9.5px;">0/4</span>
                            </div>
                            <div class="pwd-checklist" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; font-size: 10px; font-weight: 600;">
                                <span id="chg-len8" class="pwd-chk-item" style="color: #64748b;"><i class="fa-solid fa-circle" style="font-size: 5px; vertical-align: middle;"></i> 8+ chars</span>
                                <span id="chg-num" class="pwd-chk-item" style="color: #64748b;"><i class="fa-solid fa-circle" style="font-size: 5px; vertical-align: middle;"></i> a number</span>
                                <span id="chg-cap" class="pwd-chk-item" style="color: #64748b;"><i class="fa-solid fa-circle" style="font-size: 5px; vertical-align: middle;"></i> a capital</span>
                                <span id="chg-sym" class="pwd-chk-item" style="color: #64748b;"><i class="fa-solid fa-circle" style="font-size: 5px; vertical-align: middle;"></i> a symbol</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 6px;">
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 3px;">
                            Confirm New Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="conf-password" required placeholder="••••••••"
                                style="width: 100%; padding: 10px 38px 10px 12px; border-radius: 10px; background: #f8fafc; border: 1.5px solid #cbd5e1; outline: none; color: #0f172a; font-size: 13px; box-sizing: border-box; transition: border-color 0.2s;"
                                onfocus="this.style.borderColor='#1e3a8a'; this.style.background='#ffffff';"
                                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <button type="button" onclick="togglePasswordVisibility('conf-password', this)" aria-label="Toggle password visibility"
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; font-size: 13px; cursor: pointer; padding: 3px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- STEP 2: Security Verification ("instead of changing right away lets add security") -->
            <div id="chg-pwd-step-2" style="display: none; text-align: center;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; box-shadow: 0 3px 10px rgba(30, 58, 138, 0.08); border: 1.5px solid #dbeafe;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 20px; color: #1e3a8a;"></i>
                </div>

                <h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 3px 0; letter-spacing: -0.2px;">
                    Security Authorization
                </h4>
                <p style="font-size: 11.5px; color: #64748b; line-height: 1.4; margin: 0 0 10px 0;">
                    Confirm authorization before applying credentials to your tourist account.
                </p>

                <!-- Security Details Summary -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 12px; margin-bottom: 10px; font-size: 11px; text-align: left;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 600;">Account:</span>
                        <strong id="chg-pwd-account-name" style="color: #0f172a; font-weight: 800;">Tourist Account</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 600;">Action:</span>
                        <span style="color: #0284c7; font-weight: 800;">Password Credential Reset</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b; font-weight: 600;">Protection:</span>
                        <span style="color: #16a34a; font-weight: 800;"><i class="fa-solid fa-lock"></i> Bcrypt Encryption</span>
                    </div>
                </div>

                <!-- Amber Security Warning -->
                <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 8px 10px; margin-bottom: 10px; font-size: 10.5px; color: #b45309; text-align: left; display: flex; gap: 6px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 12px; flex-shrink: 0; margin-top: 1px;"></i>
                    <div style="line-height: 1.3;"><strong>Notice:</strong> Updating password invalidates other active device sessions for security.</div>
                </div>

                <!-- Security Verification Code Challenge -->
                <div style="background: #eff6ff; border: 1.5px dashed #93c5fd; border-radius: 10px; padding: 8px 12px; margin-bottom: 10px; text-align: center;">
                    <div style="font-size: 10px; font-weight: 700; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                        Security Verification Code
                    </div>
                    <div id="chg-pwd-security-code-display" style="font-size: 20px; font-weight: 900; letter-spacing: 4px; color: #1e3a8a; font-family: monospace;">
                        ----
                    </div>
                </div>

                <div style="margin-bottom: 6px;">
                    <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px; text-align: center;">
                        Enter code above to authorize:
                    </label>
                    <input type="text" id="chg-pwd-security-code-input" maxlength="4" placeholder="••••" oninput="validateSecurityCodeInput()"
                        style="width: 115px; margin: 0 auto; display: block; text-align: center; font-size: 16px; font-weight: 900; letter-spacing: 4px; padding: 7px; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; font-family: monospace;">
                </div>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner (Royal Blue) -->
        <div id="change-password-modal-footer"
            style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 10px 14px; border: none !important; outline: none !important; display: flex; flex-direction: column; gap: 6px; box-sizing: border-box;">
            
            <!-- Footer Actions for Step 1 -->
            <div id="chg-pwd-footer-step-1" style="display: block;">
                <button type="button" id="btn-proceed-security-check" onclick="proceedToPasswordSecurityStep()"
                    style="width: 100%; padding: 10px 16px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #1e3a8a !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-shield-halved" style="color: #1e3a8a !important;"></i> Continue to Security Check
                </button>
            </div>

            <!-- Footer Actions for Step 2 -->
            <div id="chg-pwd-footer-step-2" style="display: none; flex-direction: column; gap: 6px;">
                <button type="button" id="btn-submit-change-password" onclick="executePasswordChange()" disabled
                    style="width: 100%; padding: 10px 16px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #1e3a8a !important; font-size: 13.5px; font-weight: 900; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 7px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; opacity: 0.5; transition: all 0.2s ease;"
                    onpointerdown="if(!this.disabled) this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-lock" style="color: #1e3a8a !important;"></i> Confirm &amp; Update Password
                </button>
                <button type="button" onclick="backToPasswordStep1()"
                    style="width: 100%; padding: 6px 12px; border: none !important; outline: none !important; background: transparent !important; color: #ffffff !important; font-size: 11.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px; text-decoration: underline;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Edit Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Two-Factor Authentication Modal -->
<div id="two-factor-modal" class="profile-modal-overlay" onclick="if(event.target===this) close2FAModal();">
    <div class="profile-modal-card"
        style="background: #ffffff !important; border: none !important; outline: none !important; border-radius: 22px; padding: 0 !important; max-width: 335px; width: 88%; max-height: 86vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.45); color: #0f172a !important; box-sizing: border-box; text-align: center; position: relative;">
        
        <!-- Modal Top Header Banner (Royal Blue) with Exit Button on Right -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 10px 14px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 7px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-shield-halved" style="color: #00f2fe; font-size: 13px;"></i> Two-Factor Security
                </h4>
                <p style="margin: 1px 0 0 0; font-size: 10.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Account Protection
                </p>
            </div>
            <button type="button" onclick="close2FAModal()" aria-label="Close"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 10px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 13px;"></i>
            </button>
        </div>

        <!-- Middle Body Area (Pure White Background) -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 16px 14px 12px 14px; text-align: center; color: #0f172a !important; box-sizing: border-box;">
            <div id="settings-2fa-icon"
                style="width: 44px; height: 44px; border-radius: 50%; background: #eff6ff !important; color: #1e3a8a !important; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; box-shadow: 0 3px 10px rgba(30, 58, 138, 0.1); border: 1.5px solid #dbeafe;">
                <i class="fa-solid fa-shield-halved" style="font-size: 20px; color: #1e3a8a;"></i>
            </div>

            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; letter-spacing: -0.2px;">Two-Factor Authentication</h3>

            <!-- View 1: When 2FA is Active -->
            <div id="2fa-view-active" style="display: none;">
                <p style="font-size: 11.5px; color: #64748b; line-height: 1.4; margin: 0 0 10px 0;">
                    Your account is protected with email verification codes. Device logins require OTP verification.
                </p>
                <div
                    style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; padding: 10px; text-align: left;">
                    <div
                        style="display: flex; align-items: center; gap: 6px; color: #16a34a; font-weight: 800; font-size: 11.5px; margin-bottom: 2px;">
                        <i class="fa-solid fa-circle-check"></i> Security Status: Active
                    </div>
                    <div style="font-size: 10.5px; color: #475569; font-weight: 600;">
                        Verification Channel: Registered Email Address
                    </div>
                </div>
            </div>

            <!-- View 2: When 2FA is Disabled (Initial state) -->
            <div id="2fa-view-disabled" style="display: block;">
                <p style="font-size: 11.5px; color: #64748b; line-height: 1.4; margin: 0 0 10px 0;">
                    Add an extra layer of security to your tourist account. When logging in, you will be prompted for a 6-digit security code sent to your email.
                </p>
                <div
                    style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 9px 11px; text-align: left; font-size: 11px; color: #b45309; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 13px;"></i>
                    <span>2FA is currently disabled for your account.</span>
                </div>
            </div>

            <!-- View 3: Awaiting OTP Verification -->
            <div id="2fa-view-verify" style="display: none;">
                <p style="font-size: 11.5px; color: #64748b; line-height: 1.4; margin: 0 0 8px 0;">
                    Enter the 6-digit verification code sent to your email to confirm activation.
                </p>
                <div
                    style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 7px 10px; margin-bottom: 10px; font-size: 10.5px; color: #0284c7; font-weight: 700; text-align: left; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-paper-plane" style="font-size: 10px;"></i>
                    <span id="settings-2fa-verify-msg" style="word-break: break-all;">A 6-digit code has been sent to your email.</span>
                </div>
                <form id="settings-2fa-form" onsubmit="handleVerify2FA(event); return false;">
                    <div id="settings-2fa-boxes" style="display: flex; justify-content: center; gap: 5px; margin-bottom: 10px;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleSettings2FAOtpInput(this, 0)" onkeydown="handleSettings2FAOtpKeydown(this, event, 0)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 1)" onkeydown="handleSettings2FAOtpKeydown(this, event, 1)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 2)" onkeydown="handleSettings2FAOtpKeydown(this, event, 2)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 3)" onkeydown="handleSettings2FAOtpKeydown(this, event, 3)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 4)" onkeydown="handleSettings2FAOtpKeydown(this, event, 4)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                        <input type="text" class="settings-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleSettings2FAOtpInput(this, 5)" onkeydown="handleSettings2FAOtpKeydown(this, event, 5)" onpaste="handleSettings2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 3px; font-size: 11px; color: #64748b; font-weight: 600;">
                        Didn't get code? 
                        <span id="settings-2fa-resend-btn" onclick="handleInitiate2FA()" style="color: #0284c7; font-weight: 800; cursor: pointer; text-decoration: underline;">Resend</span>
                        <span id="settings-2fa-timer" style="display: none; color: #94a3b8; font-weight: 700;"></span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner (Royal Blue) for Action Buttons -->
        <div id="two-factor-modal-footer"
            style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 10px 14px; border: none !important; outline: none !important; display: flex; flex-direction: column; gap: 6px; box-sizing: border-box;">
            
            <!-- Footer Action 1: When 2FA is Disabled (Enable 2FA Security Button) -->
            <div id="2fa-footer-disabled" style="display: block;">
                <button type="button" id="btn-settings-enable-2fa" onclick="handleInitiate2FA()"
                    style="width: 100%; padding: 10px 16px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #1e3a8a !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-lock" style="color: #1e3a8a !important;"></i> Enable 2FA Security
                </button>
            </div>

            <!-- Footer Action 2: When 2FA is Awaiting Verification (Verify & Activate 2FA Button) -->
            <div id="2fa-footer-verify" style="display: none;">
                <button type="button" id="btn-settings-verify-2fa" onclick="handleVerify2FA(event)"
                    style="width: 100%; padding: 10px 16px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #16a34a !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-circle-check" style="color: #16a34a !important;"></i> Verify &amp; Activate 2FA
                </button>
            </div>

            <!-- Footer Action 3: When 2FA is Active (Disable 2FA Security Button) -->
            <div id="2fa-footer-active" style="display: none;">
                <button type="button" onclick="handleDisable2FA()"
                    style="width: 100%; padding: 9px 14px; border: 1.5px solid #fecdd3 !important; outline: none !important; border-radius: 100px; background: #fff1f2 !important; color: #e11d48 !important; font-size: 12.5px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-shield-xmark" style="color: #e11d48 !important;"></i> Disable 2FA Security
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Modal 1: Confirm Deactivate 2FA -->
<div id="modal-2fa-deactivate-confirm" class="profile-modal-overlay" onclick="if(event.target===this) close2FADeactivateConfirmModal();">
    <div class="profile-modal-card"
        style="background: #ffffff !important; border: none !important; outline: none !important; border-radius: 22px; padding: 0 !important; max-width: 335px; width: 88%; max-height: 86vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.45); color: #0f172a !important; box-sizing: border-box; text-align: center; position: relative;">
        
        <!-- Modal Top Header Banner (Royal Blue) -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 10px 14px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 7px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-shield-halved" style="color: #00f2fe; font-size: 13px;"></i> Deactivate 2FA
                </h4>
                <p style="margin: 1px 0 0 0; font-size: 10.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Security Confirmation
                </p>
            </div>
            <button type="button" onclick="close2FADeactivateConfirmModal()" aria-label="Close"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 10px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 13px;"></i>
            </button>
        </div>

        <!-- Middle Body Area -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 18px 14px 14px 14px; text-align: center; color: #0f172a !important; box-sizing: border-box;">
            <div style="width: 46px; height: 46px; border-radius: 50%; background: #fff1f2 !important; color: #e11d48 !important; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; box-shadow: 0 3px 10px rgba(225, 29, 72, 0.12); border: 1.5px solid #fecdd3;">
                <i class="fa-solid fa-shield-xmark" style="font-size: 22px; color: #e11d48;"></i>
            </div>

            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; letter-spacing: -0.2px;">
                Are you sure you want to deactivate 2FA?
            </h3>
            
            <p style="font-size: 11.5px; color: #64748b; line-height: 1.45; margin: 0 0 12px 0;">
                Disabling Two-Factor Authentication reduces your account security level. Future logins will only require your password.
            </p>

            <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 10px 12px; text-align: left; display: flex; gap: 8px; align-items: flex-start;">
                <i class="fa-solid fa-shield-halved" style="color: #d97706; font-size: 13px; margin-top: 2px; flex-shrink: 0;"></i>
                <div style="font-size: 10.5px; color: #92400e; font-weight: 600; line-height: 1.35;">
                    For your protection, an identity verification code will be sent to your Gmail before 2FA is turned off.
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 10px 14px; border: none !important; outline: none !important; display: flex; gap: 8px; box-sizing: border-box;">
            <button type="button" onclick="close2FADeactivateConfirmModal()"
                style="flex: 1; padding: 10px 12px; border: 1.5px solid rgba(255, 255, 255, 0.35) !important; outline: none !important; border-radius: 100px; background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; font-size: 12.5px; font-weight: 800; cursor: pointer; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                Cancel
            </button>
            <button type="button" id="btn-proceed-deactivate-2fa" onclick="proceedTo2FADeactivationVerification()"
                style="flex: 1.25; padding: 10px 14px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #e11d48 !important; font-size: 13px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-arrow-right"></i> Yes, Continue
            </button>
        </div>

    </div>
</div>

<!-- Modal 2: "Let us know it's you" (Gmail OTP verification) -->
<div id="modal-2fa-deactivate-verify" class="profile-modal-overlay" onclick="if(event.target===this) close2FADeactivateVerifyModal();">
    <div class="profile-modal-card"
        style="background: #ffffff !important; border: none !important; outline: none !important; border-radius: 22px; padding: 0 !important; max-width: 335px; width: 88%; max-height: 86vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.45); color: #0f172a !important; box-sizing: border-box; text-align: center; position: relative;">
        
        <!-- Header -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 10px 14px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 7px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-user-shield" style="color: #00f2fe; font-size: 13px;"></i> Security Verification
                </h4>
                <p style="margin: 1px 0 0 0; font-size: 10.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Two-Factor Deactivation
                </p>
            </div>
            <button type="button" onclick="close2FADeactivateVerifyModal()" aria-label="Close"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 10px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 13px;"></i>
            </button>
        </div>

        <!-- Middle Body Area -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 16px 14px 12px 14px; text-align: center; color: #0f172a !important; box-sizing: border-box;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #eff6ff !important; color: #1e3a8a !important; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; box-shadow: 0 3px 10px rgba(30, 58, 138, 0.1); border: 1.5px solid #dbeafe;">
                <i class="fa-solid fa-key" style="font-size: 20px; color: #1e3a8a;"></i>
            </div>

            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; letter-spacing: -0.2px;">
                Let us know it's you
            </h3>
            <p style="font-size: 11.5px; color: #64748b; line-height: 1.4; margin: 0 0 10px 0;">
                Enter the 6-digit security code sent to your Gmail to authorize deactivating 2FA.
            </p>

            <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 7px 10px; margin-bottom: 10px; font-size: 10.5px; color: #0284c7; font-weight: 700; text-align: left; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-paper-plane" style="font-size: 10px;"></i>
                <span id="deactivate-2fa-email-msg" style="word-break: break-all;">A 6-digit code has been sent to your email.</span>
            </div>

            <form id="deactivate-2fa-form" onsubmit="handleConfirmDeactivate2FA(event); return false;">
                <div id="deactivate-2fa-boxes" style="display: flex; justify-content: center; gap: 5px; margin-bottom: 10px;">
                    <input type="text" class="deactivate-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleDeactivate2FAOtpInput(this, 0)" onkeydown="handleDeactivate2FAOtpKeydown(this, event, 0)" onpaste="handleDeactivate2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                    <input type="text" class="deactivate-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleDeactivate2FAOtpInput(this, 1)" onkeydown="handleDeactivate2FAOtpKeydown(this, event, 1)" onpaste="handleDeactivate2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                    <input type="text" class="deactivate-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleDeactivate2FAOtpInput(this, 2)" onkeydown="handleDeactivate2FAOtpKeydown(this, event, 2)" onpaste="handleDeactivate2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                    <input type="text" class="deactivate-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleDeactivate2FAOtpInput(this, 3)" onkeydown="handleDeactivate2FAOtpKeydown(this, event, 3)" onpaste="handleDeactivate2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                    <input type="text" class="deactivate-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleDeactivate2FAOtpInput(this, 4)" onkeydown="handleDeactivate2FAOtpKeydown(this, event, 4)" onpaste="handleDeactivate2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                    <input type="text" class="deactivate-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleDeactivate2FAOtpInput(this, 5)" onkeydown="handleDeactivate2FAOtpKeydown(this, event, 5)" onpaste="handleDeactivate2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 36px; height: 40px; text-align: center; font-size: 17px; font-weight: 800; border-radius: 9px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 3px; font-size: 11px; color: #64748b; font-weight: 600;">
                    Didn't get code? 
                    <span id="deactivate-2fa-resend-btn" onclick="proceedTo2FADeactivationVerification(true)" style="color: #0284c7; font-weight: 800; cursor: pointer; text-decoration: underline;">Resend</span>
                    <span id="deactivate-2fa-timer" style="display: none; color: #94a3b8; font-weight: 700;"></span>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 10px 14px; border: none !important; outline: none !important; display: flex; flex-direction: column; gap: 6px; box-sizing: border-box;">
            <button type="button" id="btn-settings-confirm-deactivate-2fa" onclick="handleConfirmDeactivate2FA(event)"
                style="width: 100%; padding: 10px 16px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #e11d48 !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-shield-xmark" style="color: #e11d48 !important;"></i> Confirm &amp; Deactivate 2FA
            </button>
            <button type="button" onclick="close2FADeactivateVerifyModal()"
                style="width: 100%; padding: 5px 12px; border: none !important; outline: none !important; background: transparent !important; color: #ffffff !important; font-size: 11.5px; font-weight: 700; cursor: pointer; text-decoration: underline;">
                Cancel
            </button>
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

        const getSettingsToken = () => localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
        const getSettingsBackendUrl = () => (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');

        function getStored2FAState() {
            const rawFlag = localStorage.getItem('intan_elyu_2fa_active');
            if (rawFlag === 'true') return true;
            if (rawFlag === 'false') return false;

            try {
                const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                if (typeof u.two_factor_enabled !== 'undefined') {
                    return Boolean(u.two_factor_enabled);
                }
            } catch (e) { }
            return false;
        }

        function sync2FAStateGlobally(isActive) {
            const val = Boolean(isActive);
            is2FAActive = val;
            localStorage.setItem('intan_elyu_2fa_active', val ? 'true' : 'false');

            ['auth_user', 'auth_user_backup', 'intan_elyu_user'].forEach(key => {
                try {
                    const raw = localStorage.getItem(key);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        parsed.two_factor_enabled = val;
                        localStorage.setItem(key, JSON.stringify(parsed));
                    }
                } catch (e) { }
            });

            if (window.AppStorage) {
                try {
                    const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    window.AppStorage.setItem('auth_user', u);
                } catch (e) { }
            }

            try {
                delete window._viewHtmlCache?.['settings'];
                for (let i = localStorage.length - 1; i >= 0; i--) {
                    const k = localStorage.key(i);
                    if (k && k.startsWith('elyu_view_cache_') && k.endsWith('_settings')) {
                        localStorage.removeItem(k);
                    }
                }
            } catch (e) { }

            window.dispatchEvent(new CustomEvent('twoFactorStateChanged', { detail: { enabled: val } }));
            update2FAState(val);
        }

        // Initialize 2FA state from storage immediately
        let is2FAActive = getStored2FAState();
        update2FAState(is2FAActive);

        window.addEventListener('twoFactorStateChanged', function (e) {
            if (e && e.detail && typeof e.detail.enabled !== 'undefined') {
                update2FAState(Boolean(e.detail.enabled));
            }
        });

        // Revalidate 2FA status from backend without discarding active client state if offline
        (async function fetch2FAStatus() {
            try {
                const token = getSettingsToken();
                if (token && token !== 'guest_token' && !token.startsWith('offline_')) {
                    const backendUrl = getSettingsBackendUrl();
                    const res = await fetch(backendUrl + '/api/tourist/profile', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.user && typeof data.user.two_factor_enabled !== 'undefined') {
                            sync2FAStateGlobally(Boolean(data.user.two_factor_enabled));
                        }
                    }
                }
            } catch (e) { }
        })();

        let _activePasswordSecurityCode = '';

        window.openChangePasswordModal = function () {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
            const isGuest = (typeof window.isGuestUser === 'function' && window.isGuestUser()) ||
                !token ||
                token === 'guest_token' ||
                token.startsWith('guest_') ||
                localStorage.getItem('intan_elyu_guest') === 'true';

            if (isGuest) {
                if (typeof window.promptGuestLogin === 'function') {
                    window.promptGuestLogin('change your account password');
                } else if (typeof showToast === 'function') {
                    showToast('Guest users do not have an account password. Please sign in.', 'info');
                } else {
                    navigateTo('auth');
                }
                return;
            }

            // Reset back to Step 1
            const step1 = document.getElementById('chg-pwd-step-1');
            const step2 = document.getElementById('chg-pwd-step-2');
            const footer1 = document.getElementById('chg-pwd-footer-step-1');
            const footer2 = document.getElementById('chg-pwd-footer-step-2');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
            if (footer1) footer1.style.display = 'block';
            if (footer2) footer2.style.display = 'none';

            // Reset form inputs & strength container
            const form = document.getElementById('change-password-form');
            if (form) form.reset();
            const strengthContainer = document.getElementById('change-pwd-strength-container');
            if (strengthContainer) strengthContainer.style.display = 'none';

            // Populate current account name in security view
            try {
                const user = JSON.parse(localStorage.getItem('auth_user') || localStorage.getItem('intan_elyu_user') || '{}');
                const acctEl = document.getElementById('chg-pwd-account-name');
                if (acctEl) {
                    acctEl.textContent = user.name || user.email || 'Tourist Account';
                }
            } catch (e) { }

            const modal = document.getElementById('change-password-modal');
            if (modal) modal.classList.add('active');
        };

        window.closeChangePasswordModal = function () {
            const modal = document.getElementById('change-password-modal');
            if (modal) modal.classList.remove('active');
        };

        window.proceedToPasswordSecurityStep = function () {
            const currPass = document.getElementById('curr-password')?.value || '';
            const newPass = document.getElementById('new-password')?.value || '';
            const confPass = document.getElementById('conf-password')?.value || '';

            if (!currPass || !newPass || !confPass) {
                if (typeof showToast === 'function') showToast('Please fill in all password fields.', 'warning');
                return;
            }
            if (newPass.length < 8) {
                if (typeof showToast === 'function') showToast('New password must be at least 8 characters.', 'warning');
                return;
            }
            if (!/[A-Za-z]/.test(newPass) || !/\d/.test(newPass)) {
                if (typeof showToast === 'function') showToast('Password must contain both letters and numbers.', 'warning');
                return;
            }
            if (currPass === newPass) {
                if (typeof showToast === 'function') showToast('New password must be different from current password.', 'warning');
                return;
            }
            if (newPass !== confPass) {
                if (typeof showToast === 'function') showToast('New passwords do not match.', 'warning');
                return;
            }

            // Generate a fresh 4-digit security authorization code
            _activePasswordSecurityCode = Math.floor(1000 + Math.random() * 9000).toString();
            const codeDisplay = document.getElementById('chg-pwd-security-code-display');
            if (codeDisplay) codeDisplay.textContent = _activePasswordSecurityCode;

            const codeInput = document.getElementById('chg-pwd-security-code-input');
            if (codeInput) {
                codeInput.value = '';
                codeInput.style.borderColor = '#cbd5e1';
            }

            const submitBtn = document.getElementById('btn-submit-change-password');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }

            // Transition from Step 1 to Step 2
            const step1 = document.getElementById('chg-pwd-step-1');
            const step2 = document.getElementById('chg-pwd-step-2');
            const footer1 = document.getElementById('chg-pwd-footer-step-1');
            const footer2 = document.getElementById('chg-pwd-footer-step-2');
            if (step1) step1.style.display = 'none';
            if (step2) step2.style.display = 'block';
            if (footer1) footer1.style.display = 'none';
            if (footer2) footer2.style.display = 'flex';

            setTimeout(() => {
                if (codeInput) codeInput.focus();
            }, 100);
        };

        window.backToPasswordStep1 = function () {
            const step1 = document.getElementById('chg-pwd-step-1');
            const step2 = document.getElementById('chg-pwd-step-2');
            const footer1 = document.getElementById('chg-pwd-footer-step-1');
            const footer2 = document.getElementById('chg-pwd-footer-step-2');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
            if (footer1) footer1.style.display = 'block';
            if (footer2) footer2.style.display = 'none';
        };

        window.validateSecurityCodeInput = function () {
            const input = document.getElementById('chg-pwd-security-code-input');
            const submitBtn = document.getElementById('btn-submit-change-password');
            if (!input || !submitBtn) return;

            const val = (input.value || '').trim();
            if (val === _activePasswordSecurityCode && val.length === 4) {
                input.style.borderColor = '#16a34a';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                input.style.borderColor = val.length === 4 ? '#ef4444' : '#cbd5e1';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
        };

        window.togglePasswordVisibility = function (inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn ? btn.querySelector('i') : null;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        };

        window.open2FAModal = function () {
            const isVerifying = document.getElementById('2fa-view-verify') && document.getElementById('2fa-view-verify').style.display === 'block';
            if (!isVerifying) {
                update2FAState(getStored2FAState());
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
                const token = getSettingsToken();
                const backendUrl = getSettingsBackendUrl();
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
                const token = getSettingsToken();
                const backendUrl = getSettingsBackendUrl();
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
                    sync2FAStateGlobally(true);
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

        window.open2FADeactivateConfirmModal = function () {
            const m = document.getElementById('modal-2fa-deactivate-confirm');
            if (m) m.classList.add('active');
        };

        window.close2FADeactivateConfirmModal = function () {
            const m = document.getElementById('modal-2fa-deactivate-confirm');
            if (m) m.classList.remove('active');
        };

        window.open2FADeactivateVerifyModal = function () {
            const m = document.getElementById('modal-2fa-deactivate-verify');
            if (m) m.classList.add('active');
        };

        window.close2FADeactivateVerifyModal = function () {
            const m = document.getElementById('modal-2fa-deactivate-verify');
            if (m) m.classList.remove('active');
        };

        window.handleDisable2FA = function () {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required to update 2FA settings.', 'warning');
                return;
            }
            // Close main 2FA modal first
            if (typeof window.close2FAModal === 'function') {
                window.close2FAModal();
            }
            open2FADeactivateConfirmModal();
        };

        window.proceedTo2FADeactivationVerification = async function (isResend = false) {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required.', 'warning');
                return;
            }

            const btn = document.getElementById('btn-proceed-deactivate-2fa');
            const resendBtn = document.getElementById('deactivate-2fa-resend-btn');
            const timerEl = document.getElementById('deactivate-2fa-timer');

            if (!isResend && btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending Code...';
                btn.disabled = true;
            }
            if (resendBtn) resendBtn.style.pointerEvents = 'none';

            try {
                const token = getSettingsToken();
                const backendUrl = getSettingsBackendUrl();
                const res = await fetch(backendUrl + '/api/tourist/2fa/request-disable', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Failed to request security verification code.');

                close2FADeactivateConfirmModal();
                open2FADeactivateVerifyModal();

                const emailMsg = document.getElementById('deactivate-2fa-email-msg');
                const targetEmail = data.email || (function () {
                    try {
                        return JSON.parse(localStorage.getItem('auth_user') || '{}').email;
                    } catch (e) { return ''; }
                })() || 'your email';

                if (emailMsg) {
                    emailMsg.textContent = `A 6-digit code has been sent to ${targetEmail}.`;
                }

                const boxes = document.querySelectorAll('.deactivate-2fa-otp-box');
                boxes.forEach(b => b.value = '');
                if (boxes[0]) setTimeout(() => boxes[0].focus(), 150);

                if (typeof showToast === 'function') showToast('Security code sent to your Gmail!', 'success');

                // 60s cooldown countdown
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
            } catch (err) {
                console.error('Deactivation Request Error:', err);
                if (typeof showToast === 'function') showToast(err.message || 'Failed to send security code.', 'error');
                if (resendBtn) resendBtn.style.pointerEvents = 'auto';
            } finally {
                if (!isResend && btn) {
                    btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Yes, Continue';
                    btn.disabled = false;
                }
            }
        };

        window.handleDeactivate2FAOtpInput = function (el, index) {
            el.value = el.value.replace(/[^0-9]/g, '');
            const boxes = document.querySelectorAll('.deactivate-2fa-otp-box');
            if (el.value && index < boxes.length - 1) {
                boxes[index + 1].focus();
            }
            const fullCode = Array.from(boxes).map(b => b.value).join('');
            if (fullCode.length === 6) {
                window.handleConfirmDeactivate2FA();
            }
        };

        window.handleDeactivate2FAOtpKeydown = function (el, e, index) {
            const boxes = document.querySelectorAll('.deactivate-2fa-otp-box');
            if (e.key === 'Backspace' && !el.value && index > 0) {
                boxes[index - 1].focus();
            }
        };

        window.handleDeactivate2FAOtpPaste = function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            const boxes = document.querySelectorAll('.deactivate-2fa-otp-box');
            for (let i = 0; i < boxes.length && i < pasted.length; i++) {
                boxes[i].value = pasted[i];
            }
            if (pasted.length >= 6) {
                if (boxes[5]) boxes[5].focus();
                window.handleConfirmDeactivate2FA();
            }
        };

        window.handleConfirmDeactivate2FA = async function (e) {
            if (e) e.preventDefault();
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required.', 'warning');
                return;
            }

            const boxes = document.querySelectorAll('.deactivate-2fa-otp-box');
            const code = Array.from(boxes).map(b => b.value.trim()).join('');
            if (!code || code.length < 6) {
                if (typeof showToast === 'function') showToast('Please enter all 6 digits of the security code.', 'warning');
                return;
            }

            const btn = document.getElementById('btn-settings-confirm-deactivate-2fa');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';
                btn.disabled = true;
            }

            try {
                const token = getSettingsToken();
                const backendUrl = getSettingsBackendUrl();
                const res = await fetch(backendUrl + '/api/tourist/2fa/confirm-disable', {
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
                    sync2FAStateGlobally(false);
                    close2FADeactivateVerifyModal();
                    if (typeof showToast === 'function') showToast('Two-Factor Authentication deactivated.', 'info');
                } else {
                    throw new Error(data.message || 'Invalid 6-digit security verification code.');
                }
            } catch (err) {
                console.error('Confirm Deactivation Error:', err);
                if (typeof showToast === 'function') showToast(err.message || 'Verification failed. Please try again.', 'error');
                const boxContainer = document.getElementById('deactivate-2fa-boxes');
                if (boxContainer) {
                    boxContainer.classList.remove('shake-attention');
                    void boxContainer.offsetWidth;
                    boxContainer.classList.add('shake-attention');
                }
                boxes.forEach(b => b.value = '');
                if (boxes[0]) boxes[0].focus();
            } finally {
                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-shield-xmark" style="color: #e11d48 !important; margin-right: 6px;"></i> Confirm &amp; Deactivate 2FA';
                    btn.disabled = false;
                }
            }
        };

        function update2FAState(isActive) {
            is2FAActive = Boolean(isActive);
            const pill = document.getElementById('2fa-status-pill');
            const viewActive = document.getElementById('2fa-view-active');
            const viewDisabled = document.getElementById('2fa-view-disabled');
            const viewVerify = document.getElementById('2fa-view-verify');

            if (pill) {
                if (is2FAActive) {
                    pill.textContent = 'Active';
                    pill.style.background = '#dcfce7';
                    pill.style.color = '#15803d';
                    pill.style.border = '1.5px solid #16a34a';
                    pill.style.fontWeight = '800';
                } else {
                    pill.textContent = 'Disabled';
                    pill.style.background = '#f1f5f9';
                    pill.style.color = '#475569';
                    pill.style.border = '1.5px solid #94a3b8';
                    pill.style.fontWeight = '800';
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
            const segments = container.querySelectorAll('.pwd-segment');
            if (pwd.length === 0) {
                container.style.display = 'none';
                segments.forEach(seg => {
                    seg.style.setProperty('background', '#e2e8f0', 'important');
                    seg.style.setProperty('box-shadow', 'none', 'important');
                    seg.style.setProperty('border', 'none', 'important');
                });
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

            // Solid vibrant non-glowing bar colors
            const solidColors = ['#e2e8f0', '#ef4444', '#f97316', '#eab308', '#10b981'];
            const activeColor = solidColors[score] || '#e2e8f0';

            segments.forEach((seg, idx) => {
                seg.style.setProperty('box-shadow', 'none', 'important');
                seg.style.setProperty('border', 'none', 'important');
                seg.style.setProperty('border-radius', '999px', 'important');
                seg.style.setProperty('height', '5px', 'important');
                if (idx < score) {
                    seg.style.setProperty('background', activeColor, 'important');
                } else {
                    seg.style.setProperty('background', '#e2e8f0', 'important');
                }
            });

            if (chkLen8) chkLen8.className = 'pwd-chk-item' + (len8 ? ' passed' : '');
            if (chkNum) chkNum.className = 'pwd-chk-item' + (hasNum ? ' passed' : '');
            if (chkCap) chkCap.className = 'pwd-chk-item' + (hasCap ? ' passed' : '');
            if (chkSym) chkSym.className = 'pwd-chk-item' + (hasSym ? ' passed' : '');

            const labels = ['Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            if (label) {
                label.textContent = labels[score] || 'Password Strength';
                label.style.color = solidColors[score] || '#64748b';
            }
        };

        window.executePasswordChange = async function () {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') showToast('Internet connection required to change your account password.', 'warning');
                return;
            }
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
            const isGuest = (typeof window.isGuestUser === 'function' && window.isGuestUser()) ||
                !token ||
                token === 'guest_token' ||
                token.startsWith('guest_') ||
                localStorage.getItem('intan_elyu_guest') === 'true';

            if (isGuest) {
                if (typeof showToast === 'function') showToast('Guest accounts do not have passwords. Please log in or register.', 'info');
                return;
            }

            const codeInput = document.getElementById('chg-pwd-security-code-input');
            const enteredCode = (codeInput?.value || '').trim();
            if (enteredCode !== _activePasswordSecurityCode || enteredCode.length !== 4) {
                if (typeof showToast === 'function') showToast('Please enter the matching 4-digit security code to authorize.', 'warning');
                if (codeInput) codeInput.focus();
                return;
            }

            const currPass = document.getElementById('curr-password')?.value || '';
            const newPass = document.getElementById('new-password')?.value || '';
            const confPass = document.getElementById('conf-password')?.value || '';

            if (!currPass || !newPass || !confPass) {
                if (typeof showToast === 'function') showToast('Please fill in all password fields.', 'warning');
                window.backToPasswordStep1();
                return;
            }

            const submitBtn = document.getElementById('btn-submit-change-password');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '<i class="fa-solid fa-lock" style="color: #1e3a8a !important;"></i> Confirm &amp; Update Password';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.75';
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Authorizing &amp; Updating...';
            }

            try {
                const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
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
                    if (typeof showToast === 'function') showToast(data.message || 'Password updated successfully!', 'success');
                    const form = document.getElementById('change-password-form');
                    if (form) form.reset();
                    const container = document.getElementById('change-pwd-strength-container');
                    if (container) container.style.display = 'none';
                    window.closeChangePasswordModal();
                } else {
                    let errMsg = data.message;
                    if (data.errors && typeof data.errors === 'object') {
                        const firstErr = Object.values(data.errors).flat()[0];
                        if (firstErr) errMsg = firstErr;
                    }
                    if (typeof showToast === 'function') showToast(errMsg || 'Failed to update password.', 'error');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('Network error while updating password.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        };

        window.submitChangePassword = window.executePasswordChange;
    })();
</script>