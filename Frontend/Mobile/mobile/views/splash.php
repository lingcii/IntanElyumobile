<!-- Splash Screen View -->


<div class="splash-container animate-fade-in">
    <div class="splash-logo stagger-1" style="display:flex; flex-direction:column; align-items:center;">
        <img src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 120px; height: auto; margin-bottom: 16px;">
        Intan Elyu
    </div>
    <div class="splash-tagline stagger-2">Your Ultimate Travel Companion</div>
    
    <div class="loading-dots stagger-3">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
</div>

<script>
    // Simulate loading and check authentication
    setTimeout(() => {
        const token = localStorage.getItem('intan_elyu_token');
        if (token) {
            navigateTo('dashboard');
        } else {
            navigateTo('auth');
        }
    }, 2500);
</script>
