<?php
$pageTitle = 'Edit Profile';
$backRoute = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="edit-profile-container has-header animate-slide-up">
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
                <input class="form-control" id="profile-phone" type="tel" placeholder="+63 9XX XXX XXXX"
                    autocomplete="tel">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-location">
                    Hometown / Origin
                </label>
                <input class="form-control" id="profile-location" type="text"
                    placeholder="e.g. San Juan, La Union / Manila">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label" for="profile-age">
                        Age
                    </label>
                    <input class="form-control" id="profile-age" type="number" min="1" max="120" placeholder="e.g. 24">
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
                        <option value="Non-binary" style="background: #1e3a8a; color: #ffffff;">Non-binary</option>
                        <option value="Prefer not to say" style="background: #1e3a8a; color: #ffffff;">Prefer not to say
                        </option>
                        <option value="Other" style="background: #1e3a8a; color: #ffffff;">Other</option>
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
        const locationInput = document.getElementById('profile-location');
        const ageInput = document.getElementById('profile-age');
        const genderInput = document.getElementById('profile-gender');
        const bioInput = document.getElementById('profile-bio');
        const img = document.getElementById('avatar-img');
        const icon = document.getElementById('avatar-icon');

        // Populate user values
        if (nameInput && user.name) nameInput.value = user.name;
        if (emailInput && user.email) emailInput.value = user.email;
        if (phoneInput && user.phone) phoneInput.value = user.phone;
        if (locationInput && user.home_location) locationInput.value = user.home_location;
        if (ageInput && user.age !== undefined && user.age !== null) ageInput.value = user.age;
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

            if (rawPrefs !== null && rawPrefs !== undefined) {
                const prefList = String(rawPrefs).split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
                chips.forEach(chip => {
                    const val = (chip.getAttribute('data-value') || '').toLowerCase();
                    const text = chip.textContent.toLowerCase();

                    const isMatch = prefList.some(p => {
                        const cleanP = p.replace(/[\u{1F300}-\u{1F9FF}]/gu, '').trim();
                        if (!cleanP) return false;
                        return val === cleanP || val.includes(cleanP) || cleanP.includes(val) ||
                            text.includes(cleanP) || cleanP.includes(text) ||
                            (cleanP.includes('surf') && val.includes('surf')) ||
                            (cleanP.includes('beach') && val.includes('beach')) ||
                            ((cleanP.includes('nature') || cleanP.includes('fall') || cleanP.includes('hike')) && val.includes('nature')) ||
                            ((cleanP.includes('heritage') || cleanP.includes('cultur')) && val.includes('heritage')) ||
                            ((cleanP.includes('food') || cleanP.includes('dine') || cleanP.includes('dining')) && val.includes('food')) ||
                            ((cleanP.includes('sunset') || cleanP.includes('night')) && val.includes('sunset'));
                    });

                    if (isMatch) {
                        chip.classList.add('active');
                    } else {
                        chip.classList.remove('active');
                    }
                });
            } else {
                // First time only when preferences have never been set: default to active
                chips.forEach(chip => chip.classList.add('active'));
            }
        }

        // Run initial sync from current user
        syncPreferencesFromData(user);

        // Toggle chip helper
        window.toggleChip = function (el) {
            el.classList.toggle('active');
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
                        if (locationInput && u.home_location) locationInput.value = u.home_location;
                        if (ageInput && u.age !== undefined && u.age !== null) ageInput.value = u.age;
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
            if (!navigator.onLine) {
                if (typeof showToast === 'function') {
                    showToast("Saving profile updates requires an active internet connection.");
                }
                return;
            }
            const btn = document.getElementById('btn-save');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Saving...</span>';
            btn.disabled = true;

            const name = document.getElementById('profile-name')?.value || '';
            const email = document.getElementById('profile-email')?.value || '';
            const phone = document.getElementById('profile-phone')?.value || '';
            const homeLocation = document.getElementById('profile-location')?.value || '';
            const age = document.getElementById('profile-age')?.value || '';
            const gender = document.getElementById('profile-gender')?.value || '';
            const bio = document.getElementById('profile-bio')?.value || '';

            // Active preferences chips
            const activeChips = Array.from(document.querySelectorAll('#preferences-chips .chip-item.active'))
                .map(chip => chip.getAttribute('data-value'));
            const travelPreferences = activeChips.join(', ');

            const avatarFile = window.selectedAvatarBlob || (document.getElementById('avatar-input') ? document.getElementById('avatar-input').files[0] : null);

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
                const token = localStorage.getItem('intan_elyu_token');
                const res = await fetch((window.backendUrl || '') + '/api/tourist/profile', {
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
                if (typeof showToast === 'function') showToast(err.message || 'Error updating profile');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        };
    })();
</script>