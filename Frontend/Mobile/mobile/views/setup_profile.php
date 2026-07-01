<!-- Setup Profile View -->
<?php
$pageTitle = 'Setup Profile';
?>



<div class="setup-container animate-slide-up" style="padding-top: env(safe-area-inset-top, 40px);">
    
    <div class="setup-header stagger-1">
        <h2>Complete Your Profile</h2>
        <p>Let's personalize your Intan Elyu experience.</p>
    </div>
    
    <div class="avatar-upload stagger-2">
        <div class="avatar-preview" id="avatarPreview">
            <i class="fa-solid fa-user" id="avatarIcon"></i>
            <img id="avatarImage" src="" alt="Avatar">
        </div>
        <div class="avatar-btn" onclick="document.getElementById('avatarInput').click()">
            <i class="fa-solid fa-camera"></i>
        </div>
        <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
    </div>
    
    <form class="setup-form stagger-3" onsubmit="saveProfile(event)">
        <div class="form-group">
            <label class="form-label">Phone Number (Optional)</label>
            <input type="tel" class="form-control" placeholder="+63 9XX XXX XXXX">
        </div>
        
        <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea class="form-control" rows="3" placeholder="I love traveling to mountains..."></textarea>
        </div>
        
        <button type="submit" class="btn-primary" style="padding:16px; margin-top:20px; width:100%;">Save Profile</button>
        <button type="button" class="btn-skip" onclick="skipProfile()">Skip for now</button>
    </form>
    
</div>

<script>
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarIcon').style.display = 'none';
                const img = document.getElementById('avatarImage');
                img.src = e.target.result;
                img.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }
    
    function saveProfile(e) {
        e.preventDefault();
        showToast('Profile saved!', 1500);
        setTimeout(() => {
            navigateTo('dashboard');
        }, 1000);
    }
    
    function skipProfile() {
        showToast('You can update this later.', 1500);
        setTimeout(() => {
            navigateTo('dashboard');
        }, 1000);
    }
</script>
