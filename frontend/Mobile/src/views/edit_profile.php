<?php
$pageTitle = 'Edit Profile';
$backRoute = 'profile';
?>

<link rel="stylesheet" href="assets/css/views/edit_profile.css?v=<?= time() ?>">
<style>
/* Embedded styles for Edit Profile view */
body[data-view="edit_profile"] {
    background:
        radial-gradient(ellipse at 85% 5%, rgba(0, 242, 254, 0.35) 0%, transparent 55%),
        radial-gradient(ellipse at 15% 45%, rgba(56, 189, 248, 0.3) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 80%, rgba(63, 125, 183, 0.4) 0%, transparent 60%),
        linear-gradient(180deg, #1e3a8a 0%, #3f7db7 30%, #0284c7 65%, #06b6d4 90%, #00f2fe 100%) !important;
    background-attachment: fixed !important;
    color: #ffffff !important;
}

.edit-profile-container {
    width: 100%;
    min-height: calc(100vh - 60px);
    padding: 20px 16px 120px 16px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

.edit-profile-card {
    width: 100%;
    max-width: 440px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: none !important;
    outline: none !important;
    border-radius: 28px;
    padding: 28px 20px;
    box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-sizing: border-box;
}

.avatar-upload {
    position: relative;
    margin-bottom: 24px;
    display: inline-block;
}

.avatar-preview {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    border: none !important;
    outline: none !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin: 0 auto;
    position: relative;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: none;
    border-radius: 50%;
}

.avatar-preview i {
    font-size: 42px;
    color: rgba(255, 255, 255, 0.7);
}

.avatar-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none !important;
    outline: none !important;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 242, 254, 0.4);
    font-size: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.avatar-btn:active {
    transform: scale(0.92);
}

.edit-profile-form {
    width: 100%;
}

.edit-section-title {
    font-size: 13px;
    font-weight: 800;
    color: #00f2fe;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin: 22px 0 14px 0;
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    gap: 8px !important;
    width: 100% !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    padding-bottom: 8px;
    text-align: left;
    box-sizing: border-box;
}

.edit-section-title i {
    color: #00f2fe;
    font-size: 14px;
}

.form-group {
    text-align: left;
    margin-bottom: 18px;
    width: 100%;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-label i {
    color: #00f2fe;
    font-size: 13px;
}

.form-control {
    width: 100%;
    padding: 13px 16px;
    background: rgba(255, 255, 255, 0.12);
    border: none !important;
    outline: none !important;
    color: #ffffff;
    border-radius: 16px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.25s ease;
    box-sizing: border-box;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
    line-height: 1.5;
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.form-control:focus {
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 0 2px #00f2fe;
}

.input-with-badge {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.form-control.disabled-input {
    background: rgba(255, 255, 255, 0.06);
    border: none !important;
    outline: none !important;
    color: rgba(255, 255, 255, 0.5);
    padding-right: 90px;
    cursor: not-allowed;
}

.read-only-badge {
    position: absolute;
    right: 12px;
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    background: rgba(255, 255, 255, 0.12);
    padding: 4px 8px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
    pointer-events: none;
    border: none !important;
    outline: none !important;
}

.chips-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 6px;
}

.chip-item {
    background: rgba(255, 255, 255, 0.12);
    border: none !important;
    outline: none !important;
    color: rgba(255, 255, 255, 0.85);
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.chip-item.active {
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(0, 242, 254, 0.35);
}

.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.08);
    border: none !important;
    outline: none !important;
    padding: 14px 16px;
    border-radius: 16px;
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
}

.toggle-info {
    text-align: left;
}

.toggle-title {
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
}

.toggle-desc {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 2px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 26px;
    flex-shrink: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(255, 255, 255, 0.2);
    transition: .3s;
    border-radius: 26px;
    border: none !important;
    outline: none !important;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%);
}

input:checked + .slider:before {
    transform: translateX(20px);
}

.form-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 14px;
    width: 100%;
}

.btn-save-profile {
    width: 100%;
    height: 50px;
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%);
    color: #ffffff;
    border: none !important;
    outline: none !important;
    border-radius: 16px;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0, 242, 254, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.btn-save-profile:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(0, 242, 254, 0.25);
}

.btn-save-profile:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-cancel-profile {
    width: 100%;
    height: 44px;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border: none !important;
    outline: none !important;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel-profile:hover, .btn-cancel-profile:active {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
}
</style>

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
                <i class="fa-solid fa-user-gear"></i> Personal Details
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-name">
                    <i class="fa-solid fa-user-pen"></i> Full Name
                </label>
                <input class="form-control" id="profile-name" type="text" placeholder="Enter your full name" required autocomplete="name">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-email">
                    <i class="fa-solid fa-envelope"></i> Email Address
                </label>
                <input class="form-control" id="profile-email" type="email" placeholder="Email address" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-phone">
                    <i class="fa-solid fa-phone"></i> Phone / Mobile Number
                </label>
                <input class="form-control" id="profile-phone" type="tel" placeholder="+63 9XX XXX XXXX" autocomplete="tel">
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-location">
                    <i class="fa-solid fa-location-dot"></i> Hometown / Origin
                </label>
                <input class="form-control" id="profile-location" type="text" placeholder="e.g. San Juan, La Union / Manila">
            </div>

            <!-- Bio / Motto -->
            <div class="edit-section-title">
                <i class="fa-solid fa-quote-left"></i> Travel Bio & Motto
            </div>

            <div class="form-group">
                <label class="form-label" for="profile-bio">
                    <i class="fa-solid fa-pen-nib"></i> Bio / Traveler Statement
                </label>
                <textarea class="form-control" id="profile-bio" placeholder="Share a brief motto or your passion for exploring La Union..."></textarea>
            </div>

            <!-- Travel Preferences -->
            <div class="edit-section-title">
                <i class="fa-solid fa-compass"></i> Travel Preferences
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-heart"></i> Preferred Spot Categories
                </label>
                <div class="chips-container" id="preferences-chips">
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Surfing & Beach">🏄‍♂️ Surfing & Beach</div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Nature & Falls">🏔️ Nature & Falls</div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Heritage & Culture">🏛️ Heritage & Culture</div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Food & Dining">🍲 Food & Dining</div>
                    <div class="chip-item" onclick="toggleChip(this)" data-value="Sunset & Nightlife">🌅 Sunset & Nightlife</div>
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
<div id="image-picker-modal" onclick="if(event.target===this) window.closeImagePickerModal()" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:999999; align-items:flex-end; justify-content:center; padding:0; margin:0; box-sizing:border-box;">
    <div style="background:linear-gradient(135deg, rgba(30,41,59,0.98) 0%, rgba(15,23,42,1) 100%); border-top:1px solid rgba(56,189,248,0.3); border-radius:28px 28px 0 0; width:100%; max-width:500px; padding:26px 22px; box-shadow:0 -10px 45px rgba(0,0,0,0.8); animation:slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); box-sizing:border-box;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:17px; font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-image" style="color:#38bdf8; font-size:18px;"></i> Select Profile Photo
            </h3>
            <button type="button" onclick="window.closeImagePickerModal()" style="background:rgba(255,255,255,0.08); border:none; color:#94a3b8; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button type="button" onclick="window.selectImageSource('camera')" style="width:100%; padding:15px; background:linear-gradient(135deg, rgba(56,189,248,0.18) 0%, rgba(37,99,235,0.22) 100%); border:1px solid rgba(56,189,248,0.35); border-radius:18px; color:#38bdf8; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <i class="fa-solid fa-camera" style="font-size:17px;"></i> Take Photo with Camera
            </button>
            <button type="button" onclick="window.selectImageSource('gallery')" style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:18px; color:#f8fafc; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, background 0.15s ease;">
                <i class="fa-solid fa-images" style="font-size:17px; color:#38bdf8;"></i> Choose from Photo Gallery
            </button>
            <button type="button" onclick="window.closeImagePickerModal()" style="width:100%; padding:12px; background:transparent; border:none; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; margin-top:4px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
(function() {
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
    const bioInput = document.getElementById('profile-bio');
    const privacyToggle = document.getElementById('profile-privacy');
    const img = document.getElementById('avatar-img');
    const icon = document.getElementById('avatar-icon');

    // Populate user values
    if (nameInput && user.name) nameInput.value = user.name;
    if (emailInput && user.email) emailInput.value = user.email;
    if (phoneInput && user.phone) phoneInput.value = user.phone;
    if (locationInput && user.home_location) locationInput.value = user.home_location;
    if (bioInput && user.bio) bioInput.value = user.bio;
    if (privacyToggle && typeof user.is_leaderboard_private !== 'undefined') {
        privacyToggle.checked = Boolean(user.is_leaderboard_private);
    }

    // Populate travel preferences chips
    if (user.travel_preferences) {
        const selected = user.travel_preferences.split(',').map(s => s.trim());
        document.querySelectorAll('#preferences-chips .chip-item').forEach(chip => {
            if (selected.includes(chip.getAttribute('data-value'))) {
                chip.classList.add('active');
            }
        });
    }

    // Toggle chip helper
    window.toggleChip = function(el) {
        el.classList.toggle('active');
    };

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

        img.onerror = function() {
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

    window.openImagePickerModal = function() {
        const modal = document.getElementById('image-picker-modal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeImagePickerModal = function() {
        const modal = document.getElementById('image-picker-modal');
        if (modal) modal.style.display = 'none';
    };

    window.selectImageSource = async function(mode) {
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
                    allowEditing: true,
                    resultType: 'dataUrl',
                    source: mode === 'camera' ? 'CAMERA' : 'PHOTOS'
                });

                if (image && image.dataUrl) {
                    if (img) {
                        img.src = image.dataUrl;
                        img.style.display = 'block';
                    }
                    if (icon) icon.style.display = 'none';

                    // Convert dataUrl to Blob File
                    const res = await fetch(image.dataUrl);
                    const blob = await res.blob();
                    window.selectedAvatarBlob = new File([blob], 'avatar_' + Date.now() + '.jpg', { type: blob.type || 'image/jpeg' });
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

    window.previewAvatar = function(event) {
        const file = event.target.files[0];
        if (!file) return;
        window.selectedAvatarBlob = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
            if (icon) icon.style.display = 'none';
        };
        reader.readAsDataURL(file);
    };

    window.saveProfile = async function(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-save');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Saving...</span>';
        btn.disabled = true;

        const name = document.getElementById('profile-name')?.value || '';
        const email = document.getElementById('profile-email')?.value || '';
        const phone = document.getElementById('profile-phone')?.value || '';
        const homeLocation = document.getElementById('profile-location')?.value || '';
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
            } catch (e) {}

            if (res.ok) {
                if (data.user) {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    stored.name = data.user.name;
                    stored.email = data.user.email;
                    stored.phone = data.user.phone;
                    stored.home_location = data.user.home_location;
                    stored.bio = data.user.bio;
                    stored.travel_preferences = data.user.travel_preferences;

                    if (data.user.avatar) {
                        stored.avatar = window.getFullImageUrl ? window.getFullImageUrl(data.user.avatar) : data.user.avatar;
                    }
                    localStorage.setItem('auth_user', JSON.stringify(stored));
                }
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
                window.dispatchEvent(new CustomEvent('userProfileUpdated', { detail: data.user }));
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
