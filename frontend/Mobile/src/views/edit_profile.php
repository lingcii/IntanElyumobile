<?php
$pageTitle = 'Edit Profile';
$backRoute = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="edit-profile-container has-header animate-slide-up">
    <!-- Offline Status Badge -->
    <div id="edit-profile-offline-badge" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 16px auto; padding: 7px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11.5px; font-weight: 800; text-align: center; width: fit-content;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Edits will save locally and sync when online</span>
    </div>
    <div class="edit-profile-card">
        <!-- Avatar Section -->
        <div class="avatar-upload">
            <div class="avatar-preview" id="avatar-preview">
                <i class="fa-solid fa-user" id="avatar-icon"></i>
                <img id="avatar-img" alt="Avatar" style="display:none;">
            </div>
            <div class="avatar-btn" onclick="window.openImagePickerModal()" title="Change Profile Picture">
                <i class="fa-solid fa-camera"></i>
            </div>
            <input type="file" id="avatar-input" accept="image/*" style="display:none;" onchange="previewAvatar(event)">
        </div>

        <form class="edit-profile-form" onsubmit="saveProfile(event)">
            <!-- Personal Info -->
            <div class="edit-section-title">
                Personal Details
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-name">
                    Full Name
                </label>
                <input class="form-control" id="profile-name" type="text" placeholder="Enter your full name" required
                    autocomplete="name">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-email">
                    Email Address
                </label>
                <input class="form-control" id="profile-email" type="email" placeholder="Email address" required
                    autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-phone">
                    Phone / Mobile Number
                </label>
                <input class="form-control" id="profile-phone" type="tel" inputmode="tel" placeholder="+63 9XX XXX XXXX"
                    autocomplete="tel" maxlength="16">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-location">
                    Hometown / Origin
                </label>
                <select class="form-control" id="profile-location"
                    style="cursor: pointer; background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: none !important; outline: none !important; border-radius: 16px; padding: 13px 14px; font-size: 14px; font-weight: 600; width: 100%; box-sizing: border-box;"
                    onchange="window.handleHometownChange(this)">
                    <option value="" style="background: #1e3a8a; color: #ffffff;">Select hometown / origin</option>
                    
                    <option value="Agoo" style="background: #1e3a8a; color: #ffffff;">Agoo</option>
                    <option value="Aringay" style="background: #1e3a8a; color: #ffffff;">Aringay</option>
                    <option value="Bacnotan" style="background: #1e3a8a; color: #ffffff;">Bacnotan</option>
                    <option value="Bagulin" style="background: #1e3a8a; color: #ffffff;">Bagulin</option>
                    <option value="Balaoan" style="background: #1e3a8a; color: #ffffff;">Balaoan</option>
                    <option value="Bangar" style="background: #1e3a8a; color: #ffffff;">Bangar</option>
                    <option value="Bauang" style="background: #1e3a8a; color: #ffffff;">Bauang</option>
                    <option value="Burgos" style="background: #1e3a8a; color: #ffffff;">Burgos</option>
                    <option value="Caba" style="background: #1e3a8a; color: #ffffff;">Caba</option>
                    <option value="Luna" style="background: #1e3a8a; color: #ffffff;">Luna</option>
                    <option value="Naguilian" style="background: #1e3a8a; color: #ffffff;">Naguilian</option>
                    <option value="Pugo" style="background: #1e3a8a; color: #ffffff;">Pugo</option>
                    <option value="Rosario" style="background: #1e3a8a; color: #ffffff;">Rosario</option>
                    <option value="San Fernando City" style="background: #1e3a8a; color: #ffffff;">San Fernando City</option>
                    <option value="San Gabriel" style="background: #1e3a8a; color: #ffffff;">San Gabriel</option>
                    <option value="San Juan" style="background: #1e3a8a; color: #ffffff;">San Juan</option>
                    <option value="Santo Tomas" style="background: #1e3a8a; color: #ffffff;">Santo Tomas</option>
                    <option value="Santol" style="background: #1e3a8a; color: #ffffff;">Santol</option>
                    <option value="Sudipen" style="background: #1e3a8a; color: #ffffff;">Sudipen</option>
                    <option value="Tubao" style="background: #1e3a8a; color: #ffffff;">Tubao</option>
                    
                    <optgroup label="🇵🇭 Major Cities & Nearby Provinces" style="background: #1e3a8a; color: #38bdf8; font-weight: 800;">
                        <option value="Metro Manila (NCR)" style="background: #1e3a8a; color: #ffffff;">Metro Manila (NCR)</option>
                        <option value="Baguio City / Benguet" style="background: #1e3a8a; color: #ffffff;">Baguio City / Benguet</option>
                        <option value="Pangasinan" style="background: #1e3a8a; color: #ffffff;">Pangasinan</option>
                        <option value="Ilocos Sur" style="background: #1e3a8a; color: #ffffff;">Ilocos Sur</option>
                        <option value="Ilocos Norte" style="background: #1e3a8a; color: #ffffff;">Ilocos Norte</option>
                        <option value="Pampanga / Central Luzon" style="background: #1e3a8a; color: #ffffff;">Pampanga / Central Luzon</option>
                        <option value="Bulacan" style="background: #1e3a8a; color: #ffffff;">Bulacan</option>
                        <option value="Tarlac" style="background: #1e3a8a; color: #ffffff;">Tarlac</option>
                        <option value="Bataan / Zambales" style="background: #1e3a8a; color: #ffffff;">Bataan / Zambales</option>
                        <option value="Cavite / Laguna / Batangas" style="background: #1e3a8a; color: #ffffff;">Cavite / Laguna / Batangas (CALABARZON)</option>
                        <option value="Rizal / Quezon" style="background: #1e3a8a; color: #ffffff;">Rizal / Quezon</option>
                        <option value="Cebu / Central Visayas" style="background: #1e3a8a; color: #ffffff;">Cebu / Central Visayas</option>
                        <option value="Iloilo / Western Visayas" style="background: #1e3a8a; color: #ffffff;">Iloilo / Western Visayas</option>
                        <option value="Davao Region / Mindanao" style="background: #1e3a8a; color: #ffffff;">Davao Region / Mindanao</option>
                        <option value="Cagayan de Oro / Northern Mindanao" style="background: #1e3a8a; color: #ffffff;">Cagayan de Oro / Northern Mindanao</option>
                    </optgroup>

                    <optgroup label="🌍 International & Other" style="background: #1e3a8a; color: #38bdf8; font-weight: 800;">
                        <option value="International / Overseas" style="background: #1e3a8a; color: #ffffff;">International / Overseas</option>
                        <option value="Other" style="background: #1e3a8a; color: #ffffff;">Other (Specify below...)</option>
                    </optgroup>
                </select>
                <div id="hometown-custom-container" style="display:none; margin-top:8px;">
                    <input class="form-control" id="profile-location-custom" type="text"
                        placeholder="Type your hometown/origin here..."
                        style="width: 100%; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label" for="profile-age">
                        Age <span style="font-size:11px; font-weight:600; color:rgba(255,255,255,0.7);">(2 digits max)</span>
                    </label>
                    <input class="form-control" id="profile-age" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="2"
                        placeholder="e.g. 24" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2);">
                </div>

                <div class="form-group">
                    <label class="form-label" for="profile-gender">
                        Gender
                    </label>
                    <select class="form-control" id="profile-gender"
                        style="cursor: pointer; background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: none !important; outline: none !important; border-radius: 16px; padding: 13px 14px; font-size: 14px; font-weight: 600; width: 100%; box-sizing: border-box;">
                        <option value="" style="background: #1e3a8a; color: #ffffff;">Select gender</option>
                        <option value="Male" style="background: #1e3a8a; color: #ffffff;">Male</option>
                        <option value="Female" style="background: #1e3a8a; color: #ffffff;">Female</option>
                    </select>
                </div>
            </div>

            <!-- Bio / Motto -->
            <div class="edit-section-title">
                Travel Bio & Motto
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-bio">
                    Bio / Traveler Statement
                </label>
                <textarea class="form-control" id="profile-bio"
                    placeholder="Share a brief motto or your passion for exploring La Union..."></textarea>
            </div>

            <!-- Travel Preferences -->
            <div class="edit-section-title">
                Travel Preferences
            </div>

            <div class="form-group">
                <label class="form-label">
                    Preferred Spot Categories
                </label>
                <div class="chips-container" id="preferences-chips">
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Surfing & Beach">🏄‍♂️ Surfing & Beach
                    </div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Nature & Falls">🏔️ Nature & Falls
                    </div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Heritage & Culture">🏛️ Heritage &
                        Culture</div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Food & Dining">🍲 Food & Dining</div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Sunset & Nightlife">🌅 Sunset &
                        Nightlife</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button class="btn-save-profile" type="submit" id="btn-save">
                    <i class="fa-solid fa-check"></i> <span>Save Changes</span>
                </button>
                <button class="btn-cancel-profile" type="button" onclick="navigateTo('profile')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Image Picker Choice Modal (Top-level full-viewport position) -->
<div id="image-picker-modal" onclick="if(event.target===this) window.closeImagePickerModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:999999; align-items:flex-end; justify-content:center; padding:0; margin:0; box-sizing:border-box;">
    <div
        style="background:linear-gradient(135deg, rgba(30,41,59,0.98) 0%, rgba(15,23,42,1) 100%); border-top:1px solid rgba(56,189,248,0.3); border-radius:28px 28px 0 0; width:100%; max-width:500px; padding:26px 22px; box-shadow:0 -10px 45px rgba(0,0,0,0.8); animation:slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); box-sizing:border-box;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3
                style="margin:0; font-size:17px; font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-image" style="color:#38bdf8; font-size:18px;"></i> Select Profile Photo
            </h3>
            <button type="button" onclick="window.closeImagePickerModal()"
                style="background:rgba(255,255,255,0.08); border:none; color:#94a3b8; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button type="button" onclick="window.selectImageSource('camera')"
                style="width:100%; padding:15px; background:linear-gradient(135deg, rgba(56,189,248,0.18) 0%, rgba(37,99,235,0.22) 100%); border:1px solid rgba(56,189,248,0.35); border-radius:18px; color:#38bdf8; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <i class="fa-solid fa-camera" style="font-size:17px;"></i> Take Photo with Camera
            </button>
            <button type="button" onclick="window.selectImageSource('gallery')"
                style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:18px; color:#f8fafc; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, background 0.15s ease;">
                <i class="fa-solid fa-images" style="font-size:17px; color:#38bdf8;"></i> Choose from Photo Gallery
            </button>
            <button type="button" onclick="window.closeImagePickerModal()"
                style="width:100%; padding:12px; background:transparent; border:none; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; margin-top:4px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Full-screen Photo Preview & Crop Modal (Matching Exact UI from Screenshot) -->
<div id="avatar-crop-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:#ffffff; z-index:9999999; flex-direction:column; justify-content:space-between; box-sizing:border-box; overflow:hidden;">

    <!-- Top Header -->
    <div
        style="height:56px; padding:0 16px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f1f5f9; flex-shrink:0;">
        <button type="button" onclick="window.closeCropPreviewModal()"
            style="background:transparent; border:none; outline:none; font-size:18px; color:#0f172a; padding:8px 12px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <span style="font-size:17px; font-weight:700; color:#0f172a; letter-spacing:-0.2px;">Preview</span>
        <div style="width:38px;"></div> <!-- Spacer to balance header -->
    </div>

    <!-- Cropper Container Card -->
    <div
        style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:12px 16px; box-sizing:border-box; overflow:hidden;">
        <div id="crop-card"
            style="width:100%; max-width:440px; height:100%; max-height:520px; background:#f1f3f5; border-radius:28px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; box-shadow:inset 0 0 0 1px rgba(0,0,0,0.04);">

            <!-- Interactive Viewport -->
            <div id="crop-viewport"
                style="position:absolute; top:0; left:0; width:100%; height:100%; overflow:hidden; touch-action:none; cursor:grab; user-select:none;">
                <img id="crop-source-img" draggable="false"
                    style="position:absolute; left:50%; top:45%; max-width:none; transform-origin:center center; pointer-events:none; user-select:none; will-change:transform;"
                    alt="Crop source">
            </div>

            <!-- SVG Mask Cutout with Crisp White Border Ring -->
            <svg id="crop-svg-mask"
                style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:10;">
                <defs>
                    <mask id="crop-aperture-mask">
                        <rect width="100%" height="100%" fill="white" />
                        <circle id="crop-mask-circle" cx="50%" cy="45%" r="125" fill="black" />
                    </mask>
                </defs>
                <rect width="100%" height="100%" fill="rgba(241, 243, 245, 0.82)" mask="url(#crop-aperture-mask)" />
                <circle id="crop-border-circle" cx="50%" cy="45%" r="125" fill="none" stroke="#ffffff"
                    stroke-width="2.5" />
            </svg>

            <!-- Floating Zoom & Reset Pill (matching screenshot) -->
            <div
                style="position:absolute; bottom:22px; z-index:20; background:#ffffff; border-radius:999px; box-shadow:0 4px 18px rgba(0,0,0,0.12); padding:5px 16px; display:inline-flex; align-items:center; gap:14px;">
                <button type="button" onclick="window.cropZoomStep(-0.15)"
                    style="background:none; border:none; outline:none; font-size:16px; font-weight:700; color:#0f172a; cursor:pointer; padding:6px 8px; display:flex; align-items:center; justify-content:center;"
                    title="Zoom Out">
                    <i class="fa-solid fa-minus"></i>
                </button>
                <button type="button" onclick="window.cropZoomStep(0.15)"
                    style="background:none; border:none; outline:none; font-size:16px; font-weight:700; color:#0f172a; cursor:pointer; padding:6px 8px; display:flex; align-items:center; justify-content:center;"
                    title="Zoom In">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <div style="width:1px; height:18px; background:#e2e8f0;"></div>
                <button type="button" onclick="window.cropResetPosition()"
                    style="background:none; border:none; outline:none; font-size:14px; font-weight:700; color:#0f172a; cursor:pointer; padding:6px 8px;"
                    title="Reset">
                    Reset
                </button>
            </div>

        </div>
    </div>

    <!-- Bottom Actions -->
    <div
        style="padding:14px 20px 24px 20px; display:flex; flex-direction:column; gap:10px; max-width:440px; width:100%; margin:0 auto; box-sizing:border-box; flex-shrink:0;">
        <button type="button" onclick="window.cropSelectAnother()"
            style="width:100%; height:50px; background:#f1f5f9; color:#0f172a; border-radius:25px; border:none; outline:none; font-size:15px; font-weight:700; cursor:pointer; transition:background 0.15s ease;">
            Select another photo
        </button>
        <button type="button" onclick="window.cropConfirm()"
            style="width:100%; height:50px; background:#0f172a; color:#ffffff; border-radius:25px; border:none; outline:none; font-size:15px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(15,23,42,0.25); transition:transform 0.15s ease;">
            Confirm
        </button>
    </div>

</div>

<script>
    (function () {
        // Ensure view opens at the absolute top
        function resetEditProfileScroll() {
            if (document.activeElement && typeof document.activeElement.blur === 'function' && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA')) {
                document.activeElement.blur();
            }
            try {
                window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
            } catch (e) {
                window.scrollTo(0, 0);
            }
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;
            const mc = document.getElementById('main-content');
            if (mc) mc.scrollTop = 0;
            const ac = document.getElementById('app-container');
            if (ac) ac.scrollTop = 0;
            const epc = document.querySelector('.edit-profile-container');
            if (epc) epc.scrollTop = 0;
        }

        resetEditProfileScroll();
        requestAnimationFrame(resetEditProfileScroll);
        setTimeout(resetEditProfileScroll, 50);
        setTimeout(resetEditProfileScroll, 150);
        setTimeout(resetEditProfileScroll, 300);

        const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
        const nameInput = document.getElementById('profile-name');
        const emailInput = document.getElementById('profile-email');
        const phoneInput = document.getElementById('profile-phone');
        const ageInput = document.getElementById('profile-age');
        const genderInput = document.getElementById('profile-gender');
        const bioInput = document.getElementById('profile-bio');
        const img = document.getElementById('avatar-img');
        const icon = document.getElementById('avatar-icon');

        function syncHometownDropdown(locStr) {
            const locSelect = document.getElementById('profile-location');
            const customBox = document.getElementById('hometown-custom-container');
            const customInput = document.getElementById('profile-location-custom');
            if (!locSelect) return;

            if (!locStr || !String(locStr).trim()) {
                locSelect.value = '';
                if (customBox) customBox.style.display = 'none';
                if (customInput) customInput.value = '';
                return;
            }

            const rawTarget = String(locStr).trim();
            const cleanTarget = rawTarget.replace(/,\s*La\s*Union/i, '').trim().toLowerCase();
            const target = rawTarget.toLowerCase();
            let matched = false;

            for (let i = 0; i < locSelect.options.length; i++) {
                const opt = locSelect.options[i];
                if (!opt.value) continue;
                const optVal = opt.value.toLowerCase();
                if (optVal === cleanTarget || optVal === target || optVal.includes(cleanTarget) || cleanTarget.includes(optVal)) {
                    locSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }

            if (!matched && rawTarget) {
                locSelect.value = 'Other';
                if (customBox) customBox.style.display = 'block';
                if (customInput) customInput.value = rawTarget;
            } else {
                if (customBox) customBox.style.display = 'none';
            }
        }

        window.handleHometownChange = function (sel) {
            const customBox = document.getElementById('hometown-custom-container');
            const customInput = document.getElementById('profile-location-custom');
            if (sel && sel.value === 'Other') {
                if (customBox) customBox.style.display = 'block';
                if (customInput) customInput.focus();
            } else {
                if (customBox) customBox.style.display = 'none';
                if (customInput) customInput.value = '';
            }
        };

        // Philippine Mobile Number (+63) Interactive Auto-Formatter
        if (phoneInput) {
            phoneInput.addEventListener('focus', function () {
                if (!this.value || !this.value.trim()) {
                    this.value = '+63 ';
                }
            });

            phoneInput.addEventListener('input', function () {
                let val = this.value;
                if (!val) return;

                // Strip illegal characters
                let cleaned = val.replace(/[^\d+]/g, '');

                if (cleaned.startsWith('09')) {
                    cleaned = '+639' + cleaned.substring(2);
                } else if (cleaned.startsWith('9')) {
                    cleaned = '+639' + cleaned.substring(1);
                } else if (cleaned.startsWith('63')) {
                    cleaned = '+' + cleaned;
                } else if (!cleaned.startsWith('+63')) {
                    cleaned = '+63' + cleaned.replace(/^\+*6*3*/, '');
                }

                const digitsAfter63 = cleaned.replace(/^\+63/, '').replace(/\D/g, '').slice(0, 10);
                let formatted = '+63';
                if (digitsAfter63.length > 0) formatted += ' ' + digitsAfter63.substring(0, 3);
                if (digitsAfter63.length > 3) formatted += ' ' + digitsAfter63.substring(3, 6);
                if (digitsAfter63.length > 6) formatted += ' ' + digitsAfter63.substring(6, 10);

                this.value = formatted;
            });
        }

        // Populate initial user values
        if (nameInput && user.name) nameInput.value = user.name;
        if (emailInput && user.email) emailInput.value = user.email;
        if (phoneInput && user.phone) phoneInput.value = user.phone;
        if (user.home_location) syncHometownDropdown(user.home_location);
        if (ageInput && user.age !== undefined && user.age !== null) ageInput.value = String(user.age).slice(0, 2);
        if (genderInput && user.gender) genderInput.value = user.gender;
        if (bioInput && user.bio) bioInput.value = user.bio;

        function syncPreferencesFromData(u) {
            const chips = document.querySelectorAll('#preferences-chips .chip-item');
            if (!chips.length) return;

            let rawPrefs = null;
            if (u && typeof u.travel_preferences !== 'undefined' && u.travel_preferences !== null) {
                rawPrefs = u.travel_preferences;
            } else {
                try {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    if (typeof stored.travel_preferences !== 'undefined' && stored.travel_preferences !== null) {
                        rawPrefs = stored.travel_preferences;
                    }
                } catch (e) { }
            }

            // Only highlight chips that are explicitly saved in the user's preferences list
            const prefList = (rawPrefs && typeof rawPrefs === 'string' && rawPrefs.trim())
                ? rawPrefs.split(',').map(s => s.replace(/[\u{1F300}-\u{1F9FF}]/gu, '').trim().toLowerCase()).filter(Boolean)
                : [];

            chips.forEach(chip => {
                const val = (chip.getAttribute('data-value') || '').replace(/[\u{1F300}-\u{1F9FF}]/gu, '').trim().toLowerCase();
                const isMatch = prefList.length > 0 && prefList.includes(val);
                if (isMatch) {
                    chip.classList.add('active');
                } else {
                    chip.classList.remove('active');
                }
            });
        }

        // Run initial sync from current user
        syncPreferencesFromData(user);

        // Toggle chip helper
        window.toggleChip = function (el) {
            if (el) el.classList.toggle('active');
        };

        // Immediately fetch fresh profile from API to ensure 100% accuracy from DB
        (async function fetchLatestUserData() {
            const token = localStorage.getItem('intan_elyu_token');
            if (!token) return;
            try {
                const res = await fetch((window.backendUrl || '') + '/api/tourist/profile', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.user) {
                        const u = data.user;
                        if (nameInput && (!nameInput.value || nameInput.value === 'Explorer')) nameInput.value = u.name || '';
                        if (emailInput && (!emailInput.value || emailInput.value.includes('loading'))) emailInput.value = u.email || '';
                        if (phoneInput && u.phone) phoneInput.value = u.phone;
                        if (u.home_location) syncHometownDropdown(u.home_location);
                        if (ageInput && u.age !== undefined && u.age !== null) ageInput.value = String(u.age).slice(0, 2);
                        if (genderInput && u.gender) genderInput.value = u.gender;
                        if (bioInput && u.bio) bioInput.value = u.bio;
                        syncPreferencesFromData(u);

                        const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                        Object.assign(stored, u);
                        localStorage.setItem('auth_user', JSON.stringify(stored));
                    }
                }
            } catch (e) {
                console.warn('Could not fetch fresh user profile in edit_profile:', e);
            }
        })();

        if (user.avatar && img) {
            let avatarUrl = user.avatar;
            if (avatarUrl.includes('localhost:3000') || avatarUrl.includes('127.0.0.1:3000')) {
                avatarUrl = avatarUrl.replace(/http:\/\/(localhost|127\.0\.0\.1):3000/, window.backendUrl || 'http://localhost:8000');
            }
            if (!avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:') && !avatarUrl.startsWith('blob:')) {
                let b = (window.backendUrl || '').replace(/\/+$/, '');
                avatarUrl = b + '/' + avatarUrl.replace(/^\//, '');
            }

            let fallbackAvatar = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + user.avatar.replace(/^\//, '');

            img.onerror = function () {
                if (this.src !== fallbackAvatar) {
                    this.src = fallbackAvatar;
                } else {
                    img.style.display = 'none';
                    if (icon) icon.style.display = 'block';
                }
            };

            img.src = avatarUrl;
            img.style.display = 'block';
            if (icon) icon.style.display = 'none';
        }

        window.selectedAvatarBlob = null;

        window.openImagePickerModal = function () {
            const modal = document.getElementById('image-picker-modal');
            if (modal) modal.style.display = 'flex';
        };

        window.closeImagePickerModal = function () {
            const modal = document.getElementById('image-picker-modal');
            if (modal) modal.style.display = 'none';
        };

        window.selectImageSource = async function (mode) {
            window.closeImagePickerModal();
            const input = document.getElementById('avatar-input');

            // Check if running in Capacitor Native Environment with Camera plugin
            const isCapacitorNative = Boolean(
                window.Capacitor &&
                typeof window.Capacitor.isNativePlatform === 'function' &&
                window.Capacitor.isNativePlatform() &&
                window.Capacitor.Plugins &&
                window.Capacitor.Plugins.Camera
            );

            if (isCapacitorNative) {
                try {
                    const cameraPlugin = window.Capacitor.Plugins.Camera;
                    const image = await cameraPlugin.getPhoto({
                        quality: 90,
                        allowEditing: false,
                        resultType: 'dataUrl',
                        source: mode === 'camera' ? 'CAMERA' : 'PHOTOS'
                    });

                    if (image && image.dataUrl) {
                        window.openCropPreviewModal(image.dataUrl);
                    }
                } catch (err) {
                    console.warn('Capacitor Camera cancel or error:', err);
                }
            } else {
                // Web / standard browser fallback
                if (!input) return;
                if (mode === 'camera') {
                    input.setAttribute('capture', 'environment');
                } else {
                    input.removeAttribute('capture');
                }
                input.click();
            }
        };

        window.previewAvatar = function (event) {
            const file = event.target?.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                if (e.target && e.target.result) {
                    window.openCropPreviewModal(e.target.result);
                }
            };
            reader.readAsDataURL(file);
            event.target.value = '';
        };

        // ─────────────────────────────────────────────────────────────
        // Photo Preview & Interactive Cropper Controller (Matching Design)
        // ─────────────────────────────────────────────────────────────
        const cropState = {
            img: null,
            naturalW: 0,
            naturalH: 0,
            panX: 0,
            panY: 0,
            zoom: 1,
            baseZoom: 1,
            isDragging: false,
            startX: 0,
            startY: 0,
            startPanX: 0,
            startPanY: 0,
            touchDist: 0,
            startZoom: 1,
            radius: 125
        };

        function updateCropTransform() {
            const imgEl = document.getElementById('crop-source-img');
            if (!imgEl) return;
            imgEl.style.transform = `translate(calc(-50% + ${cropState.panX}px), calc(-50% + ${cropState.panY}px)) scale(${cropState.zoom})`;
        }

        window.openCropPreviewModal = function (imageSrc) {
            const modal = document.getElementById('avatar-crop-modal');
            const sourceImg = document.getElementById('crop-source-img');
            const cropCard = document.getElementById('crop-card');
            if (!modal || !sourceImg) return;

            modal.style.display = 'flex';

            sourceImg.onload = function () {
                cropState.img = sourceImg;
                cropState.naturalW = sourceImg.naturalWidth || 400;
                cropState.naturalH = sourceImg.naturalHeight || 400;

                const rect = cropCard ? cropCard.getBoundingClientRect() : { width: 360, height: 460 };
                const cardW = rect.width || 360;
                const r = Math.min(Math.floor(cardW * 0.36), 130);
                cropState.radius = r;

                const maskHole = document.getElementById('crop-mask-circle');
                const borderCircle = document.getElementById('crop-border-circle');
                if (maskHole) maskHole.setAttribute('r', r);
                if (borderCircle) borderCircle.setAttribute('r', r);

                const minSide = Math.min(cropState.naturalW, cropState.naturalH);
                const targetSide = r * 2 * 1.15;
                cropState.baseZoom = targetSide / minSide;
                cropState.zoom = cropState.baseZoom;
                cropState.panX = 0;
                cropState.panY = 0;

                sourceImg.style.width = cropState.naturalW + 'px';
                sourceImg.style.height = cropState.naturalH + 'px';

                updateCropTransform();
            };

            sourceImg.src = imageSrc;
        };

        window.closeCropPreviewModal = function () {
            const modal = document.getElementById('avatar-crop-modal');
            if (modal) modal.style.display = 'none';
        };

        window.cropZoomStep = function (delta) {
            const minZoom = cropState.baseZoom * 0.4;
            const maxZoom = cropState.baseZoom * 4.5;
            cropState.zoom = Math.max(minZoom, Math.min(maxZoom, cropState.zoom + delta));
            updateCropTransform();
        };

        window.cropResetPosition = function () {
            cropState.zoom = cropState.baseZoom;
            cropState.panX = 0;
            cropState.panY = 0;
            updateCropTransform();
        };

        window.cropSelectAnother = function () {
            window.closeCropPreviewModal();
            window.openImagePickerModal();
        };

        window.cropConfirm = function () {
            if (!cropState.img || !cropState.naturalW || !cropState.naturalH) {
                window.closeCropPreviewModal();
                return;
            }

            try {
                const cropCard = document.getElementById('crop-card');
                const rect = cropCard ? cropCard.getBoundingClientRect() : { width: 360, height: 460 };
                const cardW = rect.width || 360;
                const cardH = rect.height || 460;

                const apertureCX = cardW * 0.5;
                const apertureCY = cardH * 0.45;
                const r = cropState.radius || 125;

                const imgScreenCX = apertureCX + cropState.panX;
                const imgScreenCY = apertureCY + cropState.panY;

                const offsetScreenX = apertureCX - imgScreenCX;
                const offsetScreenY = apertureCY - imgScreenCY;

                const imgOffsetX = offsetScreenX / cropState.zoom;
                const imgOffsetY = offsetScreenY / cropState.zoom;

                const cropImgCX = cropState.naturalW / 2 + imgOffsetX;
                const cropImgCY = cropState.naturalH / 2 + imgOffsetY;
                const cropSize = (r * 2) / cropState.zoom;

                const srcX = cropImgCX - cropSize / 2;
                const srcY = cropImgCY - cropSize / 2;
                const srcW = cropSize;
                const srcH = cropSize;

                const canvas = document.createElement('canvas');
                canvas.width = 600;
                canvas.height = 600;
                const ctx = canvas.getContext('2d');

                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, 600, 600);
                ctx.drawImage(cropState.img, srcX, srcY, srcW, srcH, 0, 0, 600, 600);

                canvas.toBlob(function (blob) {
                    if (!blob) return;
                    window.selectedAvatarBlob = new File([blob], 'avatar_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    const previewUrl = canvas.toDataURL('image/jpeg', 0.92);
                    if (img) {
                        img.src = previewUrl;
                        img.style.display = 'block';
                    }
                    if (icon) icon.style.display = 'none';

                    window.closeCropPreviewModal();
                    if (typeof showToast === 'function') {
                        showToast('Photo confirmed! Click "Save Changes" to save profile.');
                    }
                }, 'image/jpeg', 0.92);
            } catch (err) {
                console.error('Crop confirmation error:', err);
                window.closeCropPreviewModal();
            }
        };

        // Attach dragging and pinch-to-zoom listeners to viewport
        const viewport = document.getElementById('crop-viewport');
        if (viewport) {
            viewport.addEventListener('mousedown', function (e) {
                cropState.isDragging = true;
                cropState.startX = e.clientX;
                cropState.startY = e.clientY;
                cropState.startPanX = cropState.panX;
                cropState.startPanY = cropState.panY;
                viewport.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', function (e) {
                if (!cropState.isDragging) return;
                const dx = e.clientX - cropState.startX;
                const dy = e.clientY - cropState.startY;
                cropState.panX = cropState.startPanX + dx;
                cropState.panY = cropState.startPanY + dy;
                updateCropTransform();
            });

            window.addEventListener('mouseup', function () {
                cropState.isDragging = false;
                if (viewport) viewport.style.cursor = 'grab';
            });

            viewport.addEventListener('touchstart', function (e) {
                if (e.touches.length === 1) {
                    cropState.isDragging = true;
                    cropState.startX = e.touches[0].clientX;
                    cropState.startY = e.touches[0].clientY;
                    cropState.startPanX = cropState.panX;
                    cropState.startPanY = cropState.panY;
                } else if (e.touches.length === 2) {
                    cropState.isDragging = false;
                    cropState.touchDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    cropState.startZoom = cropState.zoom;
                }
            }, { passive: false });

            window.addEventListener('touchmove', function (e) {
                const modal = document.getElementById('avatar-crop-modal');
                if (!modal || modal.style.display === 'none') return;

                if (e.touches.length === 1 && cropState.isDragging) {
                    e.preventDefault();
                    const dx = e.touches[0].clientX - cropState.startX;
                    const dy = e.touches[0].clientY - cropState.startY;
                    cropState.panX = cropState.startPanX + dx;
                    cropState.panY = cropState.startPanY + dy;
                    updateCropTransform();
                } else if (e.touches.length === 2 && cropState.touchDist > 0) {
                    e.preventDefault();
                    const newDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const scaleFactor = newDist / cropState.touchDist;
                    cropState.zoom = Math.max(cropState.baseZoom * 0.4, Math.min(cropState.baseZoom * 4.5, cropState.startZoom * scaleFactor));
                    updateCropTransform();
                }
            }, { passive: false });

            window.addEventListener('touchend', function (e) {
                if (e.touches.length === 0) {
                    cropState.isDragging = false;
                    cropState.touchDist = 0;
                } else if (e.touches.length === 1) {
                    cropState.isDragging = true;
                    cropState.startX = e.touches[0].clientX;
                    cropState.startY = e.touches[0].clientY;
                    cropState.startPanX = cropState.panX;
                    cropState.startPanY = cropState.panY;
                }
            });
        }

        // ─────────────────────────────────────────────────────────────
        // Save Profile Action
        // ─────────────────────────────────────────────────────────────
        window.saveProfile = async function (event) {
            event.preventDefault();

            const name = document.getElementById('profile-name')?.value.trim() || '';
            const email = document.getElementById('profile-email')?.value.trim() || '';
            const phone = document.getElementById('profile-phone')?.value.trim() || '';
            
            const locSelect = document.getElementById('profile-location');
            let homeLocation = locSelect?.value || '';
            if (homeLocation === 'Other') {
                const customVal = document.getElementById('profile-location-custom')?.value.trim();
                homeLocation = customVal || 'Other';
            }

            const age = document.getElementById('profile-age')?.value.trim() || '';
            const gender = document.getElementById('profile-gender')?.value || '';
            const bio = document.getElementById('profile-bio')?.value.trim() || '';

            // Validation 1: Age must be 2 digits only (1 to 99)
            if (age) {
                const ageNum = parseInt(age, 10);
                if (isNaN(ageNum) || ageNum < 1 || ageNum > 99 || age.length > 2) {
                    if (typeof showToast === 'function') showToast("Age must be 2 digits only (1-99).");
                    document.getElementById('profile-age')?.focus();
                    return;
                }
            }

            // Validation 2: Contact number must start with +63
            if (phone && phone !== '+63') {
                const cleanPhone = phone.replace(/[\s\-]/g, '');
                if (!cleanPhone.startsWith('+63')) {
                    if (typeof showToast === 'function') showToast("Contact number must start with +63 (e.g. +63 9XX XXX XXXX)");
                    document.getElementById('profile-phone')?.focus();
                    return;
                }
                const digitsAfter63 = cleanPhone.substring(3);
                if (digitsAfter63.length !== 10) {
                    if (typeof showToast === 'function') showToast("Please enter a valid 10-digit mobile number starting with +63 (e.g. +63 912 345 6789)");
                    document.getElementById('profile-phone')?.focus();
                    return;
                }
            }

            // Active preferences chips (only explicitly selected ones)
            const activeChips = Array.from(document.querySelectorAll('#preferences-chips .chip-item.active'))
                .map(chip => chip.getAttribute('data-value'));
            const travelPreferences = activeChips.join(', ');

            const avatarFile = window.selectedAvatarBlob || (document.getElementById('avatar-input') ? document.getElementById('avatar-input').files[0] : null);

            // Fast offline profile update
            if (!navigator.onLine || window.isAppOnline === false) {
                const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                stored.name = name;
                stored.email = email;
                stored.phone = phone;
                stored.home_location = homeLocation;
                stored.age = age;
                stored.gender = gender;
                stored.bio = bio;
                stored.travel_preferences = travelPreferences;

                const avatarPreviewImg = document.getElementById('avatar-img');
                const avatarSrc = (avatarPreviewImg && avatarPreviewImg.src && avatarPreviewImg.style.display !== 'none') ? avatarPreviewImg.src : null;
                if (avatarSrc && (avatarSrc.startsWith('data:') || avatarSrc.startsWith('blob:'))) {
                    stored.avatar = avatarSrc;
                }
                localStorage.setItem('auth_user', JSON.stringify(stored));

                if (window.OfflineProfileManager && typeof window.OfflineProfileManager.queueProfileUpdate === 'function') {
                    window.OfflineProfileManager.queueProfileUpdate({
                        name, email, phone, home_location: homeLocation, age, gender, bio, travel_preferences: travelPreferences,
                        avatarDataUrl: avatarSrc
                    });
                }

                window.profileNeedsRefresh = true;
                window.dashboardNeedsRefresh = true;
                window.dispatchEvent(new CustomEvent('userProfileUpdated', { detail: stored }));
                if (typeof showToast === 'function') showToast('Profile saved on device! Will sync when online.', 'info', 5000);
                if (typeof navigateTo === 'function') navigateTo('profile');
                return;
            }

            const btn = document.getElementById('btn-save');
            const originalContent = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Saving...</span>';
                btn.disabled = true;
            }

            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('home_location', homeLocation);
            formData.append('age', age);
            formData.append('gender', gender);
            formData.append('bio', bio);
            formData.append('travel_preferences', travelPreferences);

            if (avatarFile) formData.append('avatar', avatarFile);

            try {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
                const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                const res = await fetch(bUrl + '/api/tourist/profile', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: formData
                });

                let data = {};
                try {
                    data = await res.json();
                } catch (e) { }

                if (res.ok) {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    if (data.user) {
                        stored.name = data.user.name;
                        stored.email = data.user.email;
                        stored.phone = data.user.phone;
                        stored.home_location = data.user.home_location;
                        stored.age = data.user.age;
                        stored.gender = data.user.gender;
                        stored.bio = data.user.bio;
                        stored.travel_preferences = (data.user.travel_preferences !== undefined) ? data.user.travel_preferences : travelPreferences;

                        if (data.user.avatar) {
                            stored.avatar = window.getFullImageUrl ? window.getFullImageUrl(data.user.avatar) : data.user.avatar;
                        }
                    } else {
                        stored.name = name;
                        stored.email = email;
                        stored.phone = phone;
                        stored.home_location = homeLocation;
                        stored.age = age;
                        stored.gender = gender;
                        stored.bio = bio;
                        stored.travel_preferences = travelPreferences;
                    }
                    localStorage.setItem('auth_user', JSON.stringify(stored));

                    // Invalidate cached profile, dashboard, and leaderboard data so all screens reload fresh data from DB
                    window.profileNeedsRefresh = true;
                    window.dashboardNeedsRefresh = true;
                    window.leaderboardNeedsRefresh = true;

                    for (let i = localStorage.length - 1; i >= 0; i--) {
                        const key = localStorage.key(i);
                        if (key && (key.startsWith('profile_data_') || key.startsWith('dashboard_data_') || key.startsWith('leaderboard_data_'))) {
                            localStorage.removeItem(key);
                        }
                    }

                    // Notify active components of real-time profile update
                    window.dispatchEvent(new CustomEvent('userProfileUpdated', { detail: data.user || stored }));
                    if (typeof showToast === 'function') showToast('Profile updated successfully!');
                    if (typeof navigateTo === 'function') navigateTo('profile');
                } else {
                    let errMsg = data.message || ('Failed to update profile (HTTP ' + res.status + ')');
                    if (data.errors) {
                        const details = Object.values(data.errors).flat().join(' ');
                        if (details) errMsg += ': ' + details;
                    }
                    throw new Error(errMsg);
                }
            } catch (err) {
                console.warn('Profile online save failed, saving offline:', err);
                const isNetworkError = !navigator.onLine || window.isAppOnline === false || (err && err.message && (err.message.includes('fetch') || err.message.includes('Network')));
                if (isNetworkError) {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    stored.name = name;
                    stored.email = email;
                    stored.phone = phone;
                    stored.home_location = homeLocation;
                    stored.age = age;
                    stored.gender = gender;
                    stored.bio = bio;
                    stored.travel_preferences = travelPreferences;
                    const avatarPreviewImg = document.getElementById('avatar-img');
                    const avatarSrc = (avatarPreviewImg && avatarPreviewImg.src && avatarPreviewImg.style.display !== 'none') ? avatarPreviewImg.src : null;
                    if (avatarSrc && (avatarSrc.startsWith('data:') || avatarSrc.startsWith('blob:'))) {
                        stored.avatar = avatarSrc;
                    }
                    localStorage.setItem('auth_user', JSON.stringify(stored));

                    if (window.OfflineProfileManager && typeof window.OfflineProfileManager.queueProfileUpdate === 'function') {
                        window.OfflineProfileManager.queueProfileUpdate({
                            name, email, phone, home_location: homeLocation, age, gender, bio, travel_preferences: travelPreferences,
                            avatarDataUrl: avatarSrc
                        });
                    }

                    window.profileNeedsRefresh = true;
                    window.dashboardNeedsRefresh = true;
                    window.dispatchEvent(new CustomEvent('userProfileUpdated', { detail: stored }));
                    if (typeof showToast === 'function') showToast('Profile saved on device! Will sync when online.', 'info', 5000);
                    if (typeof navigateTo === 'function') navigateTo('profile');
                    return;
                }
                if (typeof showToast === 'function') showToast(err.message || 'Error updating profile');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        };

        function updateEditProfileOfflineStatus() {
            var badge = document.getElementById('edit-profile-offline-badge');
            var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (badge) {
                badge.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
            }
            if (!isOffline) {
                try {
                    localStorage.removeItem('intan_elyu_offline_mode');
                    localStorage.removeItem('intan_elyu_offline_guest');
                } catch(e) {}
            }
        }
        window.addEventListener('online', () => {
            updateEditProfileOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('offline', updateEditProfileOfflineStatus);
        window.addEventListener('appOnline', () => {
            updateEditProfileOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('appOffline', updateEditProfileOfflineStatus);
        setTimeout(updateEditProfileOfflineStatus, 100);
    })();
</script>