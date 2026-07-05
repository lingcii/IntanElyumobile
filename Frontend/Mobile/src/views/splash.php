<!-- Splash Screen View -->

<div class="splash-container animate-fade-in" id="splash-main">
    <div class="stagger-1" style="position:relative; z-index:100;">
        <div class="splash-logo">
            <img src="assets/img/logo.png" alt="Intan Elyu Logo" style="width:130px; height:130px; object-fit:cover; flex-shrink:0; margin-bottom:16px; position:relative; z-index:10; border-radius:50%;">
            <span class="splash-title">Intan Elyu</span>
        </div>
    </div>
    
    <div class="splash-tagline stagger-2">Your Ultimate Travel Companion</div>
    
    <div class="loading-dots stagger-3">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
    
    <!-- Animated Seamless SVG Wave -->
    <div class="wave-bottom">
        <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
            <path class="wave-layer wave-1" fill="rgba(255,255,255,0.15)" d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,100 L0,100 Z"></path>
            <path class="wave-layer wave-2" fill="rgba(255,255,255,0.3)" d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,100 L0,100 Z"></path>
            <path class="wave-layer wave-3" fill="rgba(255,255,255,0.6)" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,100 L0,100 Z"></path>
        </svg>
    </div>
</div>

<script>
    // Simulate loading and check authentication
    setTimeout(() => {
        const splashMain = document.getElementById('splash-main');
        if(splashMain) splashMain.classList.add('exit');
        
        setTimeout(() => {
            const token = localStorage.getItem('intan_elyu_token');
            if (token) {
                navigateTo('dashboard');
            } else {
                navigateTo('auth');
            }
        }, 450); // Wait for the fade out animation
    }, 2500);
</script>
