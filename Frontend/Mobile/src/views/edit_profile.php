<?php
$pageTitle = 'Edit Profile';
$backRoute = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="setup-container edit-profile-container has-header animate-slide-up" style="margin-top: 20px;">
    <div class="setup-header">
        <div class="avatar-upload">
            <div class="avatar-preview" id="avatar-preview">
                <i class="fa-solid fa-user"></i>
                <img id="avatar-img" src="" alt="Avatar">
            </div>
            <div class="avatar-btn" onclick="document.getElementById('avatar-input').click()">
                <i class="fa-solid fa-camera"></i>
            </div>
            <input type="file" id="avatar-input" accept="image/*" style="display:none;" onchange="previewAvatar(event)">
        </div>
    </div>

    <form class="setup-form" onsubmit="saveProfile(event)">
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input class="form-control" id="profile-name" type="text" placeholder="Your name" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-control" id="profile-email" type="email" placeholder="Email address" disabled style="opacity:0.6;">
        </div>

        <button class="btn-primary" type="submit" style="margin-top: 8px; padding: 15px;">
            <i class="fa-solid fa-check" style="margin-right: 8px;"></i> Save Changes
        </button>
    </form>
</div>


<script>
(function() {
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
    const nameInput = document.getElementById('profile-name');
    const emailInput = document.getElementById('profile-email');
    if (nameInput && user.name) nameInput.value = user.name;
    if (emailInput && user.email) emailInput.value = user.email;

    if (user.avatar) {
        const img = document.getElementById('avatar-img');
        const icon = document.querySelector('.avatar-preview i');
        img.src = user.avatar;
        img.style.display = 'block';
        if (icon) icon.style.display = 'none';
    }

    window.previewAvatar = function(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('avatar-img');
            const icon = document.querySelector('.avatar-preview i');
            img.src = e.target.result;
            img.style.display = 'block';
            if (icon) icon.style.display = 'none';
        };
        reader.readAsDataURL(file);
    };

    window.saveProfile = async function(event) {
        event.preventDefault();
        const btn = event.target.querySelector('.btn-primary');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const name = document.getElementById('profile-name').value;
        const avatarFile = document.getElementById('avatar-input').files[0];

        const formData = new FormData();
        formData.append('name', name);
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
            const data = await res.json();
            if (res.ok) {
                if (data.user) {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    stored.name = data.user.name;
                    if (data.user.avatar) stored.avatar = data.user.avatar;
                    localStorage.setItem('auth_user', JSON.stringify(stored));
                }
                if (typeof showToast === 'function') showToast('Profile updated!');
                if (typeof navigateTo === 'function') navigateTo('profile');
            } else {
                throw new Error(data.message || 'Failed to save');
            }
        } catch (err) {
            if (typeof showToast === 'function') showToast(err.message);
            btn.innerHTML = '<i class="fa-solid fa-check" style="margin-right: 8px;"></i> Save Changes';
            btn.disabled = false;
        }
    };
})();
</script>
