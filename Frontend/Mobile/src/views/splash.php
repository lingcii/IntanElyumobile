<!-- Splash Screen View -->

<div class="splash-container animate-fade-in" id="splash-main">
    <div class="stagger-1" style="position:relative; z-index:100;">
        <div class="splash-logo">
            <div class="splash-logo-container" style="box-sizing: border-box; width: 140px; height: 140px; background: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; position: relative; z-index: 10; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4); padding: 5px;">
                <img src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </div>
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
    <div class="wave-wrapper" id="splash-wave-wrapper">
        <div class="wave-bottom">
            <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                <path class="wave-layer wave-1" fill="rgba(30,41,59,0.3)" d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,100 L0,100 Z"></path>
                <path class="wave-layer wave-2" fill="rgba(30,41,59,0.5)" d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,100 L0,100 Z"></path>
                <path class="wave-layer wave-3" fill="#1e293b" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,100 L0,100 Z"></path>
            </svg>
        </div>
    </div>  
</div>

<script>
    // Simulate loading and check authentication
    setTimeout(() => {
        const splashMain = document.getElementById('splash-main');
        const token = localStorage.getItem('intan_elyu_token');
        
        if (token) {
            // Going to dashboard: normal fade out
            if(splashMain) splashMain.classList.add('exit');
            setTimeout(() => {
                navigateTo('dashboard');
            }, 450); // Wait for the fade out animation
        } else {
            // Going to auth: animate wave upwards to connect with login screen
            if(splashMain) {
                // Dynamically calculate the perfect translateY destination based on auth page layout
                const vh = window.innerHeight;
                const authTopHeight = Math.max(vh * 0.45, 350);
                const destCenter = (authTopHeight - 40) / 2 - 40; // Center of logo in auth flex block

                // Determine current position of the splash logo
                const logoContainer = splashMain.querySelector('.splash-logo-container');
                if (logoContainer) {
                    const rect = logoContainer.getBoundingClientRect();
                    const currentCenter = rect.top + (rect.height / 2);
                    
                    // The exact pixel difference needed
                    const translateY = destCenter - currentCenter;
                    
                    // Override CSS with exact inline style
                    logoContainer.style.setProperty('transform', `translateY(${translateY}px) scale(1)`, 'important');
                    logoContainer.style.setProperty('transition', 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)', 'important');
                    
                    // Freeze the float animation exactly where it is so it doesn't drift during transition
                    const wrapper = splashMain.querySelector('.splash-logo');
                    if (wrapper) wrapper.style.setProperty('animation', 'none', 'important');
                }
                
                splashMain.classList.add('transition-to-auth');
            }
            setTimeout(() => {
                navigateTo('auth', true, false);
            }, 600); // Wait for wave transition
        }
    }, 2500);
</script>
</script>
